<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Contacts extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function markRead(int $id): void
    {
        ContactMessage::findOrFail($id)->update(['status' => 'read', 'read_at' => now()]);
        $this->notifySuccess('Marked as read.');
    }

    public function markReplied(int $id): void
    {
        ContactMessage::findOrFail($id)->update(['status' => 'replied']);
        $this->notifySuccess('Marked as replied.');
    }

    public function deleteMessage(int $id): void
    {
        ContactMessage::findOrFail($id)->delete();
        $this->notifySuccess('Message deleted.');
    }

    protected function title(): string
    {
        return 'Contact Messages';
    }

    protected function view(): string
    {
        return 'livewire.super.contacts';
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'subject', 'message'];
    }

    protected function query(): Builder
    {
        return ContactMessage::query();
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => ContactMessage::count(),
                'unread' => ContactMessage::where('status', 'unread')->count(),
                'replied' => ContactMessage::where('status', 'replied')->count(),
            ],
        ];
    }
}
