<?php

namespace App\Http\Requests\Api\V1\Quarter;

use Illuminate\Foundation\Http\FormRequest;

class AllocateQuarterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'officer_id' => 'required|exists:officers,id',
            'quarter_id' => 'required|exists:quarters,id',
            'allocation_date' => 'required|date|after_or_equal:today',
        ];
    }
}

