<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmolumentAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assessment_status' => $this->assessment_status,
            'comments' => $this->comments,
            'assessed_at' => $this->assessed_at?->toIso8601String(),
            'assessor' => [
                'id' => $this->whenLoaded('assessor')?->id,
                'email' => $this->whenLoaded('assessor')?->email,
            ],
        ];
    }
}

