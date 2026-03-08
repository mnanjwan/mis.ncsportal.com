<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    if (!$user->officer) {
        return false;
    }

    $isMember = \App\Models\ChatRoomMember::where('chat_room_id', $roomId)
        ->where('officer_id', $user->officer->id)
        ->where('is_active', true)
        ->exists();

    if ($isMember) {
        return [
            'id' => $user->officer->id,
            'name' => $user->officer->name,
            'avatar' => $user->officer->getProfilePictureUrlFull(),
        ];
    }

    return false;
});
