<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiGenerationType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'website_id', 'type', 'provider', 'model', 'prompt', 'options', 'result',
        'status', 'error', 'tokens_used', 'credits_used', 'duration_ms',
    ];

    protected function casts(): array
    {
        return ['options' => 'array', 'type' => AiGenerationType::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function scopeOfType(Builder $q, ?string $t): Builder
    {
        return $t && $t !== 'all' ? $q->where('type', $t) : $q;
    }

    public function decodedResult(): array
    {
        return json_decode((string) $this->result, true) ?: [];
    }
}
