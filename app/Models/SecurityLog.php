<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'event', 'email', 'ip_address', 'user_agent', 'location', 'level', 'context',
    ];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $event, array $data = [], string $level = 'info'): self
    {
        $request = request();

        return static::create(array_merge([
            'user_id' => auth()->id(),
            'event' => $event,
            'ip_address' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 255) ?: null,
            'level' => $level,
        ], $data));
    }

    public function levelColor(): string
    {
        return match ($this->level) {
            'critical' => 'rose',
            'warning' => 'amber',
            'success' => 'emerald',
            default => 'sky',
        };
    }
}
