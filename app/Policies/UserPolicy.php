<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->is($model) || $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return true;
        }

        // Admins may never edit super admins.
        if ($model->isSuperAdmin()) {
            return false;
        }

        return $user->can('users.update');
    }

    public function delete(User $user, User $model): bool
    {
        return ! $user->is($model) && ! $model->isSuperAdmin() && $user->can('users.delete');
    }

    public function suspend(User $user, User $model): bool
    {
        return ! $user->is($model) && ! $model->isSuperAdmin() && $user->can('users.suspend');
    }

    public function impersonate(User $user, User $model): bool
    {
        return ! $user->is($model) && ! $model->isStaff() && $user->can('users.impersonate');
    }
}
