<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;

class LoginController extends Controller
{
    protected $authService;

    /**
     * Constructor
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show login page
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $result = $this->authService->login($request);

        if (!$result['success']) {

            return back()->withErrors([
                'email' => $result['message']
            ]);
        }

        return redirect()->route('admin.dashboard');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->authService->logout();

        return redirect()->route('login');
    }
}
