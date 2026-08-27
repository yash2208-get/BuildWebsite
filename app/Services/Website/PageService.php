<?php

declare(strict_types=1);

namespace App\Services\Website;

use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageService
{
    public function __construct(private readonly QuotaService $quotas) {}

    public function create(Website $website, array $data): Page
    {
        $this->quotas->assertCanCreatePage($website);

        return DB::transaction(function () use ($website, $data) {
            $isFirst = $website->pages()->count() === 0;

            $page = Page::create([
                'website_id' => $website->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['slug'] ?? $data['title']),
                'type' => $data['type'] ?? 'page',
                'html' => $data['html'] ?? '',
                'css' => $data['css'] ?? '',
                'grapes_data' => $data['grapes_data'] ?? null,
                'seo' => $data['seo'] ?? ['title' => $data['title'], 'description' => ''],
                'is_homepage' => (bool) ($data['is_homepage'] ?? $isFirst),
                'show_in_nav' => (bool) ($data['show_in_nav'] ?? true),
                'sort_order' => $data['sort_order'] ?? $website->pages()->max('sort_order') + 1,
                'status' => $data['status'] ?? 'draft',
            ]);

            if ($page->is_homepage) {
                $this->makeHomepage($page);
            }

            $website->update(['pages_count' => $website->pages()->count()]);

            return $page;
        });
    }

    public function duplicate(Page $page, ?string $title = null): Page
    {
        $this->quotas->assertCanCreatePage($page->website);

        return DB::transaction(function () use ($page, $title) {
            $copy = $page->replicate(['views_count', 'published_at', 'deleted_at']);
            $copy->title = $title ?: $page->title.' (Copy)';
            $copy->slug = Str::slug($copy->title).'-'.Str::lower(Str::random(4));
            $copy->is_homepage = false;
            $copy->status = 'draft';
            $copy->views_count = 0;
            $copy->published_at = null;
            $copy->sort_order = (int) $page->website->pages()->max('sort_order') + 1;
            $copy->save();

            $page->website->update(['pages_count' => $page->website->pages()->count()]);

            return $copy;
        });
    }

    /** Persist builder output and snapshot a revision. */
    public function saveCanvas(Page $page, array $payload, ?int $userId = null, ?string $note = null): Page
    {
        return DB::transaction(function () use ($page, $payload, $userId, $note) {
            $this->snapshot($page, $userId, $note);

            $page->update([
                'html' => $payload['html'] ?? $page->html,
                'css' => $payload['css'] ?? $page->css,
                'grapes_data' => $payload['grapes_data'] ?? $page->grapes_data,
            ]);

            $page->website?->touchEdited();

            return $page->fresh();
        });
    }

    public function snapshot(Page $page, ?int $userId = null, ?string $note = null): ?PageRevision
    {
        if (blank($page->html) && blank($page->grapes_data)) {
            return null;
        }

        $revision = PageRevision::create([
            'page_id' => $page->id,
            'user_id' => $userId,
            'version' => $page->nextVersion(),
            'html' => $page->html,
            'css' => $page->css,
            'grapes_data' => $page->grapes_data,
            'note' => $note,
        ]);

        $this->pruneRevisions($page);

        return $revision;
    }

    public function restore(Page $page, PageRevision $revision): Page
    {
        $this->snapshot($page, auth()->id(), 'Auto-snapshot before restore');

        $page->update([
            'html' => $revision->html,
            'css' => $revision->css,
            'grapes_data' => $revision->grapes_data,
        ]);

        return $page->fresh();
    }

    public function makeHomepage(Page $page): void
    {
        Page::where('website_id', $page->website_id)
            ->whereKeyNot($page->getKey())
            ->update(['is_homepage' => false]);

        $page->forceFill(['is_homepage' => true])->save();
    }

    public function reorder(Website $website, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $index => $id) {
            Page::where('website_id', $website->id)->whereKey($id)->update(['sort_order' => $index]);
        }
    }

    public function delete(Page $page): void
    {
        $website = $page->website;
        $wasHome = $page->is_homepage;

        $page->delete();

        if ($wasHome && $next = $website?->pages()->orderBy('sort_order')->first()) {
            $this->makeHomepage($next);
        }

        $website?->update(['pages_count' => $website->pages()->count()]);
    }

    private function pruneRevisions(Page $page): void
    {
        $keep = (int) config('platform.builder.max_revisions', 30);

        $ids = $page->revisions()->skip($keep)->take(100)->pluck('id');

        if ($ids->isNotEmpty()) {
            PageRevision::whereIn('id', $ids)->delete();
        }
    }
}
