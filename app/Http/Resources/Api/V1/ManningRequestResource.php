<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManningRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'command' => new CommandResource($this->whenLoaded('command')),
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'forwarded_to_hrd_at' => $this->forwarded_to_hrd_at?->toIso8601String(),
            'notes' => $this->notes,
            'items' => ManningRequestItemResource::collection($this->whenLoaded('items')),
            'requested_by' => [
                'id' => $this->whenLoaded('requestedBy')?->id,
                'email' => $this->whenLoaded('requestedBy')?->email,
            ],
        ];
    }
}

