<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsPage extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected string $slugFrom = 'title';

    protected $fillable = ['title', 'slug', 'content', 'seo', 'status', 'show_in_footer', 'sort_order'];

    protected function casts(): array
    {
        return ['seo' => 'array', 'show_in_footer' => 'boolean'];
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }
}
