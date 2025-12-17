<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManningRequestItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rank' => $this->rank,
            'quantity_needed' => $this->quantity_needed,
            'sex_requirement' => $this->sex_requirement,
            'qualification_requirement' => $this->qualification_requirement,
        ];
    }
}

