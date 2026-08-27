<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('tickets.view');
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        return $ticket->user_id === $user->id || $user->can('tickets.manage');
    }

    public function create(User $user): bool
    {
        return ! $user->isSuspended();
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        return $ticket->user_id === $user->id || $user->can('tickets.manage');
    }

    public function manage(User $user): bool
    {
        return $user->can('tickets.manage');
    }
}
