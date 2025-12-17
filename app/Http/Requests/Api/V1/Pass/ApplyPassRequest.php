<?php

namespace App\Http\Requests\Api\V1\Pass;

use Illuminate\Foundation\Http\FormRequest;

class ApplyPassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after' => 'End date must be after start date',
            'start_date.after_or_equal' => 'Start date cannot be in the past',
        ];
    }
}

