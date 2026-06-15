<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Services\StudentQRService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class StudentPaymentReadController extends Controller
{
    public function read(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string', 'max:150'],
        ]);

        try {
            $qrResult = StudentQRService::read($validated['qr_code']);

            if (! $qrResult['success']) {
                return $this->errorResponse(
                    $qrResult['message'],
                    $qrResult['status_code']
                );
            }

            $student = $this->getActiveStudent((int) $qrResult['student_id']);

            if (! $student) {
                return $this->errorResponse(
                    'Student not found or inactive',
                    404
                );
            }

            return $this->successResponse([
                'student' => [
                    'id' => $student->id,

                    'custom_id' => $student->permanent_qr_active
                        ? $student->custom_id
                        : $student->temporary_qr_code,
                    'initial_name' => $student->initial_name,
                    'mobile' => $student->mobile,
                    'guardian_mobile' => $student->guardian_mobile,
                    'img_url' => $student->img_url,
                    'qr_type' => $qrResult['qr_type'],
                ],

                'classes' => $this->getStudentClassPaymentDetails($student->id),
            ]);
        } catch (Throwable $e) {
            Log::error('Payment read failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->errorResponse(
                'Something went wrong while reading payment details',
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

    private function getStudentClassPaymentDetails(int $studentId)
    {
        return StudentClassEnrollment::query()
            ->with([
                'studentClass:id,class_name,teacher_id,subject_id,grade_id',
                'studentClass.teacher:id,initials',
                'studentClass.grade:id,grade_name',
                'studentClass.subject:id,subject_name',

                'classCategoryFee:id,class_category_id,fee',
                'classCategoryFee.category:id,category_name',

                'payments' => function ($query) {
                    $query->select([
                        'id',
                        'student_class_enrollment_id',
                        'amount',
                        'discount_amount',
                        'payment_month',
                        'payment_method',
                        'receipt_number',
                        'paid_at',
                        'status',
                    ])
                        ->where('status', 'completed')
                        ->orderByDesc('paid_at');
                },
            ])
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->get()
            ->map(function ($enrollment) use ($studentId) {
                $studentClass = $enrollment->studentClass;
                $lastPayment = $enrollment->payments->first();

                $classCategoryFeeId = $enrollment->classCategoryFee?->id
                    ? (int) $enrollment->classCategoryFee->id
                    : null;

                return [
                    'enrollment_id' => $enrollment->id,

                    'student_class_id' => $enrollment->student_class_id,

                    'class_category_id' => $classCategoryFeeId,

                    'class_name' => $studentClass?->class_name,

                    'category_name' => $enrollment->classCategoryFee?->category?->category_name,

                    'teacher_initials' => $studentClass?->teacher?->initials,

                    'grade' => $studentClass?->grade?->grade_name,

                    'subject' => $studentClass?->subject?->subject_name,

                    'is_free_card' => (bool) $enrollment->is_free_card,

                    'final_fee' => $enrollment->final_fee,

                    'last_payment' => $this->formatLastPayment($lastPayment),

                    'attendance' => $this->getCurrentMonthAttendanceSummary(
                        studentId: $studentId,
                        studentClassId: (int) $enrollment->student_class_id,
                        enrollmentId: (int) $enrollment->id,
                        classCategoryFeeId: $classCategoryFeeId
                    ),
                ];
            })
            ->values();
    }

    private function formatLastPayment($payment): ?array
    {
        if (! $payment) {
            return null;
        }

        return [
            'payment_id' => $payment->id,

            'amount' => $payment->amount,

            'discount_amount' => $payment->discount_amount,

            'payment_month' => $payment->payment_month
                ? Carbon::parse($payment->payment_month)->format('Y-m')
                : null,

            'payment_method' => $payment->payment_method,

            'receipt_number' => $payment->receipt_number,

            'paid_at' => $payment->paid_at
                ? Carbon::parse($payment->paid_at)->format('Y-m-d H:i:s')
                : null,

            'status' => $payment->status,
        ];
    }

    private function getCurrentMonthAttendanceSummary(
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

            'message' => 'Student payment details loaded successfully',

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
