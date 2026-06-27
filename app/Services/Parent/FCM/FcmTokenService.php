<?php

namespace App\Services\Parent\FCM;

use App\Models\FcmToken;

class FcmTokenService
{
    public function save(array $data): void
    {
        FcmToken::updateOrCreate(

            [
                'token' => $data['token'],
            ],

            [
                'student_id' => $data['student_id'],
                'device_name' => $data['device_name'] ?? null,
                'device_type' => $data['device_type'],
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );
    }

    public function logout(array $data): void
    {
        FcmToken::where('token', $data['token'])
            ->update([
                'is_active' => false,
            ]);
    }

    public function activeTokens(int $studentId)
    {
        return FcmToken::query()
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->pluck('token');
    }
}