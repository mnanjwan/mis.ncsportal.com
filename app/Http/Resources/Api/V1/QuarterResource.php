<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuarterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'command' => new CommandResource($this->whenLoaded('command')),
            'quarter_number' => $this->quarter_number,
            'quarter_type' => $this->quarter_type,
            'address' => $this->address,
            'is_occupied' => $this->is_occupied,
            'is_active' => $this->is_active,
        ];
    }
}

