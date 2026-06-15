<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'full_name' => 'required|string|max:150',
            'initials' => 'required|string|max:20',

            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('teachers', 'email')->ignore($teacher?->id),
            ],

            'mobile' => 'required|string|max:20',

            'nic' => [
                'required',
                'string',
                'max:20',
                Rule::unique('teachers', 'nic')->ignore($teacher?->id),
            ],

            'bday' => 'required|date',
            'gender' => 'required|in:male,female,other',

            'address1' => 'required|string|max:150',
            'address2' => 'nullable|string|max:150',
            'address3' => 'nullable|string|max:150',

            'graduation_details' => 'nullable|string',
            'experience' => 'nullable|string',
            'account_number' => 'nullable|string|max:50',
            'bank_branch_id' => 'nullable|exists:bank_branches,id',

            'is_active' => 'nullable|boolean',
        ];
    }
}