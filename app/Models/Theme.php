<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'tokens', 'typography', 'spacing',
        'custom_css', 'preview_image', 'is_global', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tokens' => 'array',
            'typography' => 'array',
            'spacing' => 'array',
            'is_global' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
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

    public function scopeGlobal(Builder $q): Builder
    {
        return $q->where('is_global', true);
    }

    public function color(string $key, string $default = '#6366f1'): string
    {
        return (string) data_get($this->tokens, "colors.{$key}", $default);
    }

    public function toCssVariables(): string
    {
        $lines = [];

        foreach ((array) data_get($this->tokens, 'colors', []) as $k => $v) {
            $lines[] = "  --color-{$k}: {$v};";
        }

        foreach ((array) $this->typography as $k => $v) {
            if (is_scalar($v)) {
                $lines[] = "  --font-{$k}: {$v};";
            }
        }

        return ":root{\n".implode("\n", $lines)."\n}";
    }
}
