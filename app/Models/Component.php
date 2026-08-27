<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'slug', 'category', 'description', 'html', 'css', 'schema',
        'icon', 'preview_image', 'is_global', 'is_premium', 'is_active', 'uses_count', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'is_global' => 'boolean',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $q->when($term, fn (Builder $w) => $w->where(fn (Builder $x) => $x
            ->where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")));
    }

    public function scopeCategory(Builder $q, ?string $c): Builder
    {
        return $c && $c !== 'all' ? $q->where('category', $c) : $q;
    }
}
