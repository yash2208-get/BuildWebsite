<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'description', 'type', 'value', 'min_amount', 'max_redemptions',
        'redemptions', 'max_per_user', 'plan_ids', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'plan_ids' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'value' => 'decimal:2',
        ];
    }

    public function redemptionRecords(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function scopeValid(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn (Builder $s) => $s->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $s) => $s->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function isRedeemable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return ! $this->max_redemptions || $this->redemptions < $this->max_redemptions;
    }

    public function discountFor(float $amount): float
    {
        if ($this->min_amount && $amount < (float) $this->min_amount) {
            return 0.0;
        }

        return $this->type === 'percentage'
            ? round($amount * ((float) $this->value / 100), 2)
            : min((float) $this->value, $amount);
    }

    public function getDisplayValueAttribute(): string
    {
        return $this->type === 'percentage'
            ? rtrim(rtrim((string) $this->value, '0'), '.').'%'
            : '$'.number_format((float) $this->value, 2);
    }
}
