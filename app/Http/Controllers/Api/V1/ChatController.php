<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\Officer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends BaseController
{
    /**
     * List user's chat rooms (by officer membership)
     */
    public function rooms(Request $request): JsonResponse
    {
        $user = $request->user();
        $officerId = $user->officer?->id;
        if (!$officerId) {
            return $this->successResponse([]);
        }

        $rooms = ChatRoom::whereHas('members', function ($query) use ($officerId) {
            $query->where('officer_id', $officerId)->where('is_active', true);
        })->with(['members.officer.user', 'lastMessage'])->get();

        return $this->successResponse($rooms);
    }

    /**
     * Create chat room (custom group; command_id optional for unit/custom)
     */
    public function createRoom(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
        ]);

        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $commandId = $officer->present_station ?? \App\Models\Command::first()?->id;

        $room = ChatRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'command_id' => $commandId,
            'room_type' => 'UNIT',
            'is_active' => true,
        ]);

        ChatRoomMember::create([
            'chat_room_id' => $room->id,
            'officer_id' => $officer->id,
            'added_by' => $user->id,
            'is_active' => true,
        ]);

        foreach ($request->member_ids as $userId) {
            $memberOfficer = Officer::where('user_id', $userId)->first();
            if ($memberOfficer && $memberOfficer->id != $officer->id) {
                ChatRoomMember::firstOrCreate(
                    ['chat_room_id' => $room->id, 'officer_id' => $memberOfficer->id],
                    ['added_by' => $user->id, 'is_active' => true]
                );
            }
        }

        return $this->successResponse([
            'id' => $room->id,
            'name' => $room->name,
        ], 'Chat room created successfully', 201);
    }

    /**
     * Get room messages
     */
    public function messages(Request $request, $roomId): JsonResponse
    {
        $user = $request->user();
        $officerId = $user->officer?->id;
        if (!$officerId) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $isMember = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officerId)
            ->where('is_active', true)
            ->exists();

        if (!$isMember) {
            return $this->errorResponse(
                'You are not a member of this room',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $query = ChatMessage::where('chat_room_id', $roomId)
            ->with('sender')
            ->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 50);
        $messages = $query->paginate($perPage);

        return $this->paginatedResponse(
            $messages->items(),
            [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ]
        );
    }

    /**
     * Send message
     */
    public function sendMessage(Request $request, $roomId): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $isMember = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officer->id)
            ->where('is_active', true)
            ->exists();

        if (!$isMember) {
            return $this->errorResponse(
                'You are not a member of this room',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $chatMessage = ChatMessage::create([
            'chat_room_id' => $roomId,
            'sender_id' => $officer->id,
            'message_text' => $request->message,
        ]);

        return $this->successResponse([
            'id' => $chatMessage->id,
            'message' => $chatMessage->message_text,
            'created_at' => $chatMessage->created_at->toIso8601String(),
        ], 'Message sent successfully', 201);
    }

    /**
     * List room members (mobile: Staff Officer admin or any member)
     */
    public function members(Request $request, $roomId): JsonResponse
    {
        $user = $request->user();
        $officerId = $user->officer?->id;
        if (!$officerId) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $room = ChatRoom::findOrFail($roomId);
        $isMember = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officerId)
            ->where('is_active', true)
            ->exists();
        if (!$isMember) {
            return $this->errorResponse('You are not a member of this room', null, 403, 'PERMISSION_DENIED');
        }

        $members = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('is_active', true)
            ->with('officer.user')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'officer_id' => $m->officer_id,
                    'user_id' => $m->officer?->user_id,
                    'name' => $m->officer?->full_name,
                    'service_number' => $m->officer?->service_number,
                ];
            });

        return $this->successResponse($members);
    }

    /**
     * Add members to room (Staff Officer for command/management; creator for custom/unit)
     */
    public function addMembers(Request $request, $roomId): JsonResponse
    {
        $request->validate([
            'officer_ids' => 'required|array|min:1',
            'officer_ids.*' => 'exists:officers,id',
        ]);

        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $room = ChatRoom::findOrFail($roomId);
        $isStaffOfficer = $user->hasRole('Staff Officer');
        $sameCommand = $room->command_id && $officer->present_station == $room->command_id;
        $canAdd = $isStaffOfficer && $sameCommand;

        if (!$canAdd) {
            return $this->errorResponse('Only Staff Officers can add members to this room', null, 403, 'PERMISSION_DENIED');
        }

        $added = 0;
        foreach ($request->officer_ids as $oid) {
            $exists = ChatRoomMember::where('chat_room_id', $roomId)->where('officer_id', $oid)->exists();
            if (!$exists) {
                ChatRoomMember::create([
                    'chat_room_id' => $roomId,
                    'officer_id' => $oid,
                    'added_by' => $user->id,
                    'is_active' => true,
                ]);
                $added++;
            }
        }

        return $this->successResponse(['added' => $added], 'Members added successfully');
    }

    /**
     * Remove a member from the room (Staff Officer) or leave (current user)
     */
    public function removeMember(Request $request, $roomId, $userId): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $room = ChatRoom::findOrFail($roomId);
        $targetOfficer = Officer::where('user_id', $userId)->first();
        if (!$targetOfficer) {
            return $this->errorResponse('User/officer not found', null, 404);
        }

        $membership = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $targetOfficer->id)
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            return $this->errorResponse('Member not in this room', null, 404);
        }

        $isStaffOfficer = $user->hasRole('Staff Officer');
        $sameCommand = $room->command_id && $officer->present_station == $room->command_id;
        $isSelf = $targetOfficer->id === $officer->id;

        if ($isSelf) {
            $membership->update(['is_active' => false, 'left_at' => now()]);
            return $this->successResponse(null, 'You have left the room');
        }
        if ($isStaffOfficer && $sameCommand) {
            $membership->update(['is_active' => false, 'left_at' => now()]);
            return $this->successResponse(null, 'Member removed');
        }

        return $this->errorResponse('You cannot remove this member', null, 403, 'PERMISSION_DENIED');
    }

    /**
     * Update room name/description (custom/unit rooms only; creator or Staff Officer)
     */
    public function updateRoom(Request $request, $roomId): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $room = ChatRoom::findOrFail($roomId);
        if (($room->room_type ?? '') !== 'UNIT') {
            return $this->errorResponse('Only custom/unit rooms can be updated', null, 403, 'PERMISSION_DENIED');
        }

        $isMember = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officer->id)
            ->where('is_active', true)
            ->exists();
        if (!$isMember) {
            return $this->errorResponse('You are not a member of this room', null, 403, 'PERMISSION_DENIED');
        }

        $room->update($request->only(['name', 'description']));

        return $this->successResponse($room->fresh(), 'Room updated successfully');
    }

    /**
     * Leave a custom/unit room (soft-leave: is_active = false)
     */
    public function leave(Request $request, $roomId): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $room = ChatRoom::findOrFail($roomId);
        $membership = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officer->id)
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            return $this->errorResponse('You are not a member of this room', null, 404);
        }

        $membership->update(['is_active' => false, 'left_at' => now()]);

        return $this->successResponse(null, 'You have left the room');
    }
}

