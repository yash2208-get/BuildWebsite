<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\Media as MediaModel;
use App\Services\Platform\MediaService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Media extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function deleteMedia(int $id): void
    {
        app(MediaService::class)->delete(MediaModel::findOrFail($id));
        $this->notifySuccess('File deleted.');
    }

    protected function title(): string
    {
        return 'All Media';
    }

    protected function view(): string
    {
        return 'livewire.super.media';
    }

    protected function searchable(): array
    {
        return ['name', 'original_name'];
    }

    protected function query(): Builder
    {
        return MediaModel::query()
            ->with('user');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => MediaModel::count(),
                'size' => round((int) MediaModel::sum('size') / 1048576, 1),
            ],
        ];
    }
}
