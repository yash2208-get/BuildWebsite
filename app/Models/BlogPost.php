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
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory, HasSlug, LogsActivity, SoftDeletes;

    protected string $slugFrom = 'title';

    protected $fillable = [
        'website_id', 'user_id', 'blog_category_id', 'title', 'slug', 'excerpt', 'content',
        'featured_image', 'tags', 'seo', 'status', 'is_featured', 'ai_generated',
        'reading_minutes', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'seo' => 'array',
            'is_featured' => 'boolean',
            'ai_generated' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $post) {
            $words = str_word_count(strip_tags((string) $post->content));
            $post->reading_minutes = max(1, (int) ceil($words / 200));

            if (blank($post->excerpt) && filled($post->content)) {
                $post->excerpt = Str::limit(strip_tags((string) $post->content), 180);
            }
        });
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')
            ->where(fn (Builder $s) => $s->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeSearch(Builder $q, ?string $t): Builder
    {
        return blank($t) ? $q : $q->where(fn (Builder $s) => $s
            ->where('title', 'like', "%{$t}%")->orWhere('content', 'like', "%{$t}%"));
    }
}
