<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'user_id', 'subscription_id', 'subtotal', 'discount', 'tax', 'total',
        'currency', 'status', 'gateway', 'gateway_payment_id', 'line_items', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'line_items' => 'array',
            'paid_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function scopePaid(Builder $q): Builder
    {
        return $q->where('status', 'paid');
    }

    public static function nextNumber(): string
    {
        return 'INV-'.now()->format('Ym').'-'.str_pad((string) (static::whereYear('created_at', now()->year)->count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
