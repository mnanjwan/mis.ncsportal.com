<?php

namespace App\Http\Requests\Api\V1\Manning;

use Illuminate\Foundation\Http\FormRequest;

class CreateManningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'command_id' => 'required|exists:commands,id',
            'items' => 'required|array|min:1',
            'items.*.rank' => 'required|string|max:100',
            'items.*.quantity_needed' => 'required|integer|min:1',
            'items.*.sex_requirement' => 'nullable|in:M,F,ANY',
            'items.*.qualification_requirement' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }
}

