<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory, HasSlug, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'category', 'tags', 'preview_image',
        'demo_url', 'html', 'css', 'grapes_data', 'pages', 'is_premium', 'is_active',
        'uses_count', 'rating', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'grapes_data' => 'array',
            'pages' => 'array',
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'rating' => 'float',
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

    public function scopeCategory(Builder $q, ?string $category): Builder
    {
        return $category && $category !== 'all' ? $q->where('category', $category) : $q;
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return blank($term) ? $q : $q->where(fn (Builder $s) => $s
            ->where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%"));
    }
}
