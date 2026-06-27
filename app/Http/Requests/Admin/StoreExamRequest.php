<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'student_class_id' => ['required', 'exists:student_classes,id'],
            'class_category_id' => ['required', 'exists:class_categories,id'],
            'class_hall_id' => ['required', 'exists:class_halls,id'],
            'exam_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['sometimes', 'in:scheduled,ongoing,completed,cancelled'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Exam title is required.',
            'student_class_id.required' => 'Please select a class.',
            'class_category_id.required' => 'Please select a category.',
            'class_hall_id.required' => 'Please select a hall.',
            'exam_date.required' => 'Exam date is required.',
            'exam_date.after_or_equal' => 'Exam date cannot be in the past.',
            'start_time.required' => 'Start time is required.',
            'end_time.required' => 'End time is required.',
            'end_time.after' => 'End time must be after start time.',
        ];
    }
}