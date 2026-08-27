<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\ResourceComponent;
use App\Models\Template;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Templates extends ResourceComponent
{
    public string $sortField = 'sort_order';

    public string $sortDirection = 'asc';

    public function toggleActive(int $id): void
    {
        $template = Template::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);

        $this->notifySuccess($template->name.' is now '.($template->is_active ? 'visible' : 'hidden').'.');
    }

    public function toggleFeatured(int $id): void
    {
        $template = Template::findOrFail($id);
        $template->update(['is_featured' => ! $template->is_featured]);

        $this->notifySuccess('Featured status updated.');
    }

    protected function title(): string
    {
        return 'Templates';
    }

    protected function view(): string
    {
        return 'livewire.admin.templates';
    }

    protected function searchable(): array
    {
        return ['name', 'description'];
    }

    protected function query(): Builder
    {
        return Template::query();
    }

    protected function viewData(): array
    {
        return [
            'categories' => config('platform.template_categories'),
            'totals' => [
                'all' => Template::count(),
                'active' => Template::where('is_active', true)->count(),
                'premium' => Template::where('is_premium', true)->count(),
            ],
        ];
    }
}
