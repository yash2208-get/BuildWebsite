<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['website_id', 'name', 'slug', 'description', 'color'];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
