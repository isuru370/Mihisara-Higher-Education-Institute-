<?php

namespace App\Http\Requests\Notification;

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
            'student_id' => 'nullable|exists:students,id',
            'status' => ['nullable', 'string', Rule::in(NotificationStatus::all())],
            'type' => ['nullable', 'string', Rule::in(NotificationType::all())],
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after:date_from',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.exists' => 'Selected student does not exist.',
            'status.in' => 'Invalid status value.',
            'type.in' => 'Invalid type value.',
            'date_to.after' => 'End date must be after start date.',
            'per_page.max' => 'Maximum 100 items per page.',
        ];
    }
}