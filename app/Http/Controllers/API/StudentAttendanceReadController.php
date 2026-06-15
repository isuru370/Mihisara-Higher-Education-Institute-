<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassCategoryFee;
use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClass;
use App\Models\StudentClassEnrollment;
use App\Models\StudentTute;
use App\Services\StudentQRService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class StudentAttendanceReadController extends Controller
{
    public function read(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string', 'max:150'],
            'student_class_id' => ['required', 'integer', 'exists:student_classes,id'],
            'class_category_fee_id' => ['required', 'integer', 'exists:class_category_fees,id'],
        ]);

        try {
            $classDetails = $this->getClassDetails(
                (int) $validated['student_class_id'],
                (int) $validated['class_category_fee_id']
            );

            if (! $classDetails) {
                return $this->errorResponse(
                    'Selected class or category fee is invalid.',
                    422
                );
            }

            $qrResult = StudentQRService::read($validated['qr_code']);

            if (! is_array($qrResult) || ! ($qrResult['success'] ?? false)) {
                return $this->errorResponse(
                    $qrResult['message'] ?? 'Invalid QR code',
                    $qrResult['status_code'] ?? 422
                );
            }

            $student = $this->getActiveStudent((int) $qrResult['student_id']);

            if (! $student) {
                return $this->errorResponse(
                    'Student not found or inactive',
                    404
                );
            }

            $enrollment = $this->getStudentEnrollment(
                studentId: (int) $student->id,
                studentClassId: (int) $validated['student_class_id'],
                classCategoryFeeId: (int) $validated['class_category_fee_id']
            );

            $tuteSummary = $enrollment
                ? $this->getTuteSummary(
                    studentId: (int) $student->id,
                    enrollmentId: (int) $enrollment->id
                )
                : null;


            $isEnrolled = (bool) $enrollment;

            $lastPayment = $enrollment
                ? $this->getLastPayment($enrollment)
                : null;

            $attendance = $this->getAttendanceSummary(
                studentId: (int) $student->id,
                studentClassId: (int) $validated['student_class_id'],
                enrollmentId: $enrollment?->id,
                classCategoryFeeId: (int) $validated['class_category_fee_id']
            );

            return $this->successResponse([
                'student' => [
                    'id' => $student->id,
                    'custom_id' => $student->permanent_qr_active == 1
                        ? $student->custom_id
                        : $student->temporary_qr_code,
                    'initial_name' => $student->initial_name,
                    'mobile' => $student->mobile,
                    'guardian_mobile' => $student->guardian_mobile,
                    'img_url' => $student->img_url,
                    'qr_type' => $qrResult['qr_type'] ?? null,
                ],
                'enrollment' => [
                    'status' => $isEnrolled ? 'enrolled' : 'new_student',
                    'is_enrolled' => $isEnrolled,
                    'enrollment_id' => $enrollment?->id,
                    'student_class_id' => (int) $validated['student_class_id'],
                    'class_category_fee_id' => (int) $validated['class_category_fee_id'],
                    'class_name' => $classDetails['class_name'] ?? null,
                    'grade' => $classDetails['grade'] ?? null,
                    'subject' => $classDetails['subject'] ?? null,
                    'teacher' => $classDetails['teacher'] ?? null,
                    'category_name' => $classDetails['category_name'] ?? null,
                    'default_fee' => $classDetails['default_fee'] ?? null,
                    'is_free_card' => $enrollment ? (bool) $enrollment->is_free_card : false,
                    'final_fee' => $enrollment?->final_fee ?? ($classDetails['default_fee'] ?? null),
                ],
                'last_payment' => $lastPayment,
                'attendance' => $attendance,
                'tute' => $tuteSummary,
            ]);
        } catch (Throwable $e) {
            Log::error('Attendance read failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->errorResponse(
                'Something went wrong while reading attendance details',
                500
            );
        }
    }

    private function getActiveStudent(int $studentId): ?Student
    {
        return Student::query()
            ->select([
                'id',
                'custom_id',
                'temporary_qr_code',
                'initial_name',
                'mobile',
                'guardian_mobile',
                'img_url',
                'is_active',
                'student_disable',
            ])
            ->where('id', $studentId)
            ->where('is_active', true)
            ->where('student_disable', false)
            ->first();
    }

    private function getClassDetails(int $studentClassId, int $classCategoryFeeId): ?array
    {
        $class = StudentClass::query()
            ->with([
                'grade:id,grade_name',
                'subject:id,subject_name',
                'teacher:id,initials',
            ])
            ->where('id', $studentClassId)
            ->where('is_active', true)
            ->first();

        if (! $class) {
            return null;
        }

        $fee = ClassCategoryFee::query()
            ->with('category:id,category_name')
            ->where('id', $classCategoryFeeId)
            ->where('student_class_id', $studentClassId)
            ->where('is_active', true)
            ->first();

        if (! $fee) {
            return null;
        }

        return [
            'class_name' => $class->class_name,
            'grade' => $class->grade?->grade_name,
            'subject' => $class->subject?->subject_name,
            'teacher' => $class->teacher?->initials,
            'category_name' => $fee->category?->category_name,
            'default_fee' => $fee->fee,
        ];
    }

    private function getStudentEnrollment(
        int $studentId,
        int $studentClassId,
        int $classCategoryFeeId
    ): ?StudentClassEnrollment {
        return StudentClassEnrollment::query()
            ->with([
                'classCategoryFee:id,class_category_id,fee',
                'classCategoryFee.category:id,category_name',
                'payments' => function ($query) {
                    $query->where('status', 'completed')
                        ->orderByDesc('paid_at');
                },
            ])
            ->where('student_id', $studentId)
            ->where('student_class_id', $studentClassId)
            ->where('class_category_fee_id', $classCategoryFeeId)
            ->where('is_active', true)
            ->first();
    }

    private function getLastPayment(StudentClassEnrollment $enrollment): ?array
    {
        $payment = $enrollment->payments->first();

        if (! $payment) {
            return null;
        }

        return [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'discount_amount' => $payment->discount_amount,
            'payment_month' => $payment->payment_month?->format('Y-m'),
            'payment_method' => $payment->payment_method,
            'receipt_number' => $payment->receipt_number,
            'paid_at' => $payment->paid_at?->format('Y-m-d H:i:s'),
            'status' => $payment->status,
        ];
    }

    private function getTuteSummary(int $studentId, int $enrollmentId): ?array
    {
        try {
            $now = Carbon::now();

            $tute = StudentTute::query()
                ->where('student_id', $studentId)
                ->where('student_class_enrollment_id', $enrollmentId)
                ->where('is_issued', true)
                ->whereYear('issued_at', $now->year)
                ->whereMonth('issued_at', $now->month)
                ->latest('issued_at')
                ->first();

            if (! $tute) {
                return null;
            }

            return [
                'month' => $now->format('Y-m'),
                'is_issued' => true,
                'issued_at' => $tute->issued_at,
                'note' => $tute->note,
            ];
        } catch (Throwable $e) {
            Log::error('Tute summary failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return null;
        }
    }

    private function getAttendanceSummary(
        int $studentId,
        int $studentClassId,
        ?int $enrollmentId = null,
        ?int $classCategoryFeeId = null
    ): array {
        $now = Carbon::now();

        $scheduleQuery = ClassSchedule::query()
            ->where('student_class_id', $studentClassId)
            ->whereYear('class_date', $now->year)
            ->whereMonth('class_date', $now->month)
            ->whereIn('status', ['ongoing', 'completed'])
            ->where('is_active', true);

        if ($classCategoryFeeId) {
            $scheduleQuery->whereHas('pattern', function ($query) use ($classCategoryFeeId) {
                $query->where('class_category_fee_id', $classCategoryFeeId);
            });
        }

        $scheduleIds = $scheduleQuery->pluck('id');

        $totalClasses = $scheduleIds->count();

        $attendedClasses = ($enrollmentId && $totalClasses > 0)
            ? StudentAttendance::query()
            ->where('student_id', $studentId)
            ->where('student_class_enrollment_id', $enrollmentId)
            ->whereIn('class_schedule_id', $scheduleIds)
            ->distinct('class_schedule_id')
            ->count('class_schedule_id')
            : 0;

        return [
            'month' => $now->format('Y-m'),
            'attended_classes' => $attendedClasses,
            'total_classes' => $totalClasses,
            'absent_classes' => max($totalClasses - $attendedClasses, 0),
            'attendance_percentage' => $totalClasses > 0
                ? round(($attendedClasses / $totalClasses) * 100)
                : 0,
        ];
    }

    private function successResponse(array $data): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Student attendance details loaded successfully',
            'data' => $data,
        ], 200);
    }

    private function errorResponse(string $message, int $statusCode): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => [],
        ], $statusCode);
    }
}
