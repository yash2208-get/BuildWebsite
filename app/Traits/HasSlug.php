<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $source = $model->slugSource();

            if (blank($model->slug) && filled($model->{$source})) {
                $model->slug = $model->generateUniqueSlug(Str::slug((string) $model->{$source}));
            }
        });
    }

    protected function slugSource(): string
    {
        return property_exists($this, 'slugFrom') ? $this->slugFrom : 'name';
    }

    public function generateUniqueSlug(string $base): string
    {
        $base = $base ?: Str::random(8);
        $slug = $base;
        $i = 2;

        while ($this->slugQuery($slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    protected function slugQuery(string $slug): Builder
    {
        $q = static::query()->where('slug', $slug);

        if ($this->exists) {
            $q->whereKeyNot($this->getKey());
        }

        if (in_array('website_id', $this->getFillable(), true) && $this->website_id) {
            $q->where('website_id', $this->website_id);
        }

        return $q;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
