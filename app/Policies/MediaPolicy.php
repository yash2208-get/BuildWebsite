<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('media.view');
    }

    public function view(User $user, Media $media): bool
    {
        return $media->user_id === $user->id || $user->can('media.view');
    }

    public function create(User $user): bool
    {
        return $user->can('media.upload') && ! $user->isSuspended();
    }

    public function delete(User $user, Media $media): bool
    {
        return ($media->user_id === $user->id || $user->isStaff()) && $user->can('media.delete');
    }
}
