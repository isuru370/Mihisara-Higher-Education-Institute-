<?php

namespace App\Http\Requests\Parent\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LogoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer'],
            'device_id' => ['required', 'string'],
        ];
    }
}
