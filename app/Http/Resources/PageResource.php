<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'is_homepage' => $this->is_homepage,
            'status' => $this->status,
            'seo' => $this->seo,
            'views_count' => $this->views_count,
            'url' => $this->url,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
