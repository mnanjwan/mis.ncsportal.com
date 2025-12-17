<?php

namespace App\Http\Requests\Api\V1\Promotion;

use Illuminate\Foundation\Http\FormRequest;

class ApprovePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_rank' => 'required|string|max:100',
            'effective_date' => 'required|date|after_or_equal:today',
        ];
    }
}

