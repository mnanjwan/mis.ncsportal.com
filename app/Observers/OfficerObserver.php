<?php

namespace App\Observers;

use App\Models\Officer;
use App\Services\ChatService;

class OfficerObserver
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Handle the Officer "saved" event.
     * Triggers on both create and update.
     */
    public function saved(Officer $officer): void
    {
        // If station, rank, or unit changed, sync chat rooms instantly
        if ($officer->isDirty(['present_station', 'substantive_rank', 'unit'])) {
            $this->chatService->syncOfficerRooms($officer);
        }
    }

    /**
     * Handle the Officer "created" event.
     */
    public function created(Officer $officer): void
    {
        $this->chatService->syncOfficerRooms($officer);
    }
}
