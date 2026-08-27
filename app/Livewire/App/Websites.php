<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Website;
use App\Repositories\Eloquent\WebsiteRepository;
use App\Services\Website\QuotaService;
use App\Services\Website\WebsiteService;
use App\Support\Exceptions\QuotaExceededException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Websites extends BaseComponent
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: 'grid')]
    public string $view = 'grid';

    public bool $showCreate = false;

    public string $name = '';

    public string $description = '';

    public string $category = 'business';

    public ?int $confirmingDelete = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Website::class);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:60'],
        ]);

        try {
            $website = app(WebsiteService::class)->create($this->user(), $data);
        } catch (QuotaExceededException $e) {
            $this->notifyError($e->getMessage());

            return;
        }

        $this->reset('name', 'description', 'showCreate');
        $this->notifySuccess('Website created — opening the builder.');

        $this->redirectRoute('builder.edit', ['website' => $website->id], navigate: true);
    }

    public function clone(int $id): void
    {
        $website = Website::findOrFail($id);
        $this->authorize('clone', $website);

        try {
            app(WebsiteService::class)->clone($website);
            $this->notifySuccess('Website duplicated successfully.');
        } catch (QuotaExceededException $e) {
            $this->notifyError($e->getMessage());
        }
    }

    public function togglePublish(int $id): void
    {
        $website = Website::findOrFail($id);
        $this->authorize('publish', $website);

        $service = app(WebsiteService::class);

        if ($website->isPublished()) {
            $service->unpublish($website);
            $this->notifySuccess('Website unpublished.');
        } else {
            $service->publish($website);
            $this->notifySuccess('Website is now live!');
        }
    }

    public function delete(int $id): void
    {
        $website = Website::findOrFail($id);
        $this->authorize('delete', $website);

        app(WebsiteService::class)->delete($website);

        $this->confirmingDelete = null;
        $this->notifySuccess('Website moved to trash.');
    }

    public function render()
    {
        $websites = app(WebsiteRepository::class)->paginateForUser($this->user(), [
            'search' => $this->search,
            'status' => $this->status,
        ], 12);

        return view('livewire.app.websites', [
            'websites' => $websites,
            'stats' => app(WebsiteRepository::class)->statsFor($this->user()),
            'canCreate' => app(QuotaService::class)->canCreateWebsite($this->user()),
            'categories' => config('platform.template_categories'),
        ])->layoutData($this->layoutData('My Websites'));
    }
}
