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
        'parent_id',
        'message_text',
        'attachment_url',
        'is_broadcast',
        'is_deleted',
        'is_pinned',
        'pinned_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_broadcast' => 'boolean',
            'is_deleted' => 'boolean',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'deleted_at' => 'datetime',
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

    public function parent()
    {
        return $this->belongsTo(ChatMessage::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(ChatMessage::class, 'parent_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChatMessageReaction::class, 'chat_message_id');
    }
}

