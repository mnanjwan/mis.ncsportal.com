<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'title',
        'message',
        'entity_type',
        'entity_id',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'is_read' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

