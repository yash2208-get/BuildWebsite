<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Template;
use App\Models\User;

class TemplatePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('templates.view');
    }

    public function view(User $user, Template $template): bool
    {
        return $template->is_active || $template->user_id === $user->id || $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->can('templates.create');
    }

    public function update(User $user, Template $template): bool
    {
        return $user->can('templates.update') || $template->user_id === $user->id;
    }

    public function delete(User $user, Template $template): bool
    {
        return $user->can('templates.delete') || $template->user_id === $user->id;
    }

    public function use(User $user, Template $template): bool
    {
        if (! $template->is_premium) {
            return true;
        }

        return $user->activePlan() !== null || $user->isStaff();
    }
}
