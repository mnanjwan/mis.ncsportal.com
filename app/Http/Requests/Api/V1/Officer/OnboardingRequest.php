<?php

namespace App\Http\Requests\Api\V1\Officer;

use App\Models\Bank;
use App\Models\Pfa;
use App\Rules\ServiceNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_number' => ['required', 'string', 'unique:officers,service_number', new ServiceNumber()],
            'initials' => 'required|string|max:50',
            'surname' => 'required|string|max:255',
            'sex' => 'required|in:M,F',
            'date_of_first_appointment' => 'required|date',
            'date_of_present_appointment' => 'required|date',
            'substantive_rank' => 'required|string|max:100',
            'salary_grade_level' => 'required|string|max:10',
            'date_of_birth' => 'required|date|before:today',
            'state_of_origin' => 'required|string|max:100',
            'lga' => 'required|string|max:100',
            'geopolitical_zone' => 'required|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'entry_qualification' => 'required|string|max:255',
            'discipline' => 'nullable|string|max:255',
            'additional_qualification' => 'nullable|string|max:255',
            'present_station' => 'required|exists:commands,id',
            'date_posted_to_station' => 'required|date',
            'residential_address' => 'nullable|string',
            'permanent_home_address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|unique:officers,email|unique:users,email',
            'bank_name' => [
                'required',
                'string',
                'max:255',
                Rule::exists('banks', 'name')->where('is_active', true),
            ],
            'bank_account_number' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $bankName = $this->input('bank_name');
                    $bank = Bank::query()
                        ->where('name', $bankName)
                        ->where('is_active', true)
                        ->first();

                    if (!$bank) {
                        return;
                    }

                    $digits = max(1, (int) $bank->account_number_digits);
                    if (!preg_match('/^\d{' . $digits . '}$/', (string) $value)) {
                        $fail("Bank Account Number must be exactly {$digits} digits.");
                    }
                },
            ],
            'sort_code' => 'nullable|string|max:20',
            'pfa_name' => [
                'required',
                'string',
                'max:255',
                Rule::exists('pfas', 'name')->where('is_active', true),
            ],
            'rsa_number' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $pfaName = $this->input('pfa_name');
                    $pfa = Pfa::query()
                        ->where('name', $pfaName)
                        ->where('is_active', true)
                        ->first();

                    if (!$pfa) {
                        return;
                    }

                    $prefix = strtoupper((string) $pfa->rsa_prefix);
                    $digits = max(1, (int) $pfa->rsa_digits);
                    $pattern = '/^' . preg_quote($prefix, '/') . '\d{' . $digits . '}$/';

                    if (!preg_match($pattern, (string) $value)) {
                        $example = $prefix . str_repeat('0', $digits);
                        $fail("RSA number must be {$prefix} followed by {$digits} digits (e.g., {$example}).");
                    }
                },
            ],
            'unit' => 'nullable|string|max:255',
            'next_of_kin' => 'required|array|min:1',
            'next_of_kin.*.name' => 'required|string|max:255',
            'next_of_kin.*.relationship' => 'required|string|max:100',
            'next_of_kin.*.phone_number' => 'nullable|string|max:20',
            'next_of_kin.*.address' => 'nullable|string',
            'interdicted' => 'boolean',
            'suspended' => 'boolean',
            'quartered' => 'boolean',
            'documents' => 'nullable|array',
            'documents.*.type' => 'required_with:documents|string',
            'documents.*.file' => 'required_with:documents|file|mimes:jpeg,jpg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.exists' => 'Selected bank is not configured.',
            'pfa_name.exists' => 'Selected PFA is not configured.',
            'email.unique' => 'This email is already registered',
            'service_number.unique' => 'This service number is already in use',
            'service_number.regex' => 'Service number must start with NCS (e.g., NCS50001)',
        ];
    }
}

