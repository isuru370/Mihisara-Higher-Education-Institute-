<?php

namespace App\Services\NewPayment;

use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Services\ReceiptNumberService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class PaymentMarker
{
    /**
     * Allowed attendance/payment marking methods.
     */
    private const ALLOWED_MARK_METHODS = [
        'qr_mobile',
        'qr_web',
        'manual_mobile',
        'manual_web',
    ];

    /**
     * Allowed payment methods.
     */
    private const ALLOWED_PAYMENT_METHODS = [
        'cash',
        'card',
        'bank_transfer',
        'online',
        'cheque',
        'other',
    ];

    /**
     * Create one completed payment.
     *
     * Payment rules:
     *
     * 1. Student must own the enrollment.
     * 2. Payment month is required.
     * 3. Any month can be selected.
     * 4. Same enrollment + same month cannot be paid twice.
     * 5. Payment amount must cover the payable amount.
     * 6. Every payment gets its own receipt number.
     */
    public function mark(
        Student $student,
        StudentClassEnrollment $enrollment,
        array $data
    ): Payment {
        /*
        |--------------------------------------------------------------------------
        | 1. Verify Enrollment Belongs To Student
        |--------------------------------------------------------------------------
        */

        if ((int) $enrollment->student_id !== (int) $student->id) {
            throw new \RuntimeException(
                'The selected class enrollment does not belong to this student.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Mark Method
        |--------------------------------------------------------------------------
        */

        $markMethod = $data['mark_method'] ?? null;

        if (!in_array($markMethod, self::ALLOWED_MARK_METHODS, true)) {
            throw new \InvalidArgumentException(
                'Invalid payment mark method.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Payment Method
        |--------------------------------------------------------------------------
        */

        $paymentMethod = $data['payment_method'] ?? 'cash';

        if (!in_array(
            $paymentMethod,
            self::ALLOWED_PAYMENT_METHODS,
            true
        )) {
            throw new \InvalidArgumentException(
                'Invalid payment method.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Payment Month
        |--------------------------------------------------------------------------
        |
        | User can select ANY month.
        |
        | Example:
        |
        | August 2026
        | September 2026
        | October 2026
        |
        | No current-month restriction.
        |
        */

        if (
            !isset($data['payment_month']) ||
            trim((string) $data['payment_month']) === ''
        ) {
            throw new \InvalidArgumentException(
                'Payment month is required.'
            );
        }

        try {
            $paymentMonth = Carbon::parse(
                $data['payment_month']
            )->startOfMonth();
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException(
                'Invalid payment month.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Payment Amount
        |--------------------------------------------------------------------------
        */

        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw new \InvalidArgumentException(
                'Payment amount must be greater than zero.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Discount Amount
        |--------------------------------------------------------------------------
        */

        $discountAmount = (float) (
            $data['discount_amount'] ?? 0
        );

        if ($discountAmount < 0) {
            throw new \InvalidArgumentException(
                'Discount amount cannot be negative.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Final Fee
        |--------------------------------------------------------------------------
        */

        $finalFee = (float) $enrollment->final_fee;

        if ($finalFee < 0) {
            throw new \InvalidArgumentException(
                'Invalid enrollment fee.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Validate Discount
        |--------------------------------------------------------------------------
        */

        if ($discountAmount > $finalFee) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Discount amount cannot exceed the final fee of Rs. %.2f.',
                    $finalFee
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Calculate Payable Amount
        |--------------------------------------------------------------------------
        */

        $payableAmount = max(
            $finalFee - $discountAmount,
            0
        );

        /*
        |--------------------------------------------------------------------------
        | 10. Validate Payment Amount
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Final Fee       = 2,500
        | Discount        =   500
        | Payable         = 2,000
        |
        | Payment must be >= 2,000.
        |
        */

        if ($amount < $payableAmount) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Payment amount must be at least Rs. %.2f.',
                    $payableAmount
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 11. Duplicate Payment Check
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | This does NOT check whether the class is already paid generally.
        |
        | It checks ONLY:
        |
        | Same enrollment
        | +
        | Same payment month
        |
        | Therefore:
        |
        | A → August  ✅
        | A → September ✅
        | A → October  ✅
        |
        | But:
        |
        | A → August
        | A → August  ❌
        |
        */

        $alreadyPaid = Payment::query()
            ->where(
                'student_class_enrollment_id',
                $enrollment->id
            )
            ->whereDate(
                'payment_month',
                $paymentMonth
            )
            ->where('status', 'completed')
            ->exists();

        if ($alreadyPaid) {
            throw new \RuntimeException(
                'Payment already completed for this class and month.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 12. Paid At
        |--------------------------------------------------------------------------
        |
        | Single payment:
        |     now()
        |
        | Bulk payment:
        |     PaymentService passes the SAME Carbon instance
        |     to every payment.
        |
        */

        $paidAt = $data['paid_at'] ?? now();

        if (!$paidAt instanceof Carbon) {
            try {
                $paidAt = Carbon::parse($paidAt);
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException(
                    'Invalid payment date/time.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 13. Generate Unique Receipt Number
        |--------------------------------------------------------------------------
        |
        | Every Payment gets a separate receipt number.
        |
        | Example:
        |
        | A → REC-000125
        | B → REC-000126
        | C → REC-000127
        |
        | Bulk receipt can display all three.
        |
        */

        $receiptNumber = ReceiptNumberService::generate();

        /*
        |--------------------------------------------------------------------------
        | 14. Create Payment
        |--------------------------------------------------------------------------
        */

        try {
            return Payment::create([
                'student_id' => $student->id,

                'student_class_enrollment_id' => $enrollment->id,

                'user_id' => auth()->id(),

                'mark_method' => $markMethod,

                'amount' => $amount,

                'discount_amount' => $discountAmount,

                'paid_at' => $paidAt,

                'payment_month' => $paymentMonth,

                'payment_method' => $paymentMethod,

                'status' => 'completed',

                'receipt_number' => $receiptNumber,

                'reference_number' => $data['reference_number'] ?? null,

                'is_synced' => true,

                'note' => $data['note'] ?? null,
            ]);
        } catch (QueryException $e) {

            /*
            |--------------------------------------------------------------------------
            | 15. Database-Level Duplicate Protection
            |--------------------------------------------------------------------------
            |
            | Even if two devices submit the same payment at exactly
            | the same time, the database UNIQUE constraint protects us.
            |
            */

            $message = strtolower($e->getMessage());

            if (
                str_contains(
                    $message,
                    'unique_enrollment_month_payment'
                )
            ) {
                throw new \RuntimeException(
                    'Payment already completed for this class and month.'
                );
            }

            throw $e;
        }
    }
}