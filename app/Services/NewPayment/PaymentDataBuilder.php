<?php

namespace App\Services\NewPayment;

use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Carbon\Carbon;

class PaymentDataBuilder
{
    /**
     * Build student information.
     */
    public function student(Student $student): array
    {
        return [
            'id' => $student->id,

            'custom_id' => $student->custom_id,

            'full_name' => $student->full_name,

            'initial_name' => $student->initial_name,

            'image_url' => $student->img_url
                ? asset('storage/' . $student->img_url)
                : null,

            'mobile' => $student->mobile,

            'guardian_mobile' => $student->guardian_mobile,

            'guardian_name' => $student->guardian_name,

            'grade' => $student->grade?->grade_name,
        ];
    }

    /**
     * Build all active student classes.
     */
    public function classes($enrollments): array
    {
        return $enrollments
            ->map(function (StudentClassEnrollment $enrollment) {
                return $this->class($enrollment);
            })
            ->values()
            ->toArray();
    }

    /**
     * Build one class/enrollment.
     */
    public function class(
        StudentClassEnrollment $enrollment
    ): array {
        $studentClass = $enrollment->studentClass;

        $categoryFee = $enrollment->classCategoryFee;

        $category = $categoryFee?->category;

        /*
        |--------------------------------------------------------------------------
        | Fee
        |--------------------------------------------------------------------------
        |
        | Hall fee has been completely removed.
        | The enrollment final_fee is now the total class fee.
        |
        */

        $classFee = (float) $enrollment->final_fee;

        $totalFee = $classFee;

        return [
            /*
            |--------------------------------------------------------------------------
            | Enrollment
            |--------------------------------------------------------------------------
            */

            'enrollment_id' => $enrollment->id,

            /*
            |--------------------------------------------------------------------------
            | Class Information
            |--------------------------------------------------------------------------
            */

            'class_name' => $studentClass?->class_name,

            'subject' => $studentClass?->subject?->subject_name,

            /*
            |--------------------------------------------------------------------------
            | Class-wise Grade
            |--------------------------------------------------------------------------
            */

            'grade' => $studentClass?->grade?->grade_name,

            /*
            |--------------------------------------------------------------------------
            | Teacher
            |--------------------------------------------------------------------------
            */

            'teacher_initials' => $this->teacherInitials(
                $studentClass?->teacher
            ),

            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */

            'category_name' => $category?->category_name,

            'category_id' => $category?->id,

            'fee_id' => $categoryFee?->id,

            /*
            |--------------------------------------------------------------------------
            | Fee
            |--------------------------------------------------------------------------
            */

            'class_fee' => $classFee,

            'total_fee' => $totalFee,

            // Keep old field for existing frontend compatibility
            'final_fee' => $classFee,

            'balance' => (float) $enrollment->balance,

            /*
            |--------------------------------------------------------------------------
            | Payment Status
            |--------------------------------------------------------------------------
            */

            'payment_status' => $enrollment->payment_status,

            /*
            |--------------------------------------------------------------------------
            | Attendance
            |--------------------------------------------------------------------------
            */

            'attendance' => $this->attendance($enrollment),

            /*
            |--------------------------------------------------------------------------
            | Last Payment
            |--------------------------------------------------------------------------
            */

            'last_payment' => $this->lastPayment($enrollment),
        ];
    }

