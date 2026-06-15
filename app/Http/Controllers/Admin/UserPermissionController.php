<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Permission;
use App\Models\SystemUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserPermissionController extends Controller
{
    public function index(SystemUser $systemUser)
    {
        $user = $systemUser->user;

        $allPages = Page::active()
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        $permissions = Permission::where(
            'user_type_id',
            $user->user_type_id
        )
            ->get()
            ->keyBy('page_id');

        return view(
            'admin.user-permissions.index',
            compact(
                'systemUser',
                'user',
                'allPages',
                'permissions'
            )
        );
    }

    public function store(Request $request, SystemUser $systemUser)
    {
        $validated = $request->validate([

            'permissions' => ['required', 'array'],

            'permissions.*.page_id' => [
                'required',
                'exists:pages,id'
            ],

            'permissions.*.can_view' => ['nullable', 'boolean'],
            'permissions.*.can_create' => ['nullable', 'boolean'],
            'permissions.*.can_update' => ['nullable', 'boolean'],
            'permissions.*.can_delete' => ['nullable', 'boolean'],
            'permissions.*.is_active' => ['nullable', 'boolean'],
        ]);

        try {

            $user = $systemUser->user;

            foreach ($validated['permissions'] as $permission) {

                Permission::updateOrCreate(

                    [
                        'user_type_id' => $user->user_type_id,
                        'page_id' => $permission['page_id'],
                    ],

                    [
                        'can_view'   => $permission['can_view'] ?? false,
                        'can_create' => $permission['can_create'] ?? false,
                        'can_update' => $permission['can_update'] ?? false,
                        'can_delete' => $permission['can_delete'] ?? false,
                        'is_active'  => $permission['is_active'] ?? true,
                    ]
                );
            }

            return redirect()
                ->route(
                    'admin.user-permissions.index',
                    $systemUser->id
                )
                ->with(
                    'success',
                    'Permissions updated successfully.'
                );
        } catch (\Throwable $e) {

            Log::error('Permission update failed', [

                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Something went wrong while updating permissions.'
                );
        }
    }
}
