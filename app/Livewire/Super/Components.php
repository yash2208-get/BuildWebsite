<?php

declare(strict_types=1);

namespace App\Livewire\Super;

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
        $c = Component::findOrFail($id);
        $c->update(['is_active' => ! $c->is_active]);
        $this->notifySuccess('Component updated.');
    }

    public function deleteComponent(int $id): void
    {
        Component::findOrFail($id)->delete();
        $this->notifySuccess('Component deleted.');
    }

    protected function title(): string
    {
        return 'Component Management';
    }

    protected function view(): string
    {
        return 'livewire.super.components';
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
