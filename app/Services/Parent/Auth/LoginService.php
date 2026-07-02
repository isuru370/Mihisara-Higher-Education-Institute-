<?php

namespace App\Services\Parent\Auth;

use App\Models\FcmToken;
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

        // ✅ Build full image URL
        $imgUrl = $this->getImageUrl($student->img_url);

        return [
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                // ========== Basic Information ==========
                'student_id' => $student->id,
                'custom_id' => $student->custom_id,
                'temporary_id' => $student->temporary_qr_code,
                'temporary_id_expire' => $student->temporary_qr_code_expire_date?->toISOString(),

                // ========== Personal Information ==========
                'full_name' => $student->full_name,
                'initial_name' => $student->initial_name,
                'mobile' => $student->mobile,
                'whatsapp_mobile' => $student->whatsapp_mobile,
                'email' => $student->email,

                // ========== Demographics ==========
                'gender' => $student->gender,
                'nic' => $student->nic,
                'bday' => $student->bday?->toISOString(),

                // ========== Address ==========
                'address1' => $student->address1,
                'address2' => $student->address2,
                'address3' => $student->address3,

                // ========== Guardian Information ==========
                'guardian_fname' => $student->guardian_fname,
                'guardian_lname' => $student->guardian_lname,
                'guardian_nic' => $student->guardian_nic,
                'guardian_mobile' => $student->guardian_mobile,

                // ========== Academic Information ==========
                'grade_id' => $student->grade_id,
                'grade_name' => $student->grade?->grade_name,
                'class_type' => $student->class_type,
                'student_school' => $student->student_school,

                // ========== Image ==========
                'img_url' => $imgUrl,
                'last_image_update_at' => $student->last_image_update_at?->toISOString(),

                // ========== Status Flags ==========
                'is_active' => $student->is_active,
                'admission' => $student->admission,
                'permanent_qr_active' => $student->permanent_qr_active,
                'student_disable' => $student->student_disable,

                // ========== Timestamps ==========
                'created_at' => $student->created_at?->toISOString(),
                'updated_at' => $student->updated_at?->toISOString(),
            ],
        ];
    }

    /**
     * ✅ Generate full image URL
     */
    private function getImageUrl(?string $imgUrl): string
    {
        if (empty($imgUrl)) {
            return '';
        }

        // If already full URL, return as is
        if (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
            return $imgUrl;
        }

        // Otherwise, prepend storage URL
        $baseUrl = config('app.url');
        return $baseUrl . '/storage/' . ltrim($imgUrl, '/');
    }

    public function logout(
        int $studentId,
        string $deviceId
    ): array {

        $token = FcmToken::where('student_id', $studentId)
            ->where('device_id', $deviceId)
            ->first();

        if (!$token) {
            return [
                'status' => false,
                'message' => 'Device not found.',
            ];
        }

        $token->update([
            'is_active' => false,
        ]);

        return [
            'status' => true,
            'message' => 'Logout successful.',
        ];
    }

    public function changePassword(array $data): array
    {
        $login = StudentPortalLogin::where(
            'student_id',
            $data['student_id']
        )->first();

        if (!$login) {
            return [
                'status' => false,
                'message' => 'Student account not found.',
            ];
        }

        if (!Hash::check(
            $data['current_password'],
            $login->password
        )) {

            return [
                'status' => false,
                'message' => 'Current password is incorrect.',
            ];
        }

        $login->update([
            'password' => $data['new_password'],
        ]);

        return [
            'status' => true,
            'message' => 'Password changed successfully.',
        ];
    }
}
