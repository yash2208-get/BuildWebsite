<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Enums\RoleType;
use App\Livewire\ResourceComponent;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Admins extends ResourceComponent
{
    public bool $showCreate = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'admin';

    protected function title(): string
    {
        return 'Admin Management';
    }

    protected function view(): string
    {
        return 'livewire.super.admins';
    }

    protected function searchable(): array
    {
        return ['name', 'email'];
    }

    protected function query(): Builder
    {
        return User::query()->staff()->with('roles')->withCount('activityLogs');
    }

    public function createAdmin(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', 'in:admin,super-admin'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $user->assignRole($data['role']);

        ActivityLog::record('created', "Staff account created for {$user->email} ({$data['role']})", $user);

        $this->reset('showCreate', 'name', 'email', 'password');
        $this->notifySuccess('Administrator account created.');
    }

    public function changeRole(int $id, string $role): void
    {
        $user = User::staff()->findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->notifyError('You cannot change your own role.');

            return;
        }

        $user->syncRoles([$role]);
        ActivityLog::record('updated', "{$user->name} role changed to {$role}", $user);

        $this->notifySuccess("{$user->name} is now a ".str_replace('-', ' ', $role).'.');
    }

    public function toggleStatus(int $id): void
    {
        $user = User::staff()->findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->notifyError('You cannot suspend your own account.');

            return;
        }

        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);
        $this->notifySuccess("{$user->name} is now {$user->status}.");
    }

    public function revokeAccess(int $id): void
    {
        $user = User::staff()->findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->notifyError('You cannot revoke your own access.');

            return;
        }

        $user->syncRoles([RoleType::User->value]);
        ActivityLog::record('updated', "Staff access revoked for {$user->email}", $user);

        $this->notifySuccess('Staff access revoked — the account is now a regular user.');
    }

    protected function viewData(): array
    {
        return [
            'roles' => Role::whereIn('name', ['admin', 'super-admin'])->get(),
            'totals' => [
                'all' => User::staff()->count(),
                'super' => User::role('super-admin')->count(),
                'admin' => User::role('admin')->count(),
            ],
        ];
    }
}
