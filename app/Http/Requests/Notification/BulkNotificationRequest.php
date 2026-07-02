<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_ids' => 'required|array|min:1|max:100',
            'student_ids.*' => 'exists:students,id',
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:1000',
            'type' => ['nullable', 'string', Rule::in(NotificationType::all())],
            'data' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => 'Please select at least one student.',
            'student_ids.min' => 'Please select at least one student.',
            'student_ids.max' => 'Cannot send to more than 100 students at once.',
            'student_ids.*.exists' => 'One or more selected students do not exist.',
            'title.required' => 'Notification title is required.',
            'title.max' => 'Title cannot exceed 150 characters.',
            'message.required' => 'Notification message is required.',
            'message.max' => 'Message cannot exceed 1000 characters.',
            'type.in' => 'Invalid notification type.',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ];
    }
}