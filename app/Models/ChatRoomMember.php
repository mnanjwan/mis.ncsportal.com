<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoomMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'officer_id',
        'added_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

