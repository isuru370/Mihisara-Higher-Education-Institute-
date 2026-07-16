<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     * ->middleware('role:ADMIN')
     * ->middleware('role:ADMIN,SUPER_ADMIN')
     * ->middleware('role:TEACHER')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            abort(401, 'Unauthenticated.');
        }

        $user = Auth::user();

        // User active
        if (!$user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        // User type exists
        if (!$user->userType) {
            abort(403, 'User role not found.');
        }

        // Role active
        if (!$user->userType->is_active) {
            abort(403, 'User role is inactive.');
        }

        $currentRole = strtoupper($user->userType->code);

        // Super Admin bypass
        if ($currentRole === 'SUPER_ADMIN') {
            return $next($request);
        }

        // Normalize roles
        $roles = array_map('strtoupper', $roles);

        if (!in_array($currentRole, $roles)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}