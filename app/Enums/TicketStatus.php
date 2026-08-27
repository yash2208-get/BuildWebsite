<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case Answered = 'answered';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'sky',
            self::Pending => 'amber',
            self::Answered => 'violet',
            self::Resolved => 'emerald',
            self::Closed => 'slate',
        };
    }
}
