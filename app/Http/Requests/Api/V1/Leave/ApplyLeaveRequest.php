<?php

namespace App\Http\Requests\Api\V1\Leave;

use Illuminate\Foundation\Http\FormRequest;

class ApplyLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'nullable|string',
            'expected_date_of_delivery' => 'required_if:leave_type_id,6|date', // Maternity leave
            'medical_certificate' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'expected_date_of_delivery.required_if' => 'Expected Date of Delivery (EDD) is required for maternity leave',
        ];
    }
}

