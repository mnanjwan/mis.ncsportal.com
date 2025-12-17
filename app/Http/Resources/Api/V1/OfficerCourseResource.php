<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficerCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'officer' => new OfficerResource($this->whenLoaded('officer')),
            'course_name' => $this->course_name,
            'course_type' => $this->course_type,
            'institution' => $this->institution,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'certificate_url' => $this->certificate_url,
            'status' => $this->status,
        ];
    }
}

