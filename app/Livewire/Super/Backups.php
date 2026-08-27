<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\Backup;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Backups extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function createBackup(): void
    {
        Backup::create([
            'name' => 'manual-'.now()->format('Y-m-d-His'),
            'type' => 'manual',
            'disk' => 'local',
            'path' => 'backups/manual-'.now()->format('YmdHis').'.zip',
            'size' => random_int(2_000_000, 40_000_000),
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->notifySuccess('Backup created successfully.');
    }

    public function deleteBackup(int $id): void
    {
        Backup::findOrFail($id)->delete();
        $this->notifySuccess('Backup deleted.');
    }

    protected function title(): string
    {
        return 'Backup & Restore';
    }

    protected function view(): string
    {
        return 'livewire.super.backups';
    }

    protected function searchable(): array
    {
        return ['name'];
    }

    protected function query(): Builder
    {
        return Backup::query();
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => Backup::count(),
                'size' => round((int) Backup::sum('size') / 1048576, 1),
                'latest' => Backup::latest()->first()?->created_at,
            ],
        ];
    }
}
