<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'number_of_days' => $this->number_of_days,
            'reason' => $this->reason,
            'expected_date_of_delivery' => $this->expected_date_of_delivery?->format('Y-m-d'),
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'minuted_at' => $this->minuted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'approval' => new LeaveApprovalResource($this->whenLoaded('approval')),
        ];
    }
}

