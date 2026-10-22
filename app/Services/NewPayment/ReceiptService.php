<?php

namespace App\Services\NewPayment;

use App\Models\Payment;
use Illuminate\Support\Collection;

class ReceiptService
{
    protected PaymentDataBuilder $paymentDataBuilder;

    public function __construct(
        PaymentDataBuilder $paymentDataBuilder
    ) {
        $this->paymentDataBuilder = $paymentDataBuilder;
    }

    /**
     * Build data for a single payment receipt.
     */
    public function single(Payment $payment): array
    {
        $payment->load([
            'student',
            'enrollment.studentClass.teacher',
            'enrollment.studentClass.subject',
            'enrollment.studentClass.grade',
            'enrollment.classCategoryFee.category',
            'splitSnapshot',
        ]);

        $paymentData = $this->paymentDataBuilder->payment($payment);

        $row = $this->flattenRow($payment, $paymentData);

        return [
            'payment' => $payment,

            'student' => [
                'custom_id'    => $payment->student?->custom_id,
                'initial_name' => $payment->student?->initial_name,
                'full_name'    => $payment->student?->full_name,
            ],

            'rows' => [$row],

            'payment_date' => $payment->paid_at
                ? $payment->paid_at->format('Y-m-d')
                : null,

            'payment_time' => $payment->paid_at
                ? $payment->paid_at->format('H:i:s')
                : null,

            'payment_method' => $payment->payment_method,

            /*
            |--------------------------------------------------------------------------
            | Single Summary
            |--------------------------------------------------------------------------
            |
            | Only total class fee is required.
            |
            */

            'summary' => [
                'total_class_fee' => $row['class_fee'],
            ],
        ];
    }

    /**
     * Build data for bulk payment receipt.
     */
    public function bulk(Collection $payments): array
    {
        $payments->load([
            'student',
            'enrollment.studentClass.teacher',
            'enrollment.studentClass.subject',
            'enrollment.studentClass.grade',
            'enrollment.classCategoryFee.category',
            'splitSnapshot',
        ]);

        $rows = [];

        $totalClassFee = 0;

        $firstStudent = null;
        $firstPayment = null;

        foreach ($payments as $payment) {

            $paymentData = $this->paymentDataBuilder->payment($payment);

            $row = $this->flattenRow($payment, $paymentData);

            $rows[] = $row;

            /*
            |--------------------------------------------------------------------------
            | Add Class Fee to Bulk Total
            |--------------------------------------------------------------------------
            */

            $totalClassFee += (float) $row['class_fee'];

            if (!$firstStudent) {
                $firstStudent = $payment->student;
            }

            if (!$firstPayment) {
                $firstPayment = $payment;
            }
        }

        $studentData = $firstStudent ? [
            'custom_id'    => $firstStudent->custom_id,
            'initial_name' => $firstStudent->initial_name,
            'full_name'    => $firstStudent->full_name,
        ] : [];

        $paidAt = $firstPayment?->paid_at;

        return [
            'student' => $studentData,

            'payment_date' => $paidAt
                ? $paidAt->format('Y-m-d')
                : null,

            'payment_time' => $paidAt
                ? $paidAt->format('H:i:s')
                : null,

            /*
            |--------------------------------------------------------------------------
            | Payment Method
            |--------------------------------------------------------------------------
            */

            'payment_method' => $firstPayment?->payment_method,

            'rows' => $rows,

            /*
            |--------------------------------------------------------------------------
            | Bulk Summary
            |--------------------------------------------------------------------------
            |
            | Only Total Fee.
            |
            */

            'summary' => [
                'total_class_fee' => round($totalClassFee, 2),
            ],
        ];
    }

    /**
     * Flatten one payment row for JS receipt consumption.
     *
     * Available fields:
     *
     * row.class_name
     * row.category
     * row.grade
     * row.teacher
     * row.payment_month
     * row.receipt_number
     * row.class_fee
     * row.payment_status
     */
    protected function flattenRow(
        Payment $payment,
        array $paymentData
    ): array {

        $fee      = $paymentData['fee'] ?? [];
        $class    = $paymentData['class'] ?? [];
        $category = $paymentData['category'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Class Fee
        |--------------------------------------------------------------------------
        */

        $classFeeCandidates = [
            $fee['class_fee'] ?? null,
            $fee['final_fee'] ?? null,
            $payment->enrollment?->final_fee,
            $payment->amount,
        ];

        $classFee = 0.0;

        foreach ($classFeeCandidates as $candidate) {

            $value = (float) ($candidate ?? 0);

            if ($value > 0) {
                $classFee = $value;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Grade
        |--------------------------------------------------------------------------
        */

        $gradeCandidates = [
            $class['grade'] ?? null,
            $payment->enrollment?->studentClass?->grade?->grade_name,
            $payment->student?->grade?->grade_name,
        ];

        $grade = null;

        foreach ($gradeCandidates as $candidate) {

            if (!empty($candidate)) {
                $grade = $candidate;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Teacher
        |--------------------------------------------------------------------------
        */

        $teacherCandidates = [
            $class['teacher_initials'] ?? null,
            $payment->enrollment?->studentClass?->teacher?->initials,
            $payment->enrollment?->studentClass?->teacher?->initial_name,
        ];

        $teacher = null;

        foreach ($teacherCandidates as $candidate) {

            if (!empty($candidate)) {
                $teacher = $candidate;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Class Name
        |--------------------------------------------------------------------------
        */

        $className = $class['class_name']
            ?? $payment->enrollment?->studentClass?->class_name
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        $categoryName = $category['name']
            ?? $payment->enrollment?->classCategoryFee?->category?->category_name
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Payment Month
        |--------------------------------------------------------------------------
        */

        $paymentMonth = $paymentData['payment_month']
            ?? (
                $payment->payment_month
                    ? $payment->payment_month->format('Y-m-d')
                    : null
            );

        /*
        |--------------------------------------------------------------------------
        | Payment Status
        |--------------------------------------------------------------------------
        */

        $paymentStatus = $fee['payment_status']
            ?? $payment->status;

        /*
        |--------------------------------------------------------------------------
        | Final Receipt Row
        |--------------------------------------------------------------------------
        |
        | Only:
        |
        | - Class Fee
        | - Payment Status
        |
        */

        return [
            'class_name' => $className,

            'category' => $categoryName,

            'grade' => $grade,

            'teacher' => $teacher,

            'payment_month' => $paymentMonth,

            'receipt_number' => $paymentData['receipt_number']
                ?? $payment->receipt_number,

            'class_fee' => round($classFee, 2),

            'payment_status' => $paymentStatus,
        ];
    }
}