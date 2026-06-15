<?php

namespace App\Http\Requests\StudentClassEnrollment;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentClassEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'student_class_id' => ['required', 'exists:student_classes,id'],
            'class_category_fee_id' => ['required', 'exists:class_category_fees,id'],

            'is_free_card' => ['nullable', 'boolean'],
            'custom_fee' => ['nullable', 'numeric', 'min:0'],
            'custom_fee_reason' => ['nullable', 'string', 'max:150'],

            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_reason' => ['nullable', 'string', 'max:150'],

            'enrolled_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }
}