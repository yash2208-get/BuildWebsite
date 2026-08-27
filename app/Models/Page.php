<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, HasSlug, LogsActivity, SoftDeletes;

    protected string $slugFrom = 'title';

    protected $fillable = [
        'website_id', 'title', 'slug', 'path', 'type', 'html', 'css', 'js', 'grapes_data',
        'blocks', 'seo', 'layout', 'is_homepage', 'show_in_nav', 'sort_order', 'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'grapes_data' => 'array',
            'blocks' => 'array',
            'seo' => 'array',
            'is_homepage' => 'boolean',
            'show_in_nav' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest('version');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }

    public function getUrlAttribute(): string
    {
        $base = $this->website?->url ?? '';

        return $this->is_homepage ? $base : rtrim($base, '/').'/'.ltrim($this->slug, '/');
    }

    public function nextVersion(): int
    {
        return (int) $this->revisions()->max('version') + 1;
    }
}
