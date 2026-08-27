<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'data', 'ip_address', 'user_agent', 'referrer', 'is_spam', 'is_read'];

    protected function casts(): array
    {
        return ['data' => 'array', 'is_spam' => 'boolean', 'is_read' => 'boolean'];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
