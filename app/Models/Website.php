<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebsiteStatus;
use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends Model
{
    use HasFactory, HasSlug, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id', 'theme_id', 'name', 'slug', 'subdomain', 'custom_domain', 'domain_status',
        'domain_verification_token', 'description', 'favicon', 'logo', 'thumbnail', 'category',
        'status', 'settings', 'seo', 'integrations', 'custom_css', 'custom_js', 'head_scripts',
        'body_scripts', 'is_password_protected', 'access_password', 'maintenance_mode',
        'published_at', 'last_edited_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'seo' => 'array',
            'integrations' => 'array',
            'status' => WebsiteStatus::class,
            'is_password_protected' => 'boolean',
            'maintenance_mode' => 'boolean',
            'published_at' => 'datetime',
            'last_edited_at' => 'datetime',
        ];
    }

    protected $hidden = ['access_password'];

    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('sort_order');
    }

    public function homepage(): HasOne
    {
        return $this->hasOne(Page::class)->where('is_homepage', true);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function blogCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(WebsiteAnalytic::class);
    }

    /* ------------------------------------------------------------------ */

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (blank($term)) {
            return $q;
        }

        return $q->where(fn (Builder $s) => $s
            ->where('name', 'like', "%{$term}%")
            ->orWhere('subdomain', 'like', "%{$term}%")
            ->orWhere('custom_domain', 'like', "%{$term}%"));
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', WebsiteStatus::Published->value);
    }

    public function scopeOwnedBy(Builder $q, ?User $user): Builder
    {
        return $user ? $q->where('user_id', $user->id) : $q;
    }

    /* ------------------------------------------------------------------ */

    public function isPublished(): bool
    {
        return $this->status === WebsiteStatus::Published;
    }

    public function getUrlAttribute(): string
    {
        if (filled($this->custom_domain) && $this->domain_status === 'verified') {
            return 'https://'.$this->custom_domain;
        }

        return url("/sites/{$this->subdomain}");
    }

    public function getDisplayDomainAttribute(): string
    {
        return filled($this->custom_domain) && $this->domain_status === 'verified'
            ? $this->custom_domain
            : $this->subdomain.'.'.config('platform.subdomain_host', 'aurorabuild.app');
    }

    public function seoValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->seo, $key, $default);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function touchEdited(): void
    {
        $this->forceFill(['last_edited_at' => now()])->saveQuietly();
    }
}
