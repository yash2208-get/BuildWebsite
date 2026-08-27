<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'path', 'disk', 'type', 'size', 'status', 'error', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getHumanSizeAttribute(): string
    {
        $b = (int) $this->size;
        $u = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($b >= 1024 && $i < 3) {
            $b /= 1024;
            $i++;
        }

        return round($b, 1).' '.$u[$i];
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'completed' => 'emerald',
            'failed' => 'rose',
            'running' => 'sky',
            default => 'amber',
        };
    }
}
