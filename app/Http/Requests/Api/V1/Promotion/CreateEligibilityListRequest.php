<?php

namespace App\Http\Requests\Api\V1\Promotion;

use Illuminate\Foundation\Http\FormRequest;

class CreateEligibilityListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => 'required|integer|min:2020|max:2100|unique:promotion_eligibility_lists,year',
            'rank' => 'required|string|max:100',
            'criteria' => 'required|array|min:1',
            'criteria.*.criterion_type' => 'required|string|max:100',
            'criteria.*.value' => 'required|string|max:255',
        ];
    }
}

