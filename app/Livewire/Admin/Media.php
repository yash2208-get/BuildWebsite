<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

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
        $media = MediaModel::findOrFail($id);
        $this->authorize('delete', $media);

        app(MediaService::class)->delete($media);
        $this->notifySuccess('File deleted.');
    }

    protected function title(): string
    {
        return 'Media Library';
    }

    protected function view(): string
    {
        return 'livewire.admin.media';
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
                'images' => MediaModel::where('type', 'image')->count(),
            ],
        ];
    }
}
