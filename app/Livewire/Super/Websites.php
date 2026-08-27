<?php

declare(strict_types=1);

namespace App\Livewire\Super;

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

        $website->isPublished()
            ? app(WebsiteService::class)->unpublish($website)
            : app(WebsiteService::class)->publish($website);

        $this->notifySuccess('Website status updated.');
    }

    public function deleteWebsite(int $id): void
    {
        app(WebsiteService::class)->delete(Website::findOrFail($id));
        $this->notifySuccess('Website deleted.');
    }

    protected function title(): string
    {
        return 'All Websites';
    }

    protected function view(): string
    {
        return 'livewire.super.websites';
    }

    protected function searchable(): array
    {
        return ['name', 'subdomain', 'custom_domain', 'user.name'];
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
                'domains' => Website::whereNotNull('custom_domain')->count(),
                'views' => (int) Website::sum('views_count'),
            ],
        ];
    }
}
