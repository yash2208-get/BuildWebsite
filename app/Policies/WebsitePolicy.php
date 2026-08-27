<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Website;

class WebsitePolicy
{
    /** Super admins bypass every check. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('websites.view') || $user->can('websites.create');
    }

    public function view(User $user, Website $website): bool
    {
        return $this->owns($user, $website) || $user->can('websites.view');
    }

    public function create(User $user): bool
    {
        return $user->can('websites.create') && ! $user->isSuspended();
    }

    public function update(User $user, Website $website): bool
    {
        if ($user->isSuspended()) {
            return false;
        }

        return ($this->owns($user, $website) && $user->can('websites.update'))
            || ($user->isStaff() && $user->can('websites.update'));
    }

    public function delete(User $user, Website $website): bool
    {
        return ($this->owns($user, $website) && $user->can('websites.delete'))
            || $user->can('websites.delete');
    }

    public function publish(User $user, Website $website): bool
    {
        return $this->update($user, $website) && $user->can('websites.publish');
    }

    public function clone(User $user, Website $website): bool
    {
        return $this->view($user, $website) && $user->can('websites.create');
    }

    public function export(User $user, Website $website): bool
    {
        if (! $this->view($user, $website)) {
            return false;
        }

        if ($user->isStaff()) {
            return $user->can('websites.export');
        }

        return $user->can('websites.export')
            && (bool) $user->activePlan()?->allows_export;
    }

    private function owns(User $user, Website $website): bool
    {
        return $website->user_id === $user->id;
    }
}
