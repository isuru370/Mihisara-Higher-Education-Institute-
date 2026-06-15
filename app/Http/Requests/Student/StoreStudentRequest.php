<?php

namespace App\Http\Requests\Student;

use App\Models\TemporaryIdCard;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'admission' => $this->boolean('admission'),

            'temporary_qr_code_expire_date' => $this->temporary_qr_code
                ? Carbon::now('Asia/Colombo')->addMonths(2)
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'temporary_qr_code' => [
                'required',
                'string',
                'regex:/^TMP\d{3,}$/',
                'unique:students,temporary_qr_code',
                'exists:temporary_id_cards,temporary_id_number',
                function ($attribute, $value, $fail) {
                    if ((int) substr($value, 3) < 1) {
                        $fail('The temporary QR code must start from TMP001.');
                    }

                    $card = TemporaryIdCard::where('temporary_id_number', $value)->first();

                    if ($card && $card->status === 'expired') {
                        $fail('This temporary QR code is already expired.');
                    }

                    if ($card && $card->status === 'active') {
                        $fail('This temporary QR code is already active.');
                    }
                },
            ],

            'temporary_qr_code_expire_date' => [
                'required_with:temporary_qr_code',
                'date',
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
            'temporary_qr_code.regex' => 'Temporary QR code must be like TMP001, TMP010, TMP100, or TMP1000.',
            'grade_id.required' => 'Grade is required.',
            'admission_id.required' => 'Please select an admission type.',
        ];
    }
}
