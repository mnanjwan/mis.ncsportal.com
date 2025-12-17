<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'sender' => [
                'id' => $this->whenLoaded('sender')?->id,
                'email' => $this->whenLoaded('sender')?->email,
                'officer' => $this->whenLoaded('sender.officer') ? [
                    'service_number' => $this->sender->officer->service_number,
                    'name' => $this->sender->officer->full_name,
                ] : null,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

