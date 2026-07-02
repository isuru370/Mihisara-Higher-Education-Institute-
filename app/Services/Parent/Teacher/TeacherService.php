<?php

namespace App\Services\Parent\Teacher;

use App\Models\StudentClassEnrollment;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class TeacherService
{
    public function getAllTeachers(
        int $studentId
    ): Collection {

        try {

            // ✅ Get student's enrolled class IDs
            $studentClassIds = StudentClassEnrollment::query()
                ->where('student_id', $studentId)
                ->where('is_active', true)
                ->pluck('student_class_id')
                ->toArray();

            Log::info('Student enrolled classes', [
                'student_id' => $studentId,
                'class_ids' => $studentClassIds
            ]);

            $teachers = Teacher::query()
                ->select([
                    'id',
                    'custom_id',
                    'full_name',
                    'initials',
                    'mobile',
                    'email',
                    'is_active',
                ])
                ->where('is_active', true)
                ->with([
                    'classes' => function ($query) {

                        $query->select([
                            'id',
                            'teacher_id',
                            'class_name',
                            'medium',
                            'class_type',
                            'grade_id',
                            'is_active',
                            'is_ongoing',
                        ])
                        ->where('is_active', true)
                        ->with([
                            'grade:id,grade_name',
                            'categories:id,category_name',
                        ]);
                    },
                ])
                ->orderBy('full_name')
                ->get();

            $teachers->each(function ($teacher) use ($studentClassIds) {

                // ✅ Process each class and set is_my_class
                $teacher->classes->transform(function ($class) use ($studentClassIds) {

                    // ✅ Check if THIS class is enrolled by student
                    $isMyClass = in_array($class->id, $studentClassIds, true);

                    // ✅ Set the is_my_class flag
                    $class->is_my_class = $isMyClass;

                    Log::info('Class enrollment check', [
                        'class_id' => $class->id,
                        'class_name' => $class->class_name,
                        'is_my_class' => $isMyClass,
                        'student_class_ids' => $studentClassIds
                    ]);

                    return $class;
                });

                // ✅ Set teacher-level flag
                $teacher->is_my_teacher = $teacher->classes->contains('is_my_class', true);

                // ✅ Sort: Enrolled classes first
                $teacher->classes = $teacher->classes
                    ->sortByDesc('is_my_class')
                    ->values();

                Log::info('Teacher processed', [
                    'teacher_id' => $teacher->id,
                    'teacher_name' => $teacher->full_name,
                    'is_my_teacher' => $teacher->is_my_teacher,
                    'enrolled_classes' => $teacher->classes->where('is_my_class', true)->pluck('class_name')->toArray(),
                ]);
            });

            return $teachers;

        } catch (Throwable $e) {

            Log::error('Failed to fetch teachers.', [
                'student_id' => $studentId,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }
}