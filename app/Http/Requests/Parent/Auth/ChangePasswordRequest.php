<?php

namespace App\Http\Requests\Parent\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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

            'current_password' => [
                'required',
                'string',
            ],

            'new_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}