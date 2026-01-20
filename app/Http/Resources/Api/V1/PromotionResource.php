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
            'eligibility_list_item_id' => $this->eligibility_list_item_id,
            'eligibility_list' => $this->whenLoaded('eligibilityListItem', function () {
                return [
                    'id' => $this->eligibilityListItem?->eligibilityList?->id,
                    'year' => $this->eligibilityListItem?->eligibilityList?->year,
                ];
            }),
            'from_rank' => $this->from_rank,
            'to_rank' => $this->to_rank,
            'promotion_date' => $this->promotion_date?->format('Y-m-d'),
            'approved_by_board' => (bool) $this->approved_by_board,
            'board_meeting_date' => $this->board_meeting_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

