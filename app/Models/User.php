<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'avatar', 'phone', 'company',
        'country', 'timezone', 'locale', 'theme', 'status', 'bio', 'preferences',
        'meta', 'last_login_ip', 'last_login_at', 'suspended_at', 'suspension_reason',
        'two_factor_enabled',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'meta' => 'array',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /* ---------------------------------------------------------------------
     | Relationships
     * -------------------------------------------------------------------*/

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGeneration::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /* ---------------------------------------------------------------------
     | Scopes
     * -------------------------------------------------------------------*/

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (blank($term)) {
            return $q;
        }

        return $q->where(function (Builder $sub) use ($term) {
            $sub->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%");
        });
    }

    public function scopeCustomers(Builder $q): Builder
    {
        return $q->whereHas('roles', fn (Builder $r) => $r->where('name', RoleType::User->value));
    }

    public function scopeStaff(Builder $q): Builder
    {
        return $q->whereHas('roles', fn (Builder $r) => $r->whereIn('name', [
            RoleType::Admin->value, RoleType::SuperAdmin->value,
        ]));
    }

    /* ---------------------------------------------------------------------
     | Helpers
     * -------------------------------------------------------------------*/

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleType::SuperAdmin->value);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleType::Admin->value);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole([RoleType::Admin->value, RoleType::SuperAdmin->value]);
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended' || $this->suspended_at !== null;
    }

    public function primaryRole(): RoleType
    {
        return match (true) {
            $this->isSuperAdmin() => RoleType::SuperAdmin,
            $this->isAdmin() => RoleType::Admin,
            default => RoleType::User,
        };
    }

    public function homeRoute(): string
    {
        return $this->primaryRole()->homeRoute();
    }

    public function activePlan(): ?Plan
    {
        $sub = $this->relationLoaded('subscription')
            ? $this->subscription
            : $this->subscriptions()->whereIn('status', ['active', 'trialing'])->latest()->first();

        return $sub?->plan;
    }

    public function aiCreditsUsed(): int
    {
        return (int) $this->aiGenerations()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('credits_used');
    }

    public function aiCreditsRemaining(): int
    {
        $limit = $this->activePlan()?->max_ai_credits ?? 25;

        if ($limit < 0) {
            return PHP_INT_MAX;
        }

        return max(0, $limit - $this->aiCreditsUsed());
    }

    public function storageUsedMb(): float
    {
        return round(((int) $this->media()->sum('size')) / 1048576, 2);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $letters = array_map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));

        return implode('', $letters) ?: 'U';
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (blank($this->avatar)) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }
}
