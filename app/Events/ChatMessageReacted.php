<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roomId;
    public $messageId;
    public $reactions;

    /**
     * Create a new event instance.
     */
    public function __construct($roomId, $messageId, $reactions)
    {
        $this->roomId = $roomId;
        $this->messageId = $messageId;
        $this->reactions = $reactions;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.room.' . $this->roomId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'roomId' => $this->roomId,
            'messageId' => $this->messageId,
            'reactions' => $this->reactions,
        ];
    }
}
