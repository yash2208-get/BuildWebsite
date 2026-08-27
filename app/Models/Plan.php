<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'tagline', 'description', 'price_monthly', 'price_yearly', 'currency',
        'trial_days', 'max_websites', 'max_pages', 'max_storage_mb', 'max_ai_credits',
        'max_team_members', 'allows_custom_domain', 'allows_export', 'allows_white_label',
        'allows_remove_branding', 'features', 'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'allows_custom_domain' => 'boolean',
            'allows_export' => 'boolean',
            'allows_white_label' => 'boolean',
            'allows_remove_branding' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function isFree(): bool
    {
        return (float) $this->price_monthly <= 0;
    }

    public function isUnlimited(string $key): bool
    {
        return (int) $this->{$key} < 0;
    }

    public function limitLabel(string $key): string
    {
        $v = (int) $this->{$key};

        return $v < 0 ? 'Unlimited' : number_format($v);
    }

    public function yearlySavingPercent(): int
    {
        $monthlyTotal = (float) $this->price_monthly * 12;

        if ($monthlyTotal <= 0) {
            return 0;
        }

        return (int) round(100 - (((float) $this->price_yearly / $monthlyTotal) * 100));
    }
}
