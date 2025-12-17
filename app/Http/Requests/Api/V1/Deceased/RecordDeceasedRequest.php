<?php

namespace App\Http\Requests\Api\V1\Deceased;

use Illuminate\Foundation\Http\FormRequest;

class RecordDeceasedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'officer_id' => 'required|exists:officers,id',
            'date_of_death' => 'required|date|before_or_equal:today',
            'cause_of_death' => 'nullable|string|max:500',
            'place_of_death' => 'nullable|string|max:255',
            'death_certificate_url' => 'nullable|string|max:500',
        ];
    }
}

