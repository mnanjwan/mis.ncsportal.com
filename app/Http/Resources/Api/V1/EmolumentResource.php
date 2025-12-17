<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmolumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'year' => $this->year,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'pfa_name' => $this->pfa_name,
            'rsa_pin' => $this->rsa_pin,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'assessed_at' => $this->assessed_at?->toIso8601String(),
            'validated_at' => $this->validated_at?->toIso8601String(),
            'processed_at' => $this->processed_at?->toIso8601String(),
            'assessment' => new EmolumentAssessmentResource($this->whenLoaded('assessment')),
            'validation' => new EmolumentValidationResource($this->whenLoaded('validation')),
        ];
    }
}

