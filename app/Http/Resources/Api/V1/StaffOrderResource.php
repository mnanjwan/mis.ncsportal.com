<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'from_command' => new CommandResource($this->whenLoaded('fromCommand')),
            'to_command' => new CommandResource($this->whenLoaded('toCommand')),
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'order_type' => $this->order_type,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

