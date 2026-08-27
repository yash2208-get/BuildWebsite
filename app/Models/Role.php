<?php

declare(strict_types=1);

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'label', 'description', 'color', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->label ?: str($this->name)->replace('-', ' ')->title()->toString();
    }
}
