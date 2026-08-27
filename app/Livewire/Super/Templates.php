<?php

declare(strict_types=1);

namespace App\Livewire\Super;

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
        $t = Template::findOrFail($id);
        $t->update(['is_active' => ! $t->is_active]);
        $this->notifySuccess('Template updated.');
    }

    public function toggleFeatured(int $id): void
    {
        $t = Template::findOrFail($id);
        $t->update(['is_featured' => ! $t->is_featured]);
        $this->notifySuccess('Template updated.');
    }

    public function deleteTemplate(int $id): void
    {
        Template::findOrFail($id)->delete();
        $this->notifySuccess('Template deleted.');
    }

    protected function title(): string
    {
        return 'Template Management';
    }

    protected function view(): string
    {
        return 'livewire.super.templates';
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
            'totals' => ['all' => Template::count(), 'premium' => Template::where('is_premium', true)->count()],
        ];
    }
}
