<?php

namespace App\Http\Requests\Parent\FCM;

use Illuminate\Foundation\Http\FormRequest;

class SaveFcmTokenRequest extends FormRequest
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

            'token' => [
                'required',
                'string',
                'max:255',
            ],

            'device_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'device_type' => [
                'required',
                'in:android,ios',
            ],

            'app_version' => [
                'nullable',
                'string',
                'max:20',
            ],
        ];
    }
}
