<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleType: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::User => 'User',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SuperAdmin => 'fuchsia',
            self::Admin => 'sky',
            self::User => 'emerald',
        };
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::SuperAdmin => 'super.dashboard',
            self::Admin => 'admin.dashboard',
            self::User => 'app.dashboard',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
