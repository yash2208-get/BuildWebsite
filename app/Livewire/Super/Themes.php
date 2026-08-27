<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Themes extends ResourceComponent
{
    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public function toggleActive(int $id): void
    {
        $t = Theme::findOrFail($id);
        $t->update(['is_active' => ! $t->is_active]);
        $this->notifySuccess('Theme updated.');
    }

    public function makeDefault(int $id): void
    {
        Theme::query()->update(['is_default' => false]);
        Theme::findOrFail($id)->update(['is_default' => true, 'is_active' => true]);
        $this->notifySuccess('Default theme updated.');
    }

    protected function title(): string
    {
        return 'Theme Management';
    }

    protected function view(): string
    {
        return 'livewire.super.themes';
    }

    protected function searchable(): array
    {
        return ['name', 'description'];
    }

    protected function query(): Builder
    {
        return Theme::query();
    }

    protected function viewData(): array
    {
        return [
            'totals' => ['all' => Theme::count(), 'system' => Theme::whereNull('user_id')->count()],
        ];
    }
}
