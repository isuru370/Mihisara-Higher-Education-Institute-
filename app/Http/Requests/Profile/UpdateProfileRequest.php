<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],

            'email'     => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(auth()->id())
            ],

            'full_name' => ['required', 'string', 'max:255'],
            'mobile'    => ['nullable', 'string', 'max:20'],
            'nic'       => ['nullable', 'string', 'max:20'],
            'bday'      => ['nullable', 'date'],
            'gender'    => ['nullable', 'in:male,female,other'],
            'address1'  => ['nullable', 'string', 'max:255'],
            'address2'  => ['nullable', 'string', 'max:255'],
            'address3'  => ['nullable', 'string', 'max:255'],
            'note'      => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Name is required.',
            'email.required'     => 'Email is required.',
            'email.unique'       => 'This email is already taken.',
            'full_name.required' => 'Full name is required.',
        ];
    }
}
