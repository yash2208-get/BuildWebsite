<?php

declare(strict_types=1);

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PastDue => 'Past Due',
            default => ucfirst($this->value),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'emerald',
            self::Trialing => 'sky',
            self::PastDue => 'amber',
            self::Canceled, self::Expired => 'rose',
        };
    }

    public function isUsable(): bool
    {
        return in_array($this, [self::Active, self::Trialing], true);
    }
}
