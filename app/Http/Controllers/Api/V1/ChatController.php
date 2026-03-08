<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ChatMessage;
use App\Events\ChatMessageSent;
use App\Events\ChatMessageDeleted;
use App\Events\ChatMessageReacted;
use App\Events\ChatMessagePinned;
use App\Events\ChatMessageBroadcasted;
use App\Models\ChatRoom;
use App\Models\ChatRoomMember;
use App\Models\ChatMessageReaction;
use App\Models\Officer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends BaseController
{
    /**
     * Search officers system-wide for starting a Direct Message
     */
    public function searchOfficers(Request $request): JsonResponse
    {
        $query = $request->get('search', '');
        $perPage = min((int) $request->get('per_page', 50), 100);

        $officers = Officer::query()
            ->when(strlen($query) >= 2, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('surname', 'like', "%{$query}%")
                        ->orWhere('initials', 'like', "%{$query}%")
                        ->orWhereRaw("CONCAT(initials, ' ', surname) LIKE ?", ["%{$query}%"])
                        ->orWhere('service_number', 'like', "%{$query}%");
                });
            })
            ->with('presentStation:id,name')
            ->select(['id', 'surname', 'initials', 'service_number', 'substantive_rank', 'present_station'])
            ->orderBy('surname')
            ->paginate($perPage);

        $data = $officers->map(function ($o) {
            return [
                'id' => $o->id,
                'surname' => $o->surname,
                'initials' => $o->initials,
                'full_name' => trim("{$o->initials} {$o->surname}"),
                'service_number' => $o->service_number,
                'substantive_rank' => $o->substantive_rank,
                'presentStation' => $o->presentStation ? ['id' => $o->presentStation->id, 'name' => $o->presentStation->name] : null,
            ];
        });

        return $this->successResponse($data);
    }

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

        $memberships = ChatRoomMember::where('officer_id', $officerId)
            ->where('is_active', true)
            ->with(['chatRoom.lastMessage.sender', 'chatRoom.members'])
            ->get();

        $data = $memberships->map(function ($membership) use ($officerId) {
            $room = $membership->chatRoom;
            if (!$room)
                return null;

            // Count messages newer than last_read_at
            $unreadCount = 0;
            if ($membership->last_read_at) {
                $unreadCount = ChatMessage::where('chat_room_id', $room->id)
                    ->where('created_at', '>', $membership->last_read_at)
                    ->where('sender_id', '!=', $officerId)
                    ->count();
            } else {
                // Never read — all messages are unread
                $unreadCount = ChatMessage::where('chat_room_id', $room->id)
                    ->where('sender_id', '!=', $officerId)
                    ->count();
            }

            $memberCount = $room->members->where('is_active', true)->count();
            $lastMsg = $room->lastMessage;

            return [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'room_type' => $room->room_type,
                'command_id' => $room->command_id,
                'is_active' => $room->is_active,
                'member_count' => $memberCount,
                'unread_count' => $unreadCount,
                'last_message' => $lastMsg ? [
                    'id' => $lastMsg->id,
                    'message_text' => $lastMsg->message_text,
                    'attachment_url' => $lastMsg->attachment_url,
                    'created_at' => $lastMsg->created_at?->toIso8601String(),
                    'sender_id' => $lastMsg->sender_id,
                    'sender' => $lastMsg->sender ? [
                        'id' => $lastMsg->sender->id,
                        'name' => $lastMsg->sender->full_name ?? $lastMsg->sender->surname,
                        'rank' => $lastMsg->sender->substantive_rank,
                    ] : null,
                ] : null,
            ];
        })->filter()->values();

        return $this->successResponse($data);
    }

    /**
     * Create chat room (custom group; command_id optional for unit/custom)
     */
    public function createRoom(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'room_type' => 'nullable|string',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:officers,id',
        ]);

        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        // Safely resolve command_id using the officer's present station relationship
        $commandId = null;
        if ($officer->presentStation) {
            $commandId = $officer->presentStation->id ?? null;
        }
        if (!$commandId) {
            $commandId = \App\Models\Command::first()?->id;
        }

        $room = ChatRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'command_id' => $commandId,
            'room_type' => $request->get('room_type', 'group'),
            'is_active' => true,
        ]);

        ChatRoomMember::create([
            'chat_room_id' => $room->id,
            'officer_id' => $officer->id,
            'added_by' => $user->id,
            'is_active' => true,
        ]);

        foreach ($request->member_ids as $memberOfficerId) {
            $memberOfficer = Officer::find($memberOfficerId);
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

        // Auto-mark room as read when messages are listed
        ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officerId)
            ->where('is_active', true)
            ->update(['last_read_at' => now()]);

        $query = ChatMessage::where('chat_room_id', $roomId)
            ->with(['sender', 'reactions', 'parent.sender'])
            ->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 50);
        $messages = $query->paginate($perPage);

        $pinnedMessages = ChatMessage::where('chat_room_id', $roomId)
            ->where('is_pinned', true)
            ->with(['sender', 'parent.sender'])
            ->get();

        $data = $messages->getCollection()->map(function ($msg) use ($officerId) {
            $myReaction = $msg->reactions->where('officer_id', $officerId)->first()?->reaction_type;
            $reactionsGrouped = $msg->reactions->groupBy('reaction_type')->map->count();

            return [
                'id' => $msg->id,
                'chat_room_id' => $msg->chat_room_id,
                'sender_id' => $msg->sender_id,
                'parent_id' => $msg->parent_id,
                'message_text' => $msg->is_deleted ? 'This message was deleted' : $msg->message_text,
                'attachment_url' => $msg->is_deleted ? null : $msg->attachment_url,
                'is_deleted' => $msg->is_deleted,
                'is_broadcast' => $msg->is_broadcast,
                'is_pinned' => $msg->is_pinned,
                'created_at' => $msg->created_at?->toIso8601String(),
                'sender' => $msg->sender ? [
                    'id' => $msg->sender->id,
                    'name' => $msg->sender->full_name ?? $msg->sender->surname,
                    'rank' => $msg->sender->substantive_rank,
                ] : null,
                'parent' => $msg->parent ? [
                    'id' => $msg->parent->id,
                    'message_text' => $msg->parent->is_deleted ? 'This message was deleted' : $msg->parent->message_text,
                    'sender_name' => $msg->parent->sender?->full_name ?? $msg->parent->sender?->surname ?? 'Unknown',
                ] : null,
                'reactions' => $reactionsGrouped,
                'my_reaction' => $myReaction,
            ];
        });

        return $this->successResponse([
            'messages' => $data,
            'pinned_messages' => $pinnedMessages->map(fn($m) => [
                'id' => $m->id,
                'message_text' => $m->message_text,
                'sender_name' => $m->sender?->full_name ?? $m->sender?->surname ?? 'Unknown',
            ]),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ]
        ]);
    }

    /**
     * Delete a message (Sender or Staff Officer only)
     */
    public function deleteMessage(Request $request, $roomId, $messageId): JsonResponse
    {
        $user = $request->user();
        $officerId = $user->officer?->id;

        $message = ChatMessage::where('chat_room_id', $roomId)
            ->where('id', $messageId)
            ->firstOrFail();

        // Check permissions: sender or Staff Officer
        $isStaffOfficer = $user->roles()->where('name', 'Staff Officer')->exists();
        if ($message->sender_id !== $officerId && !$isStaffOfficer) {
            return $this->errorResponse('You do not have permission to delete this message', null, 403);
        }

        $message->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);

        // Broadcast real-time deletion
        broadcast(new ChatMessageDeleted($roomId, $messageId))->toOthers();

        return $this->successResponse(null, 'Message deleted successfully');
    }

    /**
     * Toggle reaction on a message
     */
    public function toggleReaction(Request $request, $roomId, $messageId): JsonResponse
    {
        $request->validate(['reaction' => 'required|string|max:50']);
        $user = $request->user();
        $officerId = $user->officer?->id;

        $message = ChatMessage::where('chat_room_id', $roomId)
            ->where('id', $messageId)
            ->firstOrFail();

        if ($message->is_deleted) {
            return $this->errorResponse('Cannot react to a deleted message', null, 400);
        }

        $reaction = ChatMessageReaction::where('chat_message_id', $messageId)
            ->where('officer_id', $officerId)
            ->first();

        if ($reaction && $reaction->reaction_type === $request->reaction) {
            $reaction->delete();
            return $this->successResponse(null, 'Reaction removed');
        }

        ChatMessageReaction::updateOrCreate(
            ['chat_message_id' => $messageId, 'officer_id' => $officerId],
            ['reaction_type' => $request->reaction]
        );

        // Broadcast real-time reaction update
        $updatedReactions = $message->reactions()->get()->groupBy('reaction_type')->map->count();
        broadcast(new ChatMessageReacted($roomId, $messageId, $updatedReactions))->toOthers();

        return $this->successResponse(null, 'Reaction updated');
    }

    /**
     * Toggle pinned status of a message (Staff Officer or Group Admin only)
     */
    public function togglePin(Request $request, $roomId, $messageId): JsonResponse
    {
        $user = $request->user();
        $officerId = $user->officer?->id;

        $message = ChatMessage::where('chat_room_id', $roomId)
            ->where('id', $messageId)
            ->firstOrFail();

        $room = ChatRoom::findOrFail($roomId);
        $isStaffOfficer = $user->roles()->where('name', 'Staff Officer')->exists();

        // Members who added this room (group creators) are admins
        $isAdmin = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officerId)
            ->where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('added_by') // System rooms
                    ->orWhere('added_by', $user->id); // Creator
            })
            ->exists();

        if (!$isStaffOfficer && !$isAdmin) {
            return $this->errorResponse('You do not have permission to pin messages in this room', null, 403);
        }

        $message->update([
            'is_pinned' => !$message->is_pinned,
            'pinned_at' => !$message->is_pinned ? now() : null,
        ]);

        // Broadcast real-time pin update
        broadcast(new ChatMessagePinned($message))->toOthers();

        return $this->successResponse(
            ['is_pinned' => $message->is_pinned],
            $message->is_pinned ? 'Message pinned' : 'Message unpinned'
        );
    }

    /**
     * Toggle broadcast status (Staff Officer only)
     */
    public function toggleBroadcast(Request $request, $roomId, $messageId): JsonResponse
    {
        $user = $request->user();
        $isStaffOfficer = $user->roles()->where('name', 'Staff Officer')->exists();

        if (!$isStaffOfficer) {
            return $this->errorResponse('Only Staff Officers can broadcast messages', null, 403);
        }

        $message = ChatMessage::where('chat_room_id', $roomId)
            ->where('id', $messageId)
            ->firstOrFail();

        $message->update([
            'is_broadcast' => !$message->is_broadcast,
        ]);

        // Broadcast real-time broadcast update
        broadcast(new ChatMessageBroadcasted($message))->toOthers();

        return $this->successResponse(
            ['is_broadcast' => $message->is_broadcast],
            $message->is_broadcast ? 'Message marked as broadcast' : 'Broadcast status removed'
        );
    }

    /**
     * Get read receipts for a message
     */
    public function getMessageInfo(Request $request, $roomId, $messageId): JsonResponse
    {
        $user = $request->user();
        $officerId = $user->officer?->id;

        $message = ChatMessage::where('chat_room_id', $roomId)
            ->where('id', $messageId)
            ->firstOrFail();

        // Must be a member to see info
        $isMember = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officerId)
            ->where('is_active', true)
            ->exists();

        if (!$isMember) {
            return $this->errorResponse('You are not a member of this room', null, 403);
        }

        // A user has "read" the message if their last_read_at >= message created_at
        $readers = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('is_active', true)
            ->where('last_read_at', '>=', $message->created_at)
            ->with('officer')
            ->get();

        $data = $readers->map(function ($reader) {
            return [
                'officer_id' => $reader->officer_id,
                'name' => $reader->officer->full_name ?? $reader->officer->surname,
                'rank' => $reader->officer->substantive_rank,
                'read_at' => $reader->last_read_at?->toIso8601String(),
            ];
        })->sortByDesc('read_at')->values();

        return $this->successResponse([
            'message_id' => $message->id,
            'read_by' => $data,
            'total_readers' => $data->count(),
        ]);
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
            'parent_id' => 'nullable|exists:chat_messages,id',
        ]);

        $chatMessage = ChatMessage::create([
            'chat_room_id' => $roomId,
            'sender_id' => $officer->id,
            'parent_id' => $request->parent_id,
            'message_text' => $request->message,
        ]);

        // Broadcast real-time message
        broadcast(new ChatMessageSent($chatMessage))->toOthers();

        return $this->successResponse([
            'id' => $chatMessage->id,
            'message' => $chatMessage->message_text,
            'message_text' => $chatMessage->message_text,
            'sender_id' => $officer->id,
            'created_at' => $chatMessage->created_at->toIso8601String(),
            'is_read' => false,
            'sender' => [
                'id' => $officer->id,
                'name' => $officer->full_name,
                'rank' => $officer->substantive_rank,
            ],
        ], 'Message sent successfully', 201);
    }

    /**
     * Send message with file attachment
     */
    public function sendAttachment(Request $request, $roomId): JsonResponse
    {
        $request->validate([
            'attachment' => 'required|file|max:20480', // 20MB
            'message_text' => 'nullable|string|max:5000',
            'parent_id' => 'nullable|exists:chat_messages,id',
        ]);

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
            return $this->errorResponse('You are not a member of this room', null, 403, 'PERMISSION_DENIED');
        }

        $path = $request->file('attachment')->store('chat-attachments', 'public');
        $url = \Illuminate\Support\Facades\Storage::url($path);

        $chatMessage = ChatMessage::create([
            'chat_room_id' => $roomId,
            'sender_id' => $officer->id,
            'parent_id' => $request->parent_id,
            'message_text' => $request->message_text ?? '📎 Attachment',
            'attachment_url' => $url,
        ]);

        // Broadcast real-time attachment
        broadcast(new ChatMessageSent($chatMessage))->toOthers();

        return $this->successResponse([
            'id' => $chatMessage->id,
            'message' => $chatMessage->message_text,
            'message_text' => $chatMessage->message_text,
            'attachment_url' => $chatMessage->attachment_url,
            'sender_id' => $officer->id,
            'created_at' => $chatMessage->created_at->toIso8601String(),
            'sender' => [
                'id' => $officer->id,
                'name' => $officer->full_name,
                'rank' => $officer->substantive_rank,
            ],
        ], 'Attachment sent successfully', 201);
    }

    /**
     * Mark all messages in a room as read by the current officer
     */
    public function markRead(Request $request, $roomId): JsonResponse
    {
        $user = $request->user();
        $officerId = $user->officer?->id;
        if (!$officerId) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        // Mark all unread messages in this room as read (simple approach: update last_read_at on membership)
        ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $officerId)
            ->where('is_active', true)
            ->update(['last_read_at' => now()]);

        return $this->successResponse(null, 'Messages marked as read');
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
            ->filter(fn($m) => !is_null($m->officer))
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'officer_id' => $m->officer_id,
                    'user_id' => $m->officer?->user_id,
                    'name' => $m->officer?->full_name ?? 'Unknown Officer',
                    'initials' => $m->officer?->initials,
                    'surname' => $m->officer?->surname,
                    'service_number' => $m->officer?->service_number,
                    'rank' => $m->officer?->substantive_rank ?? 'Officer',
                ];
            })->values();

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
            ChatRoomMember::updateOrCreate(
                ['chat_room_id' => $roomId, 'officer_id' => $oid],
                [
                    'added_by' => $user->id,
                    'is_active' => true,
                    'joined_at' => now(),
                    'left_at' => null
                ]
            );
            $added++;
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

        // Find target officer strictly by officer ID (as sent by mobile)
        $targetOfficer = Officer::find($userId);

        if (!$targetOfficer) {
            return $this->errorResponse("Officer with ID {$userId} not found in the system", null, 404);
        }

        $membership = ChatRoomMember::where('chat_room_id', $roomId)
            ->where('officer_id', $targetOfficer->id)
            ->where('is_active', true)
            ->first();

        if (!$membership) {
            return $this->errorResponse("Officer {$targetOfficer->full_name} is not an active member of this room (ID: {$roomId})", null, 404);
        }

        $isStaffOfficer = $user->hasRole('Staff Officer') || $user->roles->pluck('name')->contains('Staff Officer');
        $sameCommand = $room->command_id && $officer->present_station == $room->command_id;
        $isSelf = $targetOfficer->id === $officer->id;

        // 1. Can always leave yourself
        if ($isSelf) {
            $membership->update(['is_active' => false, 'left_at' => now()]);
            return $this->successResponse(null, 'You have left the room');
        }

        // 2. Staff Officers can remove anyone from their Command room or ANY Group/Unit room
        if ($isStaffOfficer && ($sameCommand || in_array($room->room_type, ['group', 'UNIT', 'management']) || !$room->command_id)) {
            $membership->update(['is_active' => false, 'left_at' => now()]);
            return $this->successResponse(null, 'Member removed');
        }

        // 3. Creator/Adder can remove (fallback)
        if ($membership->added_by === $user->id) {
            $membership->update(['is_active' => false, 'left_at' => now()]);
            return $this->successResponse(null, 'Member removed');
        }

        $reason = $isStaffOfficer ? "Station mismatch (your station: {$officer->present_station}, room station: {$room->command_id})" : "You are not a Staff Officer nor the group creator";
        return $this->errorResponse("Permission denied: {$reason}", null, 403, 'PERMISSION_DENIED');
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

    /**
     * Sync auto-join rooms based on Command, Unit, and Rank
     */
    public function sync(Request $request, \App\Services\ChatService $chatService): JsonResponse
    {
        $user = $request->user();
        $officer = $user->officer;
        if (!$officer) {
            return $this->errorResponse('Officer record not found', null, 404);
        }

        $chatService->syncOfficerRooms($officer);

        return $this->successResponse(null, 'Rooms synchronized successfully');
    }
}
