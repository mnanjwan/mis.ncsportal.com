<?php

namespace App\Http\Requests\Api\V1\Roster;

use Illuminate\Foundation\Http\FormRequest;

class CreateRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'command_id' => 'required|exists:commands,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'assignments' => 'required|array|min:1',
            'assignments.*.officer_id' => 'required|exists:officers,id',
            'assignments.*.duty_date' => 'required|date',
            'assignments.*.duty_type' => 'required|string|max:100',
        ];
    }
}

