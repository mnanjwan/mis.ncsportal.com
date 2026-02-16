<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_number' => $this->service_number,
            'initials' => $this->initials,
            'surname' => $this->surname,
            'full_name' => $this->full_name,
            'sex' => $this->sex,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'date_of_first_appointment' => $this->date_of_first_appointment?->format('Y-m-d'),
            'date_of_present_appointment' => $this->date_of_present_appointment?->format('Y-m-d'),
            'substantive_rank' => $this->substantive_rank,
            'display_rank' => $this->display_rank,
            'salary_grade_level' => $this->salary_grade_level,
            'state_of_origin' => $this->state_of_origin,
            'lga' => $this->lga,
            'geopolitical_zone' => $this->geopolitical_zone,
            'marital_status' => $this->marital_status,
            'entry_qualification' => $this->entry_qualification,
            'discipline' => $this->discipline,
            'additional_qualification' => $this->additional_qualification,
            'present_station' => new CommandResource($this->whenLoaded('presentStation')),
            'date_posted_to_station' => $this->date_posted_to_station?->format('Y-m-d'),
            'residential_address' => $this->residential_address,
            'permanent_home_address' => $this->permanent_home_address,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'sort_code' => $this->sort_code,
            'pfa_name' => $this->pfa_name,
            'rsa_number' => $this->rsa_number,
            'unit' => $this->unit,
            'interdicted' => $this->interdicted,
            'suspended' => $this->suspended,
            'dismissed' => $this->dismissed,
            'quartered' => $this->quartered,
            'is_deceased' => $this->is_deceased,
            'is_active' => $this->is_active,
            'profile_picture_url' => $this->profile_picture_url,
            'next_of_kin' => NextOfKinResource::collection($this->whenLoaded('nextOfKin')),
            'documents' => OfficerDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

