<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'current_password'
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' =>
                'Current password is incorrect.',

            'password.confirmed' =>
                'Password confirmation does not match.',

            'password.min' =>
                'Password must be at least 8 characters.',
        ];
    }
}
