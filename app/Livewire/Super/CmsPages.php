<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\CmsPage;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CmsPages extends ResourceComponent
{
    public string $sortField = 'title';

    public string $sortDirection = 'asc';

    public function togglePublished(int $id): void
    {
        $page = CmsPage::findOrFail($id);
        $page->update(['status' => $page->status === 'published' ? 'draft' : 'published']);
        $this->notifySuccess('Page updated.');
    }

    public function deletePage(int $id): void
    {
        CmsPage::findOrFail($id)->delete();
        $this->notifySuccess('Page deleted.');
    }

    protected function title(): string
    {
        return 'CMS Pages';
    }

    protected function view(): string
    {
        return 'livewire.super.cms-pages';
    }

    protected function searchable(): array
    {
        return ['title', 'slug'];
    }

    protected function query(): Builder
    {
        return CmsPage::query();
    }

    protected function viewData(): array
    {
        return [
            'totals' => ['all' => CmsPage::count(), 'published' => CmsPage::where('status', 'published')->count()],
        ];
    }
}
