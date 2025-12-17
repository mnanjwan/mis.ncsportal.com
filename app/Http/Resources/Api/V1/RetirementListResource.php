<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RetirementListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'year' => $this->year,
            'status' => $this->status,
            'generated_at' => $this->generated_at?->toIso8601String(),
            'items' => RetirementListItemResource::collection($this->whenLoaded('items')),
        ];
    }
}

