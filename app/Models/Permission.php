<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'group', 'label', 'description'];

    public function getDisplayNameAttribute(): string
    {
        return $this->label ?: str($this->name)->replace(['.', '-'], ' ')->title()->toString();
    }
}
