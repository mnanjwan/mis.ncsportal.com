<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeceasedOfficerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'date_of_death' => $this->date_of_death?->format('Y-m-d'),
            'cause_of_death' => $this->cause_of_death,
            'place_of_death' => $this->place_of_death,
            'death_certificate_url' => $this->death_certificate_url,
            'recorded_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

