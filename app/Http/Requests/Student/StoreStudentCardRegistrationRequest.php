<?php

namespace App\Http\Requests\Student;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentCardRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'admission' => $this->boolean('admission'),
        ]);
    }

    public function rules(): array
    {
        return [
            'card_qr_code' => [
                'required',
                'string',
            ],

            'full_name' => 'required|string|max:150',
            'initial_name' => 'required|string|max:100',

            'mobile' => 'required|string|max:20',
            'whatsapp_mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150|unique:students,email',

            'nic' => 'nullable|string|max:20|unique:students,nic',
            'bday' => 'nullable|date|before:today',
            'gender' => 'required|in:male,female,other',

            'address1' => 'required|string|max:150',
            'address2' => 'nullable|string|max:150',
            'address3' => 'nullable|string|max:150',

            'guardian_fname' => 'nullable|string|max:100',
            'guardian_lname' => 'nullable|string|max:100',
            'guardian_nic' => 'nullable|string|max:20',
            'guardian_mobile' => 'required|string|max:20',

            'grade_id' => 'required|exists:grades,id',
            'class_type' => 'required|in:online,offline,hybrid',

            'admission' => 'boolean',
            'admission_id' => [
                Rule::requiredIf($this->boolean('admission')),
                'nullable',
                'exists:admissions,id',
            ],

            'student_school' => 'nullable|string|max:150',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'card_qr_code.required' => 'Student card QR code is required.',
            'admission_id.required' => 'Please select an admission type.',
        ];
    }
}
