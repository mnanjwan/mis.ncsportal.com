<?php

namespace App\Http\Requests\Api\V1\Retirement;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRetirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_retirement_date' => 'required|date|before_or_equal:today',
        ];
    }
}

