<?php

namespace App\Http\Requests\Api\V1\Leave;

use Illuminate\Foundation\Http\FormRequest;

class ApproveLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject',
            'comments' => 'required_if:action,reject|string',
        ];
    }
}

