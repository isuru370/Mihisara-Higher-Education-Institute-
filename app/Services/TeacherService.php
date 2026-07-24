<?php

namespace App\Services;

use App\Jobs\SendTeacherLoginSms;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeacherService
{
    /**
     * Create Login Account for Teacher
     */
    public function createLogin(Teacher $teacher, string $password): void
    {
        DB::transaction(function () use ($teacher, $password) {

            if (User::where('email', $teacher->email)->exists()) {
                throw new \Exception('A user already exists with this email.');
            }

            $teacherUserTypeId = UserType::where('code', 'TEACHER')->value('id');

            $user = User::create([
                'name'         => $teacher->full_name,
                'email'        => $teacher->email,
                'password'     => $password,
                'user_type_id' => $teacherUserTypeId,
                'is_active'    => true,
            ]);

            $teacher->update([
                'user_id' => $user->id,
            ]);

            SendTeacherLoginSms::dispatch(
                $teacher->mobile,
                $teacher->email,
                $password
            );
        });
    }

    public function resetPassword(Teacher $teacher, string $password): void
    {
        DB::transaction(function () use ($teacher, $password) {

            if (!$teacher->user_id) {
                throw new ModelNotFoundException('Teacher login account not found.');
            }

            $user = User::findOrFail($teacher->user_id);

            $user->update([
                'password' => $password,
            ]);

            SendTeacherLoginSms::dispatch(
                $teacher->mobile,
                $teacher->email,
                $password
            );
        });
    }
}
