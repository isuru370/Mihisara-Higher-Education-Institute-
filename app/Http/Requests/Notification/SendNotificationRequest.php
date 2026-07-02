<?php

namespace App\Http\Requests\Notification;

use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
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
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'Selected student does not exist.',
            'title.required' => 'Notification title is required.',
            'title.max' => 'Title cannot exceed 150 characters.',
            'message.required' => 'Notification message is required.',
            'message.max' => 'Message cannot exceed 1000 characters.',
            'type.in' => 'Invalid notification type.',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => 'student',
            'title' => 'title',
            'message' => 'message',
            'type' => 'type',
            'scheduled_at' => 'scheduled time',
        ];
    }
}