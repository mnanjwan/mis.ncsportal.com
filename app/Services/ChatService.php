<?php

namespace App\Services;

use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\Command;
use App\Models\Officer;

class ChatService
{
    /**
     * Sync official chat rooms for an officer based on station, unit, and rank
     */
    public function syncOfficerRooms(Officer $officer): void
    {
        $officerId = $officer->id;

        // 1. Command Room (Auto-join based on present_station)
        if ($officer->present_station) {
            $command = Command::find($officer->present_station);
            if ($command) {
                $room = ChatRoom::firstOrCreate(
                    ['command_id' => $command->id, 'room_type' => 'command'],
                    [
                        'name' => $command->name . ' Command',
                        'description' => 'Official room for ' . $command->name,
                        'is_active' => true
                    ]
                );
                $this->ensureMembership($room->id, $officerId);
            }
        }

        // 2. Unit Room (Auto-join based on unit attribute)
        if ($officer->unit) {
            $room = ChatRoom::firstOrCreate(
                ['room_type' => 'UNIT', 'name' => $officer->unit . ' Unit'],
                [
                    'description' => 'Official room for ' . $officer->unit,
                    'is_active' => true
                ]
            );
            $this->ensureMembership($room->id, $officerId);
        }

        // 3. Management Room (Auto-join for AC and above)
        $promotionService = new \App\Services\PromotionService();
        $rankAbbr = $promotionService->normalizeRankToAbbreviation($officer->substantive_rank);

        $mRanks = ['CGC', 'DCG', 'ACG', 'DC', 'AC'];
        $isMgmt = in_array(strtoupper($rankAbbr), $mRanks);

        if ($isMgmt) {
            $room = ChatRoom::firstOrCreate(
                ['room_type' => 'management', 'name' => 'Management Chat'],
                [
                    'description' => 'Official room for Management Officers',
                    'is_active' => true
                ]
            );
            $this->ensureMembership($room->id, $officerId);
        }
    }

    /**
     * Helper to ensure active membership exists
     */
    private function ensureMembership(int $roomId, int $officerId): void
    {
        $member = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officerId)
            ->first();

        // If newly joined or was inactive/left, send notifications
        if (!$member || !$member->is_active) {
            ChatRoomMember::updateOrCreate(
                ['chat_room_id' => $roomId, 'officer_id' => $officerId],
                ['is_active' => true, 'joined_at' => now(), 'left_at' => null]
            );

            // Trigger Notifications instantly
            $this->sendAdditionNotifications($roomId, $officerId);
        }
    }

    /**
     * Send notifications to user and command admins
     */
    private function sendAdditionNotifications(int $roomId, int $officerId): void
    {
        $room = ChatRoom::find($roomId);
        $officer = Officer::with('user')->find($officerId);

        if (!$room || !$officer || !$officer->user) {
            return;
        }

        try {
            /** @var \App\Services\NotificationService $notificationService */
            $notificationService = app(NotificationService::class);

            // 1. Notify the individual officer (Push + Email)
            $notificationService->notifyAddedToChatRoom($officer, $room);

            // 2. Notify Command Staff Officers (Admins) for audit
            $notificationService->notifyChatRoomAdmins($room, $officer);

            \Illuminate\Support\Facades\Log::info('Chat addition notifications sent', [
                'room_id' => $roomId,
                'officer_id' => $officerId
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send chat notifications', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
