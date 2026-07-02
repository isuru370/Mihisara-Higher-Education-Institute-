<?php

namespace App\Http\Requests\Parent\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class GetTeachersRequest extends FormRequest
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

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student id is required.',
            'student_id.exists'   => 'Selected student does not exist.',
        ];
    }
}