<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RetirementListItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'expected_retirement_date' => $this->expected_retirement_date?->format('Y-m-d'),
            'actual_retirement_date' => $this->actual_retirement_date?->format('Y-m-d'),
            'status' => $this->status,
            'processed_at' => $this->processed_at?->toIso8601String(),
        ];
    }
}

