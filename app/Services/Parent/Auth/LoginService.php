<?php

namespace App\Services\Parent\Auth;

use App\Models\StudentPortalLogin;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function login(string $username, string $password): array
    {
        $user = StudentPortalLogin::query()
            ->with(['student.grade'])
            ->where('username', $username)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'status' => false,
                'message' => 'Invalid username or password',
            ];
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        $student = $user->student;

        return [
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'student_id'      => $student->id,
                'custom_id'       => $student->custom_id,
                'temporary_id'    => $student->temporary_qr_code,
                'temporary_id_expire' => $student->temporary_qr_code_expire_date,
                'full_name'       => $student->full_name,
                'initial_name'    => $student->initial_name,
                'mobile'          => $student->mobile,
                'whatsapp_mobile' => $student->whatsapp_mobile,
                'email'           => $student->email,
                'gender'          => $student->gender,
                'address1'        => $student->address1,
                'address2'        => $student->address2,
                'address3'        => $student->address3,
                'guardian_fname'  => $student->guardian_fname,
                'guardian_lname'  => $student->guardian_lname,
                'guardian_mobile' => $student->guardian_mobile,
                'grade_id'        => $student->grade_id,
                'grade_name'      => $student->grade?->grade_name,
                'student_school'  => $student->student_school,
                'img_url'         => $student->img_url,
                'is_active'       => $student->is_active,
            ],
        ];
    }
}