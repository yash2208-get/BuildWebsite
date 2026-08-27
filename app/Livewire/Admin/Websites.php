<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\ResourceComponent;
use App\Models\Website;
use App\Services\Website\WebsiteService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Websites extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function togglePublish(int $id): void
    {
        $website = Website::findOrFail($id);
        $this->authorize('publish', $website);

        $website->isPublished()
            ? app(WebsiteService::class)->unpublish($website)
            : app(WebsiteService::class)->publish($website);

        $this->notifySuccess('Website '.($website->refresh()->isPublished() ? 'published' : 'unpublished').'.');
    }

    public function deleteWebsite(int $id): void
    {
        $website = Website::findOrFail($id);
        $this->authorize('delete', $website);

        app(WebsiteService::class)->delete($website);
        $this->notifySuccess('Website deleted.');
    }

    protected function title(): string
    {
        return 'Website Management';
    }

    protected function view(): string
    {
        return 'livewire.admin.websites';
    }

    protected function searchable(): array
    {
        return ['name', 'subdomain', 'user.name'];
    }

    protected function query(): Builder
    {
        return Website::query()
            ->with('user', 'theme')
            ->withCount('pages');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => Website::count(),
                'published' => Website::where('status', 'published')->count(),
                'draft' => Website::where('status', 'draft')->count(),
                'views' => (int) Website::sum('views_count'),
            ],
        ];
    }
}
