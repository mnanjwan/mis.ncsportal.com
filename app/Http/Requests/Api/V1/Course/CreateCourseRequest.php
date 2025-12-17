<?php

namespace App\Http\Requests\Api\V1\Course;

use Illuminate\Foundation\Http\FormRequest;

class CreateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'officer_id' => 'required|exists:officers,id',
            'course_name' => 'required|string|max:255',
            'course_type' => 'required|string|max:100',
            'institution' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'certificate_url' => 'nullable|string|max:500',
        ];
    }
}

