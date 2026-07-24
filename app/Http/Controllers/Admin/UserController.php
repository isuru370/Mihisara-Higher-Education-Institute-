<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use App\Models\Teacher;
use App\Models\SystemUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with filtering and pagination.
     * Excludes SUPER_ADMIN users from the list.
     */
    public function index(Request $request)
    {
        // Get SUPER_ADMIN user type ID to exclude
        $superAdminTypeId = UserType::where('code', 'SUPER_ADMIN')->value('id');

        $query = User::with(['userType', 'systemUser', 'teacher'])
            ->withTrashed()
            ->where('user_type_id', '!=', $superAdminTypeId); // Exclude SUPER_ADMIN

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        // Filter by user type
        if ($request->filled('user_type')) {
            $query->where('user_type_id', $request->user_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'deleted') {
                $query->onlyTrashed();
            }
        }

        // Sorting
        $sortField = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'is_active', 'created_at', 'updated_at'];
        
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $users = $query->paginate($request->input('per_page', 15))
            ->appends($request->all());

        // Get user types excluding SUPER_ADMIN for filter
        $userTypes = UserType::where('code', '!=', 'SUPER_ADMIN')->get();

        // Statistics (excluding SUPER_ADMIN)
        $stats = [
            'total' => User::withTrashed()->where('user_type_id', '!=', $superAdminTypeId)->count(),
            'active' => User::where('user_type_id', '!=', $superAdminTypeId)->where('is_active', true)->count(),
            'inactive' => User::where('user_type_id', '!=', $superAdminTypeId)->where('is_active', false)->count(),
            'deleted' => User::onlyTrashed()->where('user_type_id', '!=', $superAdminTypeId)->count(),
        ];

        return view('admin.users.index', compact('users', 'userTypes', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     * Excludes SUPER_ADMIN from available user types.
     */
    public function create()
    {
        // Exclude SUPER_ADMIN from user types
        $userTypes = UserType::where('is_active', true)
            ->where('code', '!=', 'SUPER_ADMIN')
            ->get();
            
        // Get system users that don't have a user account yet
        $systemUsers = SystemUser::whereDoesntHave('user')->get();
        
        // Get teachers that don't have a user account yet
        $teachers = Teacher::whereDoesntHave('user')->get();
        
        return view('admin.users.create', compact('userTypes', 'systemUsers', 'teachers'));
    }

    /**
     * Store a newly created user in storage.
     * Prevents creating SUPER_ADMIN users.
     */
    public function store(Request $request)
    {
        // Check if trying to create SUPER_ADMIN
        $userType = UserType::find($request->user_type_id);
        if ($userType && $userType->code === 'SUPER_ADMIN') {
            return redirect()->back()
                ->with('error', 'Cannot create SUPER_ADMIN users through this interface.')
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type_id' => 'required|exists:user_types,id',
            'is_active' => 'boolean',
            'teacher_id' => 'nullable|exists:teachers,id|unique:users,teacher_id',
            'system_user_id' => 'nullable|exists:system_users,id|unique:users,system_user_id',
        ], [
            'email.unique' => 'This email is already registered.',
            'teacher_id.unique' => 'This teacher already has a user account.',
            'system_user_id.unique' => 'This system user already has a user account.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type_id' => $request->user_type_id,
                'is_active' => $request->boolean('is_active', true),
                'teacher_id' => $request->teacher_id,
            ]);

            // Link to system user if provided
            if ($request->filled('system_user_id')) {
                SystemUser::where('id', $request->system_user_id)->update(['user_id' => $user->id]);
            }

            DB::commit();

            Log::info('User created', [
                'user_id' => $user->id,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to create user. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified user details.
     * Prevents viewing SUPER_ADMIN users.
     */
    public function show(User $user)
    {
        // Prevent viewing SUPER_ADMIN users
        if ($this->isSuperAdmin($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot view SUPER_ADMIN user details.');
        }

        $user->load(['userType', 'systemUser', 'teacher', 'payments' => function ($query) {
            $query->latest()->limit(10);
        }]);

        $stats = [
            'total_payments' => $user->payments()->count(),
            'total_amount' => $user->payments()->sum('amount'),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     * Prevents editing SUPER_ADMIN users.
     */
    public function edit(User $user)
    {
        // Prevent editing SUPER_ADMIN users
        if ($this->isSuperAdmin($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot edit SUPER_ADMIN users.');
        }

        // Exclude SUPER_ADMIN from user types
        $userTypes = UserType::where('is_active', true)
            ->where('code', '!=', 'SUPER_ADMIN')
            ->get();
            
        // Get system users that don't have a user account or are linked to this user
        $systemUsers = SystemUser::whereDoesntHave('user')
            ->orWhere('user_id', $user->id)
            ->get();
            
        // Get teachers that don't have a user account or are linked to this user
        $teachers = Teacher::whereDoesntHave('user')
            ->orWhere('user_id', $user->id)
            ->get();
        
        return view('admin.users.edit', compact('user', 'userTypes', 'systemUsers', 'teachers'));
    }

    /**
     * Update the specified user in storage.
     * Prevents updating SUPER_ADMIN users.
     */
    public function update(Request $request, User $user)
    {
        // Prevent updating SUPER_ADMIN users
        if ($this->isSuperAdmin($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot update SUPER_ADMIN users.');
        }

        // Check if trying to change to SUPER_ADMIN
        if ($request->filled('user_type_id')) {
            $newUserType = UserType::find($request->user_type_id);
            if ($newUserType && $newUserType->code === 'SUPER_ADMIN') {
                return redirect()->back()
                    ->with('error', 'Cannot assign SUPER_ADMIN role through this interface.')
                    ->withInput();
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'user_type_id' => 'required|exists:user_types,id',
            'is_active' => 'boolean',
            'teacher_id' => [
                'nullable',
                'exists:teachers,id',
                Rule::unique('users', 'teacher_id')->ignore($user->id),
            ],
            'system_user_id' => [
                'nullable',
                'exists:system_users,id',
                Rule::unique('users', 'system_user_id')->ignore($user->id),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'user_type_id' => $request->user_type_id,
                'is_active' => $request->boolean('is_active', $user->is_active),
                'teacher_id' => $request->teacher_id,
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $validator = Validator::make($request->all(), [
                    'password' => 'required|string|min:8|confirmed',
                ]);

                if ($validator->fails()) {
                    return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
                }

                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Handle system user link
            if ($request->has('system_user_id')) {
                // Remove old link if exists
                SystemUser::where('user_id', $user->id)->update(['user_id' => null]);
                
                if ($request->filled('system_user_id')) {
                    SystemUser::where('id', $request->system_user_id)->update(['user_id' => $user->id]);
                }
            }

            DB::commit();

            Log::info('User updated', [
                'user_id' => $user->id,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to update user. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified user from storage (soft delete).
     * Prevents deleting SUPER_ADMIN users.
     */
    public function destroy(User $user)
    {
        // Prevent deleting SUPER_ADMIN users
        if ($this->isSuperAdmin($user)) {
            return redirect()->back()
                ->with('error', 'Cannot delete SUPER_ADMIN users.');
        }

        try {
            // Check if user has related records
            if ($user->systemUser()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete user with active system user relationship. Please unlink first.');
            }

            $userName = $user->name;
            $user->delete();

            Log::info('User deleted', [
                'user_id' => $user->id,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$userName}' deleted successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to delete user. Please try again.');
        }
    }

    /**
     * Permanently delete the specified user.
     * Prevents permanent deletion of SUPER_ADMIN users.
     */
    public function forceDelete($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            // Prevent deleting SUPER_ADMIN users
            if ($this->isSuperAdmin($user)) {
                return redirect()->back()
                    ->with('error', 'Cannot permanently delete SUPER_ADMIN users.');
            }

            // Check for relationships
            if ($user->systemUser()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot permanently delete user with active relationships.');
            }

            $userName = $user->name;
            $user->forceDelete();

            Log::info('User permanently deleted', [
                'user_id' => $id,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$userName}' permanently deleted!");

        } catch (\Exception $e) {
            Log::error('Failed to permanently delete user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to permanently delete user.');
        }
    }

    /**
     * Restore a soft-deleted user.
     * Prevents restoring SUPER_ADMIN users.
     */
    public function restore($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            // Prevent restoring SUPER_ADMIN users
            if ($this->isSuperAdmin($user)) {
                return redirect()->back()
                    ->with('error', 'Cannot restore SUPER_ADMIN users.');
            }

            $userName = $user->name;
            $user->restore();

            Log::info('User restored', [
                'user_id' => $id,
                'restored_by' => auth()->id()
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$userName}' restored successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to restore user: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to restore user.');
        }
    }

    /**
     * Toggle user active status.
     * Prevents toggling SUPER_ADMIN users.
     */
    public function toggleActive(User $user)
    {
        // Prevent toggling SUPER_ADMIN users
        if ($this->isSuperAdmin($user)) {
            return redirect()->back()
                ->with('error', 'Cannot change status of SUPER_ADMIN users.');
        }

        try {
            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'activated' : 'deactivated';

            Log::info('User status toggled', [
                'user_id' => $user->id,
                'status' => $status,
                'updated_by' => auth()->id()
            ]);

            return redirect()->back()
                ->with('success', "User {$status} successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to toggle user status: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to toggle user status.');
        }
    }

    /**
     * Check if user is SUPER_ADMIN.
     */
    private function isSuperAdmin($user)
    {
        return $user->userType && $user->userType->code === 'SUPER_ADMIN';
    }

    /**
     * Bulk action for users.
     * Prevents actions on SUPER_ADMIN users.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,delete,restore',
        ]);

        try {
            $action = $request->action;
            $userIds = $request->user_ids;

            // Get SUPER_ADMIN user type ID
            $superAdminTypeId = UserType::where('code', 'SUPER_ADMIN')->value('id');

            // Check if any SUPER_ADMIN users are selected
            $superAdminUsers = User::whereIn('id', $userIds)
                ->where('user_type_id', $superAdminTypeId)
                ->count();

            if ($superAdminUsers > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot perform bulk actions on SUPER_ADMIN users.');
            }

            DB::beginTransaction();

            switch ($action) {
                case 'activate':
                    User::whereIn('id', $userIds)
                        ->where('user_type_id', '!=', $superAdminTypeId)
                        ->update(['is_active' => true]);
                    $message = 'Selected users activated successfully!';
                    break;

                case 'deactivate':
                    User::whereIn('id', $userIds)
                        ->where('user_type_id', '!=', $superAdminTypeId)
                        ->update(['is_active' => false]);
                    $message = 'Selected users deactivated successfully!';
                    break;

                case 'delete':
                    User::whereIn('id', $userIds)
                        ->where('user_type_id', '!=', $superAdminTypeId)
                        ->delete();
                    $message = 'Selected users deleted successfully!';
                    break;

                case 'restore':
                    User::withTrashed()
                        ->whereIn('id', $userIds)
                        ->where('user_type_id', '!=', $superAdminTypeId)
                        ->restore();
                    $message = 'Selected users restored successfully!';
                    break;
            }

            DB::commit();

            Log::info('Bulk action performed on users', [
                'action' => $action,
                'user_ids' => $userIds,
                'performed_by' => auth()->id()
            ]);

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk action failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to perform bulk action.');
        }
    }

    /**
     * Change user password.
     * Prevents changing SUPER_ADMIN passwords.
     */
    public function changePassword(Request $request, User $user)
    {
        // Prevent changing SUPER_ADMIN password
        if ($this->isSuperAdmin($user)) {
            return redirect()->back()
                ->with('error', 'Cannot change password for SUPER_ADMIN users.');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            Log::info('Password changed for user', [
                'user_id' => $user->id,
                'changed_by' => auth()->id()
            ]);

            return redirect()->back()
                ->with('success', 'Password changed successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to change password: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to change password.');
        }
    }

    /**
     * Export users to Excel.
     * Excludes SUPER_ADMIN users.
     */
    public function exportExcel()
    {
        try {
            $superAdminTypeId = UserType::where('code', 'SUPER_ADMIN')->value('id');

            $users = User::with(['userType', 'systemUser', 'teacher'])
                ->withTrashed()
                ->where('user_type_id', '!=', $superAdminTypeId)
                ->get();

            // You can implement Excel export here
            return redirect()->back()
                ->with('info', 'Excel export coming soon!');

        } catch (\Exception $e) {
            Log::error('Excel export failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to export users to Excel.');
        }
    }

    /**
     * Export users to PDF.
     * Excludes SUPER_ADMIN users.
     */
    public function exportPdf()
    {
        try {
            $superAdminTypeId = UserType::where('code', 'SUPER_ADMIN')->value('id');

            $users = User::with(['userType', 'systemUser', 'teacher'])
                ->withTrashed()
                ->where('user_type_id', '!=', $superAdminTypeId)
                ->get();

            // You can implement PDF export here
            return redirect()->back()
                ->with('info', 'PDF export coming soon!');

        } catch (\Exception $e) {
            Log::error('PDF export failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to export users to PDF.');
        }
    }

    /**
     * Get user types for API.
     * Excludes SUPER_ADMIN.
     */
    public function getUserTypes()
    {
        try {
            $userTypes = UserType::select('id', 'name', 'code')
                ->where('is_active', true)
                ->where('code', '!=', 'SUPER_ADMIN')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $userTypes
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user types: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user types'
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for users.
     * Excludes SUPER_ADMIN users.
     */
    public function getStats()
    {
        try {
            $superAdminTypeId = UserType::where('code', 'SUPER_ADMIN')->value('id');

            $stats = [
                'total_users' => User::where('user_type_id', '!=', $superAdminTypeId)->count(),
                'active_users' => User::where('user_type_id', '!=', $superAdminTypeId)
                    ->where('is_active', true)
                    ->count(),
                'inactive_users' => User::where('user_type_id', '!=', $superAdminTypeId)
                    ->where('is_active', false)
                    ->count(),
                'deleted_users' => User::onlyTrashed()
                    ->where('user_type_id', '!=', $superAdminTypeId)
                    ->count(),
                'user_types' => UserType::withCount(['users' => function ($query) use ($superAdminTypeId) {
                    $query->where('user_type_id', '!=', $superAdminTypeId);
                }])
                    ->where('is_active', true)
                    ->where('code', '!=', 'SUPER_ADMIN')
                    ->get(),
                'recent_users' => User::with('userType', 'teacher')
                    ->where('user_type_id', '!=', $superAdminTypeId)
                    ->latest()
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics'
            ], 500);
        }
    }
}