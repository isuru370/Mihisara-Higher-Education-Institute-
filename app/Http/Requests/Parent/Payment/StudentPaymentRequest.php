<?php

namespace App\Http\Requests\Parent\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StudentPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
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
