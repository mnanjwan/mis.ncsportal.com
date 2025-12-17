<?php

namespace App\Http\Requests\Api\V1\Quarter;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuarterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'command_id' => 'required|exists:commands,id',
            'quarter_number' => 'required|string|max:50',
            'quarter_type' => 'required|string|max:100',
            'address' => 'required|string|max:500',
        ];
    }
}

