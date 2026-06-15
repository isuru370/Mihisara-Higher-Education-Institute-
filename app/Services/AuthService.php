<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    /**
     * Handle Login
     */
    public function login(Request $request): array
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        // login attempt
        if (!Auth::attempt($credentials, $request->remember)) {

            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        $user = Auth::user();

        // inactive user block
        if (!$user->is_active) {

            Auth::logout();

            return [
                'success' => false,
                'message' => 'Your account is inactive'
            ];
        }

        return [
            'success' => true,
            'user' => $user
        ];
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        Auth::logout();
    }
}
