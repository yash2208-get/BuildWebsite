<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'subdomain' => $this->subdomain,
            'custom_domain' => $this->custom_domain,
            'url' => $this->url,
            'status' => $this->status->value,
            'category' => $this->category,
            'description' => $this->description,
            'pages_count' => $this->pages_count,
            'views_count' => $this->views_count,
            'seo' => $this->seo,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'pages' => PageResource::collection($this->whenLoaded('pages')),
        ];
    }
}
