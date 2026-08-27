<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'plan_id', 'status', 'billing_cycle', 'amount', 'currency', 'gateway',
        'gateway_subscription_id', 'trial_ends_at', 'starts_at', 'ends_at', 'canceled_at',
        'auto_renew', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'auto_renew' => 'boolean',
            'meta' => 'array',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Trialing->value]);
    }

    public function isActive(): bool
    {
        return $this->status->isUsable() && (! $this->ends_at || $this->ends_at->isFuture());
    }

    public function onTrial(): bool
    {
        return $this->status === SubscriptionStatus::Trialing
            && $this->trial_ends_at?->isFuture() === true;
    }

    public function daysRemaining(): int
    {
        return $this->ends_at ? max(0, (int) now()->diffInDays($this->ends_at, false)) : 0;
    }
}
