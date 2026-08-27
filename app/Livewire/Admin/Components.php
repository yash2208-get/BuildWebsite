<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Livewire\ResourceComponent;
use App\Models\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Components extends ResourceComponent
{
    public string $sortField = 'sort_order';

    public string $sortDirection = 'asc';

    public function toggleActive(int $id): void
    {
        $component = Component::findOrFail($id);
        $component->update(['is_active' => ! $component->is_active]);

        $this->notifySuccess('Component updated.');
    }

    protected function title(): string
    {
        return 'Components';
    }

    protected function view(): string
    {
        return 'livewire.admin.components';
    }

    protected function searchable(): array
    {
        return ['name', 'description'];
    }

    protected function query(): Builder
    {
        return Component::query();
    }

    protected function viewData(): array
    {
        return [
            'categories' => config('platform.component_categories'),
            'totals' => ['all' => Component::count(), 'active' => Component::where('is_active', true)->count()],
        ];
    }
}
