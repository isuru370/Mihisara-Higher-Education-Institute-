<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('hasPermission')) {

    function hasPermission($routeName)
    {
        if (!auth()->check()) return false;

        $user = auth()->user();

        // 🔥 admin bypass
        if ($user->user_type_id == 1) {
            return true;
        }

        return DB::table('permissions')
            ->join('pages', 'permissions.page_id', '=', 'pages.id')
            ->where('permissions.user_type_id', $user->user_type_id)
            ->where('pages.route_name', $routeName)
            ->where('permissions.is_active', true)
            ->exists();
    }
}