<?php

declare(strict_types=1);

namespace App\Enums;

enum WebsiteStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'amber',
            self::Published => 'emerald',
            self::Suspended => 'rose',
            self::Archived => 'slate',
        };
    }
}
