<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends BaseController
{
    /**
     * List user's chat rooms
     */
    public function rooms(Request $request): JsonResponse
    {
        $user = $request->user();

        $rooms = ChatRoom::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['members.user', 'latestMessage'])->get();

        return $this->successResponse($rooms);
    }

    /**
     * Create chat room
     */
    public function createRoom(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:users,id',
        ]);

        $room = ChatRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $request->user()->id,
        ]);

        // Add creator as member
        ChatRoomMember::create([
            'room_id' => $room->id,
            'user_id' => $request->user()->id,
        ]);

        // Add other members
        foreach ($request->member_ids as $memberId) {
            if ($memberId != $request->user()->id) {
                ChatRoomMember::create([
                    'room_id' => $room->id,
                    'user_id' => $memberId,
                ]);
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

        // Verify user is a member
        $isMember = ChatRoomMember::where('room_id', $roomId)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return $this->errorResponse(
                'You are not a member of this room',
                null,
                403,
                'PERMISSION_DENIED'
            );
        }

        $query = ChatMessage::where('room_id', $roomId)
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

        // Verify user is a member
        $isMember = ChatRoomMember::where('room_id', $roomId)
            ->where('user_id', $user->id)
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
            'room_id' => $roomId,
            'sender_id' => $user->id,
            'message' => $request->message,
        ]);

        return $this->successResponse([
            'id' => $chatMessage->id,
            'message' => $chatMessage->message,
            'created_at' => $chatMessage->created_at->toIso8601String(),
        ], 'Message sent successfully', 201);
    }
}

