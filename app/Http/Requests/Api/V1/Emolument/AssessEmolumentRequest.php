<?php

namespace App\Http\Requests\Api\V1\Emolument;

use Illuminate\Foundation\Http\FormRequest;

class AssessEmolumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assessment_status' => 'required|in:APPROVED,REJECTED',
            'comments' => 'nullable|string',
        ];
    }
}

