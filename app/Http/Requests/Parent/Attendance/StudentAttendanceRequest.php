<?php

namespace App\Http\Requests\Parent\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StudentAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],
        ];
    }

    /**
     * Custom Validation Messages
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Student ID is required.',
            'student_id.integer'  => 'Student ID must be an integer.',
            'student_id.exists'   => 'Selected student does not exist.',
        ];
    }
}