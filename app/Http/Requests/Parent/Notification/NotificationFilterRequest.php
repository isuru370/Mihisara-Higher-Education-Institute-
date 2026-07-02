<?php

namespace App\Http\Requests\Parent\Notification;

use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NotificationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'status' => ['nullable', 'string', Rule::in(NotificationStatus::all())],
            'type' => ['nullable', 'string', Rule::in(NotificationType::all())],
            'date_from' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Student ID is required',
            'student_id.exists' => 'Invalid student ID',
            'status.in' => 'Invalid status value',
            'type.in' => 'Invalid type value',
            'date_from.date' => 'Invalid from date format',
            'date_to.date' => 'Invalid to date format',
            'date_to.after_or_equal' => 'To date must be after or equal to from date',
            'per_page.max' => 'Maximum 100 items per page',
        ];
    }
}