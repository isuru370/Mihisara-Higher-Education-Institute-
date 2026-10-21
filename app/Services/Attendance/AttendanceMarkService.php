<?php

namespace App\Services\Attendance;

use App\Models\Student;
use App\Models\StudentAttendance;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceMarkService
{
    /**
     * Mark attendance for the class chosen by AttendanceScanService.
     *
     * Returns ['attendance' => StudentAttendance, 'already_marked' => bool].
     * If another device marked the same student for the same class a moment
     * earlier, the existing record is returned with already_marked = true.
     */
    public function mark(
        AttendanceContext $context,
        string $markMethod,
        ?string $note = null
    ): array {

        try {

            return DB::transaction(function () use ($context, $markMethod, $note) {

                $existing = $this->existing($context);

                if ($existing) {

                    return [
                        'attendance' => $existing,
                        'already_marked' => true,
                    ];
                }

                $attendance = StudentAttendance::create([
                    'local_uuid' => null,
                    'student_id' => $context->student->id,
                    'class_schedule_id' => $context->schedule->id,
                    'student_class_enrollment_id' => $context->enrollment->id,
                    'attended_at' => Carbon::now(),
                    'mark_method' => $markMethod,
                    'marked_by' => Auth::id(),
                    'is_synced' => true,
                    'note' => $note,
                ]);

                $context->schedule->update([
                    'status' => 'ongoing',
                    'is_active' => true,
                ]);

                return [
                    'attendance' => $attendance,
                    'already_marked' => false,
                ];
            });
        } catch (QueryException $e) {

            // Unique (student_id, class_schedule_id): a parallel scan won.
            $existing = $this->existing($context);

            if ($existing) {

                return [
                    'attendance' => $existing,
                    'already_marked' => true,
                ];
            }

            throw $e;
        }
    }

    /**
     * Is the student allowed to have attendance marked?
     */
    public function isStudentActive(Student $student): bool
    {
        return Student::query()
            ->whereKey($student->id)
            ->where('is_active', true)
            ->where('student_disable', false)
            ->exists();
    }

    protected function existing(AttendanceContext $context): ?StudentAttendance
    {
        return StudentAttendance::query()
            ->where('student_id', $context->student->id)
            ->where('class_schedule_id', $context->schedule->id)
            ->first();
    }
}
