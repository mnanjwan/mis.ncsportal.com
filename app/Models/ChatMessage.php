<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'sender_id',
        'message_text',
        'attachment_url',
        'is_broadcast',
    ];

    protected function casts(): array
    {
        return [
            'is_broadcast' => 'boolean',
        ];
    }

    // Relationships
    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function sender()
    {
        return $this->belongsTo(Officer::class, 'sender_id');
    }
}

