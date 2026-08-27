<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'website_id', 'name', 'slug', 'fields', 'settings', 'notify_email', 'success_message',
        'redirect_url', 'store_submissions', 'spam_protection', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'settings' => 'array',
            'store_submissions' => 'boolean',
            'spam_protection' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class)->latest();
    }

    public function validationRules(): array
    {
        $rules = [];

        foreach ((array) $this->fields as $field) {
            $key = $field['name'] ?? null;

            if (! $key) {
                continue;
            }

            $r = [($field['required'] ?? false) ? 'required' : 'nullable'];

            $r[] = match ($field['type'] ?? 'text') {
                'email' => 'email:rfc',
                'number' => 'numeric',
                'url' => 'url',
                'tel' => 'string|max:40',
                default => 'string|max:5000',
            };

            $rules["data.{$key}"] = implode('|', $r);
        }

        return $rules;
    }
}
