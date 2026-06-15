<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\SendAttendanceSuccessSmsJob;
use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentTute;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentAttendanceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        try {
            $result = DB::transaction(function () use ($validated) {
                return $this->markAttendance($validated);
            });

            if (! $result['success']) {
                return $result['response'];
            }

           // $this->sendSuccessSmsIfAvailable($result);

            return $this->attendanceSuccessResponse($result);
        } catch (Throwable $e) {
            $this->logAttendanceError($e);

            return $this->errorResponse(
                'Something went wrong while marking attendance.',
                500
            );
        }
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'class_schedule_id' => ['required', 'integer', 'exists:class_schedules,id'],
            'student_class_id' => ['required', 'integer', 'exists:student_classes,id'],
            'class_category_fee_id' => ['required', 'integer', 'exists:class_category_fees,id'],
            'mark_method' => [
                'required',
                'string',
                Rule::in([
                    'qr_mobile',
                    'qr_web',
                    'manual_mobile',
                    'manual_web',
                ]),
            ],
            'mark_tute' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ]);
    }

    private function markAttendance(array $validated): array
    {
        $student = $this->findActiveStudent((int) $validated['student_id']);

        if (! $student) {
            return $this->failedResult('Student not found or inactive.', 404);
        }

        $schedule = $this->findValidSchedule($validated);

        if (! $schedule) {
            return $this->failedResult(
                'Class schedule not found, cancelled, completed, or inactive.',
                422
            );
        }

        if ($this->attendanceAlreadyMarked($student->id, $schedule->id)) {
            return $this->failedResult(
                'Attendance already marked for this student.',
                409
            );
        }

        $enrollment = $this->findEnrollment($student->id, $validated);

        $attendance = $this->createAttendance(
            $student,
            $schedule,
            $enrollment,
            $validated
        );

        $tute = null;

        if (! empty($validated['mark_tute']) && $validated['mark_tute'] === true) {
            if (! $enrollment) {
                return $this->failedResult(
                    'Student enrollment not found. Cannot issue tute.',
                    422
                );
            }

            $tute = $this->createStudentTute($student, $enrollment, $validated);
        }

        $schedule->update([
            'status' => 'ongoing',
            'is_active' => true,
        ]);

        return [
            'success' => true,
            'student' => $student,
            'schedule' => $schedule,
            'attendance' => $attendance,
            'enrollment' => $enrollment,
            'tute' => $tute,
        ];
    }

    private function findActiveStudent(int $studentId): ?Student
    {
        return Student::query()
            ->where('id', $studentId)
            ->where('is_active', true)
            ->where('student_disable', false)
            ->first();
    }

    private function findValidSchedule(array $validated): ?ClassSchedule
    {
        return ClassSchedule::query()
            ->where('id', (int) $validated['class_schedule_id'])
            ->where('student_class_id', (int) $validated['student_class_id'])
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->first();
    }

    private function attendanceAlreadyMarked(int $studentId, int $scheduleId): bool
    {
        return StudentAttendance::query()
            ->where('student_id', $studentId)
            ->where('class_schedule_id', $scheduleId)
            ->exists();
    }

    private function findEnrollment(int $studentId, array $validated): ?StudentClassEnrollment
    {
        return StudentClassEnrollment::query()
            ->where('student_id', $studentId)
            ->where('student_class_id', (int) $validated['student_class_id'])
            ->where('class_category_fee_id', (int) $validated['class_category_fee_id'])
            ->where('is_active', true)
            ->first();
    }

    private function createAttendance(
        Student $student,
        ClassSchedule $schedule,
        ?StudentClassEnrollment $enrollment,
        array $validated
    ): StudentAttendance {
        return StudentAttendance::create([
            'local_uuid' => null,
            'student_id' => $student->id,
            'class_schedule_id' => $schedule->id,
            'student_class_enrollment_id' => $enrollment?->id,
            'attended_at' => Carbon::now(),
            'mark_method' => $validated['mark_method'],
            'marked_by' => Auth::id(),
            'is_synced' => true,
            'note' => $validated['note'] ?? $this->defaultNote(),
        ]);
    }

    private function createStudentTute(
        Student $student,
        StudentClassEnrollment $enrollment,
        array $validated
    ): StudentTute {
        return StudentTute::create([
            'student_id' => $student->id,
            'student_class_enrollment_id' => $enrollment->id,
            'issued_month' => now()->startOfMonth()->toDateString(),
            'is_issued' => true,
            'issued_at' => now(),
            'issued_by' => Auth::id(),
            'note' => $validated['note'] ?? $this->defaultTuteNote(),
        ]);
    }

    private function defaultTuteNote(): string
    {
        return sprintf(
            'Tute issued via %s on %s',
            auth()->user()?->name ?? 'System',
            now()->format('Y-m-d H:i:s')
        );
    }

    private function defaultNote(): string
    {
        return sprintf(
            'Attendance marked via web by %s on %s',
            auth()->user()?->name ?? 'System',
            now()->format('Y-m-d H:i:s')
        );
    }

    private function sendSuccessSmsIfAvailable(array $result): void
    {
        $student = $result['student'];
        $attendance = $result['attendance'];
        $enrollment = $result['enrollment'];

        $guardianMobile = $student->guardian_mobile;

        if (! $guardianMobile) {
            return;
        }

        $enrollment?->loadMissing([
            'studentClass.grade',
            'classCategoryFee.category',
        ]);

        SendAttendanceSuccessSmsJob::dispatch(
            $guardianMobile,
            $this->buildSmsMessage($student, $attendance, $enrollment)
        );
    }

    private function buildSmsMessage(
        Student $student,
        StudentAttendance $attendance,
        ?StudentClassEnrollment $enrollment
    ): string {
        return sprintf(
            'Attendance marked. Student: %s, Class: %s, Category: %s, Grade: %s, Date: %s, Time: %s. Thank you.',
            $student->initial_name ?? $student->custom_id ?? 'Student',
            $enrollment?->studentClass?->class_name ?? 'N/A',
            $enrollment?->classCategoryFee?->category?->category_name ?? 'N/A',
            $enrollment?->studentClass?->grade?->grade_name ?? 'N/A',
            $attendance->attended_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            $attendance->attended_at?->format('H:i') ?? now()->format('H:i')
        );
    }

    private function attendanceSuccessResponse(array $result): JsonResponse
    {
        $student = $result['student'];
        $attendance = $result['attendance'];
        $enrollment = $result['enrollment'];

        return $this->successResponse([
            'attendance' => [
                'id' => $attendance->id,
                'student_id' => $attendance->student_id,
                'class_schedule_id' => $attendance->class_schedule_id,
                'student_class_enrollment_id' => $attendance->student_class_enrollment_id,
                'attended_at' => $attendance->attended_at?->format('Y-m-d H:i:s'),
                'mark_method' => $attendance->mark_method,
            ],
            'student' => [
                'id' => $student->id,
                'custom_id' => $student->custom_id,
                'initial_name' => $student->initial_name,
            ],
            'enrollment_status' => $enrollment ? 'enrolled' : 'new_student',
            'sms_queued' => (bool) $student->guardian_mobile,
        ]);
    }

    private function failedResult(string $message, int $statusCode): array
    {
        return [
            'success' => false,
            'response' => $this->errorResponse($message, $statusCode),
        ];
    }

    private function logAttendanceError(Throwable $e): void
    {
        Log::error('Attendance mark failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    private function successResponse(array $data): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Attendance marked successfully.',
            'data' => $data,
        ], 201);
    }

    private function errorResponse(string $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => [],
        ], $statusCode);
    }

    public function studentAttendanceHistory(int $studentId, int $enrolledId)
    {
        try {

            $enrollment = StudentClassEnrollment::query()
                ->where('id', $enrolledId)
                ->where('student_id', $studentId)
                ->firstOrFail();

            $enrolledAt = $enrollment->enrolled_at;
            $classCategoryFeeId = $enrollment->class_category_fee_id;

            $schedules = ClassSchedule::query()
                ->where('class_category_fee_id', $classCategoryFeeId)
                ->whereIn('status', ['scheduled', 'ongoing', 'completed'])
                ->when($enrolledAt, function ($query) use ($enrolledAt) {
                    $query->whereDate('class_date', '>=', $enrolledAt);
                })
                ->orderBy('class_date')
                ->orderBy('start_time')
                ->get();


            $attendanceMap = StudentAttendance::query()
                ->where('student_id', $studentId)
                ->where('student_class_enrollment_id', $enrolledId)
                ->whereIn('class_schedule_id', $schedules->pluck('id')->toArray())
                ->get()
                ->keyBy('class_schedule_id');


            $history = $schedules->map(function ($schedule) use ($attendanceMap) {

                $attendance = $attendanceMap->get($schedule->id);

                $classDate = optional($schedule->class_date)?->toDateString();
                $today = now()->toDateString();

                // Future scheduled classes
                if ($schedule->status === 'scheduled') {
                    $attendanceStatus = 'upcoming';
                }

                // Attendance marked
                elseif ($attendance) {
                    $attendanceStatus = 'present';
                }

                // Today's class
                elseif ($classDate === $today) {
                    $attendanceStatus = 'today';
                }

                // Past class without attendance
                else {
                    $attendanceStatus = 'absent';
                }

                return [
                    'class_schedule_id' => $schedule->id,
                    'class_date' => $classDate,
                    'day' => optional($schedule->class_date)->format('l'),
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'schedule_status' => $schedule->status,
                    'attendance_status' => $attendanceStatus,
                    'attended_at' => optional($attendance?->attended_at)?->toDateTimeString(),
                    'mark_method' => $attendance?->mark_method,
                    'note' => $attendance?->note,
                ];
            });

            $summary = [
                'total_schedules' => $history->count(),
                'present_count' => $history->where('attendance_status', 'present')->count(),
                'absent_count' => $history->where('attendance_status', 'absent')->count(),
                'today_count' => $history->where('attendance_status', 'today')->count(),
                'upcoming_count' => $history->where('attendance_status', 'upcoming')->count(),
            ];
            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $studentId,
                    'enrollment_id' => $enrolledId,
                    'enrolled_at' => optional($enrolledAt)->toDateString(),
                    'class_category_fee_id' => $classCategoryFeeId,
                    'summary' => $summary,
                    'history' => $history->values(),
                ],
            ]);
        } catch (ModelNotFoundException $e) {

            Log::warning('Student Enrollment Not Found', [
                'student_id' => $studentId,
                'enrolled_id' => $enrolledId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Student enrollment not found.',
            ], 404);
        } catch (Throwable $e) {

            Log::error('Student Attendance History Error', [
                'student_id' => $studentId,
                'enrolled_id' => $enrolledId,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching attendance history.',
            ], 500);
        }
    }
}
