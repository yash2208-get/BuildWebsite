<?php

declare(strict_types=1);

namespace App\Livewire\App;

use App\Livewire\BaseComponent;
use App\Models\Media;
use App\Services\Platform\MediaService;
use App\Support\Exceptions\QuotaExceededException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts.app')]
class MediaLibrary extends BaseComponent
{
    use WithFileUploads, WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $uploads = [];

    public array $selected = [];

    public ?int $editing = null;

    public string $altText = '';

    public function updatedUploads(): void
    {
        $service = app(MediaService::class);
        $stored = 0;

        foreach ($this->uploads as $file) {
            try {
                $service->store($this->user(), $file);
                $stored++;
            } catch (QuotaExceededException $e) {
                $this->notifyError($e->getMessage());
                break;
            } catch (Throwable $e) {
                report($e);
                $this->notifyError('Could not upload '.$file->getClientOriginalName());
            }
        }

        $this->reset('uploads');

        if ($stored) {
            $this->notifySuccess($stored.' '.str('file')->plural($stored).' uploaded.');
        }
    }

    public function startEditing(int $id): void
    {
        $media = Media::where('user_id', $this->user()->id)->findOrFail($id);

        $this->editing = $id;
        $this->altText = (string) $media->alt_text;
    }

    public function saveAlt(): void
    {
        $media = Media::where('user_id', $this->user()->id)->findOrFail($this->editing);
        $this->authorize('update', $media);

        $this->validate(['altText' => ['nullable', 'string', 'max:255']]);

        $media->update(['alt_text' => $this->altText]);

        $this->reset('editing', 'altText');
        $this->notifySuccess('Alt text updated.');
    }

    public function delete(int $id): void
    {
        $media = Media::where('user_id', $this->user()->id)->findOrFail($id);
        $this->authorize('delete', $media);

        app(MediaService::class)->delete($media);

        $this->notifySuccess('File deleted.');
    }

    public function deleteSelected(): void
    {
        $items = Media::where('user_id', $this->user()->id)->whereIn('id', $this->selected)->get();

        foreach ($items as $media) {
            if ($this->user()->can('delete', $media)) {
                app(MediaService::class)->delete($media);
            }
        }

        $count = $items->count();
        $this->selected = [];
        $this->notifySuccess($count.' '.str('file')->plural($count).' deleted.');
    }

    public function render()
    {
        $media = Media::where('user_id', $this->user()->id)
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('original_name', 'like', "%{$this->search}%")))
            ->when($this->type !== 'all', fn ($q) => $q->where('type', $this->type))
            ->latest()
            ->paginate(24);

        return view('livewire.app.media-library', [
            'media' => $media,
            'usage' => app(\App\Services\Website\QuotaService::class)->usage($this->user())['storage'],
            'counts' => [
                'all' => Media::where('user_id', $this->user()->id)->count(),
                'image' => Media::where('user_id', $this->user()->id)->where('type', 'image')->count(),
                'video' => Media::where('user_id', $this->user()->id)->where('type', 'video')->count(),
                'document' => Media::where('user_id', $this->user()->id)->where('type', 'document')->count(),
            ],
        ])->layoutData($this->layoutData('Media Library'));
    }
}
