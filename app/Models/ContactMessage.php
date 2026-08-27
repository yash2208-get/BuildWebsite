<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message', 'ip_address', 'status', 'is_read', 'admin_notes',
    ];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }
}
