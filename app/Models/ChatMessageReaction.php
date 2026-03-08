<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessageReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_message_id',
        'officer_id',
        'reaction_type',
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'chat_message_id');
    }

    public function officer()
    {
        return $this->belongsTo(Officer::class, 'officer_id');
    }
}
