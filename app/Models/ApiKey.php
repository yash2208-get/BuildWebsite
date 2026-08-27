<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'key_prefix', 'key_hash', 'scopes', 'rate_limit', 'usage_count',
        'last_used_ip', 'last_used_at', 'expires_at', 'is_active',
    ];

    protected $hidden = ['key_hash'];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return array{model: self, plain: string} */
    public static function issue(array $attributes): array
    {
        $plain = 'ab_'.Str::random(48);

        $model = static::create(array_merge($attributes, [
            'key_prefix' => substr($plain, 0, 11),
            'key_hash' => hash('sha256', $plain),
        ]));

        return ['model' => $model, 'plain' => $plain];
    }

    public static function findByPlain(string $plain): ?self
    {
        return static::where('key_hash', hash('sha256', $plain))->where('is_active', true)->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getMaskedAttribute(): string
    {
        return $this->key_prefix.str_repeat('•', 12);
    }
}
