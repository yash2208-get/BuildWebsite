<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\ResourceComponent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Users extends ResourceComponent
{
    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function toggleStatus(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);
        $this->notifySuccess("{$user->name} is now {$user->status}.");
    }

    public function impersonate(int $id): void
    {
        $user = User::findOrFail($id);

        abort_if($user->isSuperAdmin(), 403, 'Super admins cannot be impersonated.');

        session(['impersonator_id' => auth()->id()]);
        auth()->login($user);

        $this->redirectRoute($user->homeRoute(), navigate: false);
    }

    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->delete();
        $this->notifySuccess('User deleted.');
    }

    protected function title(): string
    {
        return 'All Users';
    }

    protected function view(): string
    {
        return 'livewire.super.users';
    }

    protected function searchable(): array
    {
        return ['name', 'email', 'username'];
    }

    protected function query(): Builder
    {
        return User::query()
            ->with('roles', 'subscription.plan')
            ->withCount('websites');
    }

    protected function viewData(): array
    {
        return [
            'totals' => [
                'all' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'staff' => User::staff()->count(),
            ],
        ];
    }
}
