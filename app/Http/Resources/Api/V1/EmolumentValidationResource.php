<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmolumentValidationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'validation_status' => $this->validation_status,
            'comments' => $this->comments,
            'validated_at' => $this->validated_at?->toIso8601String(),
            'validator' => [
                'id' => $this->whenLoaded('validator')?->id,
                'email' => $this->whenLoaded('validator')?->email,
            ],
        ];
    }
}

