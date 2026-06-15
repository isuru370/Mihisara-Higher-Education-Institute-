<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use App\Models\SystemUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserProfileController extends Controller
{
    public function index()
    {
        try {

            $user = User::with([
                'systemUser',
                'userType'
            ])->findOrFail(auth()->id());

            return view('admin.user-profile.index', compact('user'));
        } catch (Throwable $e) {

            Log::error('User profile load failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return redirect()->back()
                ->with('error', 'Unable to load profile details.');
        }
    }

    public function update(UpdateProfileRequest $request)
    {
        try {

            DB::beginTransaction();

            $user = User::findOrFail(auth()->id());

            $validated = $request->validated();

            $user->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ]);

            $systemUser = SystemUser::firstOrNew([
                'user_id' => $user->id
            ]);

            $systemUser->fill([
                'user_id'   => $user->id,
                'full_name' => $validated['full_name'],
                'mobile'    => $validated['mobile'] ?? null,
                'nic'       => $validated['nic'] ?? null,
                'bday'      => $validated['bday'] ?? null,
                'gender'    => $validated['gender'] ?? null,
                'address1'  => $validated['address1'] ?? null,
                'address2'  => $validated['address2'] ?? null,
                'address3'  => $validated['address3'] ?? null,
                'note'      => $validated['note'] ?? null,
            ]);

            $systemUser->save();

            DB::commit();

            return back()->with(
                'success',
                'Profile updated successfully.'
            );
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Profile update failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update profile.');
        }
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {

            DB::beginTransaction();

            $user = User::findOrFail(auth()->id());

            $user->update([
                'password' => $request->password
            ]);

            DB::commit();

            Log::info('Password changed', [
                'user_id' => $user->id,
            ]);

            return back()->with(
                'success',
                'Password changed successfully.'
            );
        } catch (Throwable $e) {

            DB::rollBack();

            Log::error('Password change failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return back()->with(
                'error',
                'Failed to change password.'
            );
        }
    }
}
