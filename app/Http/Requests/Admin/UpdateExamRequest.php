<?php

namespace App\Http\Requests\Admin;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255'
            ],

            'student_class_id' => [
                'required',
                'exists:student_classes,id'
            ],

            'class_category_id' => [
                'required',
                'exists:class_categories,id'
            ],

            'class_hall_id' => [
                'required',
                'exists:class_halls,id'
            ],

            'exam_date' => [
                'required',
                'date'
            ],

            'start_time' => [
                'required',
                'date_format:H:i'
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time'
            ],

            'status' => [
                'required',
                'in:scheduled,ongoing,completed,cancelled'
            ],

            'note' => [
                'nullable',
                'string',
                'max:500'
            ],
        ];
    }

    /**
     * Prevent editing completed exams.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $examId = $this->route('exam');

            $exam = Exam::find($examId);

            if (
                $exam &&
                $exam->status === 'completed'
            ) {

                $validator->errors()->add(
                    'title',
                    'Completed exams cannot be modified.'
                );
            }
        });
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Exam title is required.',

            'student_class_id.required' =>
            'Please select a class.',

            'class_category_id.required' =>
            'Please select a category.',

            'class_hall_id.required' =>
            'Please select a hall.',

            'exam_date.required' =>
            'Exam date is required.',

            'start_time.required' =>
            'Start time is required.',

            'end_time.required' =>
            'End time is required.',

            'end_time.after' =>
            'End time must be after start time.',

            'status.required' =>
            'Status is required.',
        ];
    }
}
