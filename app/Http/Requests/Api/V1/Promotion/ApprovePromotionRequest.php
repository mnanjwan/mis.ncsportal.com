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
            'to_rank' => 'required|string|max:100',
            'promotion_date' => 'required|date',
            'board_meeting_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}

