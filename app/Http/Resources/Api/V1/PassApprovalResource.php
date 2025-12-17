<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PassApprovalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'approval_status' => $this->approval_status,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'dc_admin' => [
                'id' => $this->whenLoaded('dcAdmin')?->id,
                'email' => $this->whenLoaded('dcAdmin')?->email,
            ],
            'area_controller' => new OfficerResource($this->whenLoaded('areaController')),
        ];
    }
}

