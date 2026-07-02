<?php

namespace App\Http\Requests\Parent\Result;

use Illuminate\Foundation\Http\FormRequest;

class ResultRequest extends FormRequest
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

            'exam_id' => [
                'required',
                'integer',
                'exists:exams,id',
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

            'exam_id.required'    => 'Exam ID is required.',
            'exam_id.integer'     => 'Exam ID must be an integer.',
            'exam_id.exists'      => 'Selected exam does not exist.',
        ];
    }
}