<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'eligibility_list' => [
                'id' => $this->whenLoaded('eligibilityList')?->id,
                'year' => $this->whenLoaded('eligibilityList')?->year,
                'rank' => $this->whenLoaded('eligibilityList')?->rank,
            ],
            'current_rank' => $this->current_rank,
            'new_rank' => $this->new_rank,
            'status' => $this->status,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];
    }
}

