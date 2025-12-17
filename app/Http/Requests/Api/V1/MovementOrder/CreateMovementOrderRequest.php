<?php

namespace App\Http\Requests\Api\V1\MovementOrder;

use Illuminate\Foundation\Http\FormRequest;

class CreateMovementOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'officer_id' => 'required|exists:officers,id',
            'to_command_id' => 'required|exists:commands,id',
            'effective_date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:500',
        ];
    }
}

