<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'platform.settings';

    protected $fillable = ['group', 'key', 'value', 'type', 'is_public', 'is_encrypted', 'label', 'description'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'is_encrypted' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function getTypedValueAttribute(): mixed
    {
        $raw = $this->value;

        if ($this->is_encrypted && filled($raw)) {
            try {
                $raw = Crypt::decryptString($raw);
            } catch (\Throwable) {
                return null;
            }
        }

        return match ($this->type) {
            'bool' => filter_var($raw, FILTER_VALIDATE_BOOLEAN),
            'int' => (int) $raw,
            'float' => (float) $raw,
            'json' => json_decode((string) $raw, true) ?: [],
            default => $raw,
        };
    }
}
