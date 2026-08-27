<?php

declare(strict_types=1);

namespace App\Livewire\Builder;

use App\Livewire\BaseComponent;
use App\Models\Component as BlockComponent;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Website;
use App\Services\Website\PageService;
use App\Services\Website\WebsiteService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Throwable;

/**
 * The visual builder shell.
 *
 * Livewire owns persistence, page management and the component catalogue;
 * GrapesJS owns the canvas. The two communicate through a narrow event
 * bridge so heavy canvas state never round-trips through the server.
 */
#[Layout('layouts.builder')]
class Editor extends BaseComponent
{
    public Website $website;

    public Page $page;

    public string $device = 'desktop';

    public bool $dirty = false;

    public ?string $lastSavedAt = null;

    public bool $showPages = false;
    public bool $showSettings = false;
    public bool $showRevisions = false;

    public string $newPageTitle = '';
    public string $newPageSlug = '';

    public function mount(Website $website, ?Page $page = null): void
    {
        $this->authorize('update', $website);

        $this->website = $website;

        $this->page = $page
            ?? $website->pages()->where('is_homepage', true)->first()
            ?? $website->pages()->orderBy('sort_order')->firstOrFail();

        abort_unless($this->page->website_id === $website->id, 404);

        $this->lastSavedAt = $this->page->updated_at?->diffForHumans();
    }

    /* ---------------------------------------------------------------- */
    /*  Canvas persistence                                              */
    /* ---------------------------------------------------------------- */

    #[On('canvas-save')]
    public function saveCanvas(array $payload): void
    {
        $this->authorize('update', $this->website);

        try {
            app(PageService::class)->saveContent($this->page, [
                'html' => $payload['html'] ?? '',
                'css' => $payload['css'] ?? '',
                'components' => $payload['components'] ?? [],
                'styles' => $payload['styles'] ?? [],
                'assets' => $payload['assets'] ?? [],
            ]);

            $this->page->refresh();
            $this->dirty = false;
            $this->lastSavedAt = now()->diffForHumans();

            $this->dispatch('canvas-saved');
        } catch (Throwable $e) {
            report($e);
            $this->notifyError('Could not save: '.$e->getMessage());
        }
    }

    #[On('canvas-dirty')]
    public function markDirty(): void
    {
        $this->dirty = true;
    }

    /* ---------------------------------------------------------------- */
    /*  Page management                                                 */
    /* ---------------------------------------------------------------- */

    public function switchPage(int $pageId): void
    {
        $page = $this->website->pages()->findOrFail($pageId);

        $this->page = $page;
        $this->showPages = false;
        $this->dirty = false;
        $this->lastSavedAt = $page->updated_at?->diffForHumans();

        $this->dispatch('page-switched', content: $this->canvasPayload());
    }

    public function createPage(): void
    {
        $this->authorize('update', $this->website);

        $data = $this->validate([
            'newPageTitle' => ['required', 'string', 'min:2', 'max:120'],
            'newPageSlug' => ['nullable', 'string', 'alpha_dash', 'max:120'],
        ]);

        $page = app(PageService::class)->create($this->website, [
            'title' => $data['newPageTitle'],
            'slug' => $data['newPageSlug'] ?: null,
        ]);

        $this->reset('newPageTitle', 'newPageSlug');
        $this->switchPage($page->id);
        $this->notifySuccess('Page created.');
    }

    public function duplicatePage(int $pageId): void
    {
        $page = $this->website->pages()->findOrFail($pageId);
        $this->authorize('update', $this->website);

        $copy = app(PageService::class)->duplicate($page);

        $this->notifySuccess("\"{$copy->title}\" created.");
    }

    public function deletePage(int $pageId): void
    {
        $page = $this->website->pages()->findOrFail($pageId);
        $this->authorize('delete', $page);

        if ($page->is_homepage) {
            $this->notifyError('The homepage cannot be deleted — set another page as home first.');

            return;
        }

        $wasCurrent = $page->id === $this->page->id;
        app(PageService::class)->delete($page);

        if ($wasCurrent) {
            $this->switchPage($this->website->pages()->first()->id);
        }

        $this->notifySuccess('Page deleted.');
    }

    public function setHomepage(int $pageId): void
    {
        $page = $this->website->pages()->findOrFail($pageId);
        $this->authorize('update', $this->website);

        app(PageService::class)->setHomepage($page);

        $this->notifySuccess("\"{$page->title}\" is now the homepage.");
    }

    /* ---------------------------------------------------------------- */
    /*  Revisions                                                       */
    /* ---------------------------------------------------------------- */

    public function restoreRevision(int $revisionId): void
    {
        $revision = PageRevision::where('page_id', $this->page->id)->findOrFail($revisionId);
        $this->authorize('update', $this->website);

        app(PageService::class)->restoreRevision($this->page, $revision);

        $this->page->refresh();
        $this->showRevisions = false;

        $this->dispatch('page-switched', content: $this->canvasPayload());
        $this->notifySuccess('Revision restored.');
    }

    /* ---------------------------------------------------------------- */
    /*  Publishing                                                      */
    /* ---------------------------------------------------------------- */

    public function publish(): void
    {
        $this->authorize('publish', $this->website);

        app(WebsiteService::class)->publish($this->website);
        $this->website->refresh();

        $this->notifySuccess('Your website is live!');
    }

    public function unpublish(): void
    {
        $this->authorize('publish', $this->website);

        app(WebsiteService::class)->unpublish($this->website);
        $this->website->refresh();

        $this->notifySuccess('Website unpublished.');
    }

    /* ---------------------------------------------------------------- */

    public function canvasPayload(): array
    {
        $content = $this->page->content ?? [];

        return [
            'pageId' => $this->page->id,
            'html' => $content['html'] ?? '<section class="ab-section"><div class="ab-container"><h1>Start building</h1><p>Drag a block from the left panel onto the canvas.</p></div></section>',
            'css' => $content['css'] ?? '',
            'components' => $content['components'] ?? null,
            'styles' => $content['styles'] ?? null,
        ];
    }

    public function render()
    {
        return view('livewire.builder.editor', [
            'pages' => $this->website->pages()->orderBy('sort_order')->get(),
            'blocks' => BlockComponent::active()->orderBy('category')->orderBy('sort_order')->get()->groupBy('category'),
            'revisions' => $this->showRevisions
                ? PageRevision::where('page_id', $this->page->id)->with('user')->latest()->limit(20)->get()
                : collect(),
            'categories' => config('platform.component_categories'),
            'theme' => $this->website->theme,
        ])->layoutData([
            'title' => $this->website->name.' — Builder',
        ]);
    }
}
