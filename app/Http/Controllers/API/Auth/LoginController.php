<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected AuthService $authService;

    /**
     * Constructor
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * API Login
     */
    public function login(Request $request)
    {
        $result = $this->authService->login($request);

        if (!$result['success']) {

            return response()->json([
                'status' => 'error',
                'message' => $result['message']
            ], 401);
        }

        // create token
        $token = $result['user']
            ->createToken('mobile-token')
            ->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $result['user']
        ],200);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful'
        ],200);
    }
}
