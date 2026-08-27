<?php

declare(strict_types=1);

namespace App\Livewire\Super;

use App\Livewire\BaseComponent;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Permissions;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Roles extends BaseComponent
{
    public ?int $editing = null;

    public bool $showCreate = false;

    public string $name = '';

    public string $description = '';

    /** @var array<int, string> */
    public array $granted = [];

    public function mount(): void
    {
        $first = Role::orderBy('id')->first();

        if ($first) {
            $this->selectRole($first->id);
        }
    }

    public function selectRole(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->editing = $role->id;
        $this->name = $role->name;
        $this->description = (string) ($role->description ?? '');
        $this->granted = $role->permissions->pluck('name')->all();
    }

    public function togglePermission(string $permission): void
    {
        in_array($permission, $this->granted, true)
            ? $this->granted = array_values(array_diff($this->granted, [$permission]))
            : $this->granted[] = $permission;
    }

    public function toggleGroup(string $group): void
    {
        $names = collect(Permissions::catalogue()[$group] ?? [])->keys()->all();
        $allOn = empty(array_diff($names, $this->granted));

        $this->granted = $allOn
            ? array_values(array_diff($this->granted, $names))
            : array_values(array_unique(array_merge($this->granted, $names)));
    }

    public function savePermissions(): void
    {
        $role = Role::findOrFail($this->editing);

        if ($role->name === 'super-admin') {
            $this->notifyError('The Super Admin role always retains every permission.');

            return;
        }

        $role->syncPermissions($this->granted);
        $role->update(['description' => $this->description]);

        $this->notifySuccess("Permissions updated for {$role->name}.");
    }

    public function createRole(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:60', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role = Role::create([
            'name' => Str::slug($data['name']),
            'guard_name' => 'web',
            'description' => $data['description'],
        ]);

        $this->reset('showCreate', 'name', 'description');
        $this->selectRole($role->id);
        $this->notifySuccess('Role created.');
    }

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['super-admin', 'admin', 'user'], true)) {
            $this->notifyError('Core platform roles cannot be deleted.');

            return;
        }

        if ($role->users()->exists()) {
            $this->notifyError('Reassign the users on this role before deleting it.');

            return;
        }

        $role->delete();
        $this->editing = null;
        $this->notifySuccess('Role deleted.');
    }

    public function render()
    {
        return view('livewire.super.roles', [
            'roles' => Role::withCount('users', 'permissions')->orderBy('id')->get(),
            'catalogue' => Permissions::catalogue(),
            'role' => $this->editing ? Role::withCount('users')->find($this->editing) : null,
            'totalPermissions' => Permission::count(),
        ])->layoutData($this->layoutData('Roles & Permissions'));
    }
}
