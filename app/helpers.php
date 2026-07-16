<?php

if (!function_exists('hasPermission')) {

    function hasPermission($routeName = null)
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        if (!$user->is_active) {
            return false;
        }

        if (!$user->userType) {
            return false;
        }

        if (!$user->userType->is_active) {
            return false;
        }

        $role = strtoupper($user->userType->code);

        switch ($role) {

            case 'SUPER_ADMIN':
                return true;

            case 'ADMIN':
                return true;

            case 'TEACHER':
                return false;

            default:
                return false;
        }
    }

    
}