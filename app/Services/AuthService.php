<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Login User
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            return [
                'success' => false,
                'message' => 'Invalid email or password',
            ];
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();

            return [
                'success' => false,
                'message' => 'Your account is inactive',
            ];
        }

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    /**
     * Logout User
     */
    public function logout(?User $user): void
    {
        if (!$user) {
            return;
        }

        $user->tokens()->delete();
    }
}
