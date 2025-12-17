<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'max_duration_days' => $this->max_duration_days,
            'max_duration_months' => $this->max_duration_months,
            'max_occurrences_per_year' => $this->max_occurrences_per_year,
            'requires_medical_certificate' => $this->requires_medical_certificate,
            'requires_approval_level' => $this->requires_approval_level,
            'description' => $this->description,
        ];
    }
}

