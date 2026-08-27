<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\RoleType;
use App\Livewire\ResourceComponent;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Users extends ResourceComponent
{
    public ?int $viewing = null;

    protected function title(): string
    {
        return 'User Management';
    }

    protected function view(): string
    {
        return 'livewire.admin.users';
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'username'];
    }

    protected function query(): Builder
    {
        return User::query()
            ->customers()
            ->withCount('websites')
            ->with('subscription.plan');
    }

    public function toggleStatus(int $id): void
    {
        $user = User::customers()->findOrFail($id);
        $this->authorize('update', $user);

        $next = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $next]);

        ActivityLog::record('updated', "{$user->name} was ".($next === 'active' ? 'reactivated' : 'suspended'), $user);
        $this->notifySuccess("{$user->name} is now {$next}.");
    }

    public function bulkSuspend(): void
    {
        $count = User::customers()->whereIn('id', $this->selected)->update(['status' => 'suspended']);
        $this->clearSelection();
        $this->notifySuccess("{$count} users suspended.");
    }

    public function bulkActivate(): void
    {
        $count = User::customers()->whereIn('id', $this->selected)->update(['status' => 'active']);
        $this->clearSelection();
        $this->notifySuccess("{$count} users activated.");
    }

    protected function viewData(): array
    {
        return [
            'user' => $this->viewing ? User::withCount('websites', 'media', 'aiGenerations')->find($this->viewing) : null,
            'totals' => [
                'all' => User::customers()->count(),
                'active' => User::customers()->where('status', 'active')->count(),
                'suspended' => User::customers()->where('status', 'suspended')->count(),
                'new' => User::customers()->where('created_at', '>=', now()->subDays(30))->count(),
            ],
        ];
    }
}