    /**
     * Build payment information.
     *
     * Used by:
     * - Single payment response
     * - Bulk payment response
     * - ReceiptService
     */
    public function payment(Payment $payment): array
{
    $payment->loadMissing([
        'student.grade',
        'enrollment.studentClass.teacher',
        'enrollment.studentClass.subject',
        'enrollment.studentClass.grade',
        'enrollment.classCategoryFee.category',
        'splitSnapshot',
    ]);

    $student = $payment->student;

    $enrollment = $payment->enrollment;

    if (!$enrollment) {
        throw new \RuntimeException(
            'Payment enrollment not found.'
        );
    }

    $studentClass = $enrollment->studentClass;

    $categoryFee = $enrollment->classCategoryFee;

    $category = $categoryFee?->category;

    /*
    |--------------------------------------------------------------------------
    | Class Fee
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | payment_split_snapshots table does NOT have a class_fee column.
    | It only stores teacher/organizer/institution split amounts.
    |
    | So we resolve class fee from the enrollment's final_fee.
    |
    | Fallback:
    |   1. enrollment.final_fee
    |   2. payment.amount
    |
    */

    $classFee = (float) ($enrollment->final_fee ?? 0);

    if ($classFee <= 0) {
        $classFee = (float) ($payment->amount ?? 0);
    }

    /*
     * Hall fee removed.
     *
     * Total fee = class fee only.
     */
    $totalFee = $classFee;

    $paidAmount = (float) $payment->amount;

    /*
    |--------------------------------------------------------------------------
    | Balance
    |--------------------------------------------------------------------------
    */

    $balance = max($totalFee - $paidAmount, 0);

    return [
        'payment_id' => $payment->id,

        'receipt_number' => $payment->receipt_number,

        'payment_month' => Carbon::parse(
            $payment->payment_month
        )->format('Y-m-d'),

        'payment_method' => $payment->payment_method,

        'mark_method' => $payment->mark_method,

        'paid_at' => $payment->paid_at
            ? $payment->paid_at->format('Y-m-d H:i:s')
            : null,

        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        'student' => [
            'id' => $student?->id,
            'custom_id' => $student?->custom_id,
            'name' => $student?->initial_name ?: $student?->full_name,
            'full_name' => $student?->full_name,
            'initial_name' => $student?->initial_name,
            'mobile' => $student?->mobile,
            'guardian_mobile' => $student?->guardian_mobile,
            'guardian_name' => $student?->guardian_name,
            'grade' => $student?->grade?->grade_name,
            'image_url' => $student?->img_url
                ? asset('storage/' . $student->img_url)
                : null,
        ],

        /*
        |--------------------------------------------------------------------------
        | Class
        |--------------------------------------------------------------------------
        */

        'class' => [
            'enrollment_id' => $enrollment->id,
            'class_name' => $studentClass?->class_name,
            'subject' => $studentClass?->subject?->subject_name,
            'grade' => $studentClass?->grade?->grade_name,
            'teacher_initials' => $this->teacherInitials(
                $studentClass?->teacher
            ),
        ],

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        'category' => [
            'id' => $category?->id,
            'name' => $category?->category_name,
            'fee_id' => $categoryFee?->id,
        ],

        /*
        |--------------------------------------------------------------------------
        | Fee
        |--------------------------------------------------------------------------
        */

        'fee' => [
            'class_fee' => $classFee,
            'total_fee' => $totalFee,
            'final_fee' => $classFee,
            'discount_amount' => (float) $payment->discount_amount,
            'paid_amount' => $paidAmount,
            'balance' => $balance,
            'payment_status' => $paidAmount >= $totalFee
                ? 'paid'
                : 'unpaid',
        ],
    ];
}

    /**
     * Get teacher initials.
     */
    private function teacherInitials($teacher): ?string
    {
        if (!$teacher) {
            return null;
        }

        return $teacher->initials
            ?? $teacher->initial_name
            ?? null;
    }

    /**
     * Attendance.
     */
    private function attendance(
        StudentClassEnrollment $enrollment
    ): array {
        return [
            'class_days' => 0,
            'attended_days' => 0,
        ];
    }

    /**
     * Get latest completed payment for this enrollment.
     */
    private function lastPayment(
        StudentClassEnrollment $enrollment
    ): ?array {
        $payment = $enrollment
            ->payments()
            ->where('status', 'completed')
            ->orderByDesc('payment_month')
            ->orderByDesc('paid_at')
            ->first();

        if (!$payment) {
            return null;
        }

        return [
            'id' => $payment->id,

            'receipt_number' => $payment->receipt_number,

            'amount' => (float) $payment->amount,

            'payment_month' => $payment->payment_month
                ? $payment->payment_month->format('Y-m-d')
                : null,

            'payment_month_name' => $payment->payment_month
                ? $payment->payment_month->format('F Y')
                : null,

            'paid_at' => $payment->paid_at
                ? $payment->paid_at->format('Y-m-d H:i:s')
                : null,

            'payment_method' => $payment->payment_method,
        ];
    }
}
