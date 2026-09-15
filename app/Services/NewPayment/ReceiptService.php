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

        $summary = [
            'total_class_fee' => $row['class_fee'],
            'total_final_fee' => $row['total_fee'],
            'total_discount'  => $row['discount_amount'],
            'total_paid'      => $row['paid_amount'],
            'total_balance'   => $row['balance'],
        ];

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

            'summary' => $summary,
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
        $totalFinalFee = 0;
        $totalDiscount = 0;
        $totalPaid = 0;
        $totalBalance = 0;

        $firstStudent = null;
        $firstPayment = null;

        foreach ($payments as $payment) {

            $paymentData = $this->paymentDataBuilder->payment($payment);

            $row = $this->flattenRow($payment, $paymentData);

            $rows[] = $row;

            $totalClassFee += (float) $row['class_fee'];
            $totalFinalFee += (float) $row['total_fee'];
            $totalDiscount += (float) $row['discount_amount'];
            $totalPaid      += (float) $row['paid_amount'];
            $totalBalance   += (float) $row['balance'];

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

            'payment_method' => $firstPayment?->payment_method,

            'rows' => $rows,

            'summary' => [
                'total_class_fee' => round($totalClassFee, 2),
                'total_final_fee' => round($totalFinalFee, 2),
                'total_discount'  => round($totalDiscount, 2),
                'total_paid'      => round($totalPaid, 2),
                'total_balance'   => round($totalBalance, 2),
            ],
        ];
    }

    /**
     * Flatten one payment row for JS receipt consumption.
     *
     * Returns flat fields so JS can access directly:
     *   row.class_name
     *   row.category
     *   row.grade
     *   row.teacher
     *   row.payment_month
     *   row.receipt_number
     *   row.class_fee
     *   row.total_fee
     *   row.paid_amount
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
        |
        | Resolve from multiple sources (skip zero values).
        |
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

        $totalFee = (float) (
            ($fee['total_fee'] ?? 0) > 0
            ? $fee['total_fee']
            : $classFee
        );

        $discountAmount = (float) (
            $fee['discount_amount']
            ?? $payment->discount_amount
            ?? 0
        );

        $paidAmount = (float) (
            $fee['paid_amount']
            ?? $payment->amount
            ?? 0
        );

        $balance = (float) (
            $fee['balance']
            ?? max($totalFee - $discountAmount - $paidAmount, 0)
        );

        /*
        |--------------------------------------------------------------------------
        | Grade (multiple fallbacks)
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
        | Teacher (multiple fallbacks)
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
        | Class name
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
            ?? ($payment->payment_month
                ? $payment->payment_month->format('Y-m-d')
                : null);

        return [
            // Flat fields for JS
            'class_name'      => $className,
            'category'        => $categoryName,
            'grade'           => $grade,
            'teacher'         => $teacher,
            'payment_month'   => $paymentMonth,
            'receipt_number'  => $paymentData['receipt_number']
                ?? $payment->receipt_number,

            // Fee fields
            'class_fee'       => round($classFee, 2),
            'total_fee'       => round($totalFee, 2),
            'final_fee'       => round($classFee, 2),
            'discount_amount' => round($discountAmount, 2),
            'paid_amount'     => round($paidAmount, 2),
            'balance'         => round($balance, 2),

            'payment_status'  => $fee['payment_status']
                ?? $payment->status,
        ];
    }
}
