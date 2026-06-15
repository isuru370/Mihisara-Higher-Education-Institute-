<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserActive
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();

        // 🔥 inactive user block
        if (!$user->is_active) {
            Auth::logout();
            abort(403, 'Your account is inactive');
        }

        return $next($request);
    }
}