<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'approval_status' => $this->approval_status,
            'minuted_at' => $this->minuted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'printed_at' => $this->printed_at?->toIso8601String(),
            'staff_officer' => [
                'id' => $this->whenLoaded('staffOfficer')?->id,
                'email' => $this->whenLoaded('staffOfficer')?->email,
            ],
            'dc_admin' => [
                'id' => $this->whenLoaded('dcAdmin')?->id,
                'email' => $this->whenLoaded('dcAdmin')?->email,
            ],
            'area_controller' => new OfficerResource($this->whenLoaded('areaController')),
        ];
    }
}

