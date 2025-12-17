<?php

namespace App\Http\Requests\Api\V1\Emolument;

use App\Rules\RsaPin;
use Illuminate\Foundation\Http\FormRequest;

class RaiseEmolumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:50',
            'pfa_name' => 'required|string|max:255',
            'rsa_pin' => ['required', 'string', new RsaPin()],
            'next_of_kin' => 'required|array|min:1',
            'next_of_kin.*.name' => 'required|string|max:255',
            'next_of_kin.*.relationship' => 'required|string|max:100',
            'next_of_kin.*.phone_number' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'rsa_pin.regex' => 'RSA PIN must be 12 digits with PEN prefix (e.g., PEN123456789012)',
            'next_of_kin.required' => 'At least one next of kin is required',
        ];
    }
}

