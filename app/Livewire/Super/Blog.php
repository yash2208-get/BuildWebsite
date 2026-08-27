<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Blog extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function publishPost(int $id): void
    {
        BlogPost::findOrFail($id)->update(['status' => 'published', 'published_at' => now()]);
        $this->notifySuccess('Post published.');
    }

    public function deletePost(int $id): void
    {
        BlogPost::findOrFail($id)->delete();
        $this->notifySuccess('Post deleted.');
    }

    protected function title(): string
    {
        return 'Blog Management';
    }

    protected function view(): string
    {
        return 'livewire.super.blog';
    }

    protected function searchable(): array
    {
        return ['title', 'excerpt'];
    }

    protected function query(): Builder
    {
        return BlogPost::query()
            ->with('user', 'category');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => BlogPost::count(),
                'published' => BlogPost::where('status', 'published')->count(),
                'draft' => BlogPost::where('status', 'draft')->count(),
            ],
        ];
    }
}
