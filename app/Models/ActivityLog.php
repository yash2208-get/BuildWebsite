<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'log_name', 'event', 'description', 'subject_type', 'subject_id',
        'properties', 'ip_address', 'user_agent', 'severity',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        string $severity = 'info',
        string $logName = 'default',
    ): self {
        $request = request();

        return static::create([
            'user_id' => auth()->id(),
            'log_name' => $logName,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 255) ?: null,
            'severity' => $severity,
        ]);
    }

    public function scopeEvent(Builder $q, ?string $e): Builder
    {
        return $e && $e !== 'all' ? $q->where('event', $e) : $q;
    }

    public function eventColor(): string
    {
        return match ($this->event) {
            'created' => 'emerald',
            'updated' => 'sky',
            'deleted' => 'rose',
            'login' => 'violet',
            default => 'slate',
        };
    }
}
