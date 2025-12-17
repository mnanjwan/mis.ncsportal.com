<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RosterAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'duty_date' => $this->duty_date?->format('Y-m-d'),
            'duty_type' => $this->duty_type,
        ];
    }
}

