<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PfaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pfaId = $this->route('pfa')?->id ?? $this->route('pfa');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pfas', 'name')->ignore($pfaId),
            ],
            'rsa_prefix' => 'required|string|max:20|regex:/^[A-Za-z]+$/',
            'rsa_digits' => 'required|integer|min:1|max:50',
            'is_active' => 'nullable|boolean',
        ];
    }
}
