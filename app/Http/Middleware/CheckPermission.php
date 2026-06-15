<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized');
        }

        $user = Auth::user();

        // 🔥 Super Admin bypass
        if ($user->user_type_id == 1) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $next($request);
        }

        $hasPermission = DB::table('permissions')
            ->join('pages', 'permissions.page_id', '=', 'pages.id')
            ->where('permissions.user_type_id', $user->user_type_id)
            ->where('pages.route_name', $routeName)
            ->where('permissions.is_active', true)
            ->exists();

        if (!$hasPermission) {
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}