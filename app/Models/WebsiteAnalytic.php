<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteAnalytic extends Model
{
    use HasFactory;

    protected $table = 'website_analytics';

    protected $fillable = [
        'website_id', 'page_id', 'date', 'views', 'unique_visitors', 'sessions',
        'bounce_rate', 'avg_duration', 'referrers', 'devices', 'countries',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'referrers' => 'array',
            'devices' => 'array',
            'countries' => 'array',
            'bounce_rate' => 'float',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
