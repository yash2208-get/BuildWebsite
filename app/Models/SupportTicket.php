<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'user_id', 'assigned_to', 'subject', 'message', 'category',
        'priority', 'status', 'attachments', 'resolved_at', 'last_reply_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'status' => TicketStatus::class,
            'resolved_at' => 'datetime',
            'last_reply_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $t) {
            $t->reference ??= 'TKT-'.strtoupper(Str::random(8));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->whereIn('status', ['open', 'pending', 'answered']);
    }

    public function priorityColor(): string
    {
        return match ($this->priority) {
            'urgent' => 'rose',
            'high' => 'amber',
            'low' => 'slate',
            default => 'sky',
        };
    }
}
