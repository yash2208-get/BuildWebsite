<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function view(User $user, Page $page): bool
    {
        return $this->owns($user, $page) || $user->can('websites.view');
    }

    public function create(User $user): bool
    {
        return $user->can('websites.update') && ! $user->isSuspended();
    }

    public function update(User $user, Page $page): bool
    {
        return ! $user->isSuspended()
            && ($this->owns($user, $page) || $user->isStaff())
            && $user->can('websites.update');
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->update($user, $page);
    }

    private function owns(User $user, Page $page): bool
    {
        return $page->website?->user_id === $user->id;
    }
}
