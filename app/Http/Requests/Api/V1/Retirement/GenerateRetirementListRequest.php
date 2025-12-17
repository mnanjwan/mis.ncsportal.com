<?php

namespace App\Http\Requests\Api\V1\Retirement;

use Illuminate\Foundation\Http\FormRequest;

class GenerateRetirementListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => 'required|integer|min:2020|max:2100|unique:retirement_lists,year',
        ];
    }
}

