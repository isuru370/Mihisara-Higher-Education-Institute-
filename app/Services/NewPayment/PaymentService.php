<?php

namespace App\Services\NewPayment;

use App\Models\Payment;
use App\Services\Notification\PaymentNotificationService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected PaymentStudentFinder $studentFinder,
        protected PaymentDataBuilder $dataBuilder,
        protected PaymentMarker $paymentMarker,
        protected PaymentNotificationService $notificationService,
        protected ReceiptService $receiptService
    ) {
    }

    /**
     * Read student payment information.
     *
     * Used by:
     * - Web
     * - Mobile
     *
     * Returns:
     * - Student details
     * - Active classes
     * - Class-wise fee
     * - Class-wise payment status
     * - Class-wise last payment
     */
    public function read(string $code): array
    {
        $student = $this->studentFinder->findStudent($code);

        if (!$student) {
            throw new \RuntimeException(
                'Student not found.'
            );
        }

        $enrollments = $this->studentFinder
            ->getActiveEnrollments($student);

        return [
            'student' => $this->dataBuilder
                ->student($student),

            'classes' => $this->dataBuilder
                ->classes($enrollments),
        ];
    }

    /**
     * Store ONE payment.
     *
     * Flow:
     *
     * Controller
     *     ↓
     * PaymentService
     *     ↓
     * StudentFinder
     *     ↓
     * PaymentMarker
     *     ↓
     * Payment
     *     ↓
     * Notification
     *     ↓
     * Receipt
     */
    public function pay(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | Create Payment
        |--------------------------------------------------------------------------
        */

        $payment = DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Find Student
            |--------------------------------------------------------------------------
            */

            $student = $this->studentFinder
                ->findStudent($data['code']);

            if (!$student) {
                throw new \RuntimeException(
                    'Student not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Find Enrollment
            |--------------------------------------------------------------------------
            */

            $enrollment = $this->studentFinder
                ->findEnrollment(
                    $student,
                    (int) $data['enrollment_id']
                );

            if (!$enrollment) {
                throw new \RuntimeException(
                    'Student class enrollment not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Payment
            |--------------------------------------------------------------------------
            */

            return $this->paymentMarker->mark(
                $student,
                $enrollment,
                $data
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Transaction Committed
        |--------------------------------------------------------------------------
        |
        | Only after successful DB commit:
        |
        | FCM → active student devices
        | SMS → guardian
        |
        */

        $this->queueNotification($payment);

        /*
        |--------------------------------------------------------------------------
        | Payment Response
        |--------------------------------------------------------------------------
        */

        $paymentData = $this->dataBuilder
            ->payment($payment);

        /*
        |--------------------------------------------------------------------------
        | Single Receipt
        |--------------------------------------------------------------------------
        */

        $receipt = $this->receiptService
            ->single($payment);

        return [
            'payment' => $paymentData,

            'receipt' => $receipt,
        ];
    }

    /**
     * Store MULTIPLE payments in one bulk operation.
     *
     * IMPORTANT REQUIREMENTS:
     *
     * 1. All payments must belong to ONE student.
     * 2. All payments must use ONE payment month.
     * 3. All payments get the SAME paid_at.
     * 4. Each Payment gets its OWN receipt number.
     * 5. One combined 58mm receipt is generated.
     */
    public function bulkPay(array $payments): array
    {
        /*
        |--------------------------------------------------------------------------
        | Empty Validation
        |--------------------------------------------------------------------------
        */

        if (empty($payments)) {
            throw new \InvalidArgumentException(
                'No payments provided.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Student + Month Before Creating Payments
        |--------------------------------------------------------------------------
        |
        | This is important because the frontend should send:
        |
        | Student: ST001
        |
        | Mathematics
        | Physics
        | Chemistry
        |
        | Payment Month: August 2026
        |
        */

        $firstCode = trim(
            (string) ($payments[0]['code'] ?? '')
        );

        if ($firstCode === '') {
            throw new \InvalidArgumentException(
                'Student code is required.'
            );
        }

        $firstStudent = $this->studentFinder
            ->findStudent($firstCode);

        if (!$firstStudent) {
            throw new \RuntimeException(
                'Student not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | First Payment Month
        |--------------------------------------------------------------------------
        */

        $firstMonth = trim(
            (string) ($payments[0]['payment_month'] ?? '')
        );

        if ($firstMonth === '') {
            throw new \InvalidArgumentException(
                'Payment month is required.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | One Timestamp For Entire Bulk Operation
        |--------------------------------------------------------------------------
        */

        $paidAt = now();

        /*
        |--------------------------------------------------------------------------
        | Create All Payments In ONE Transaction
        |--------------------------------------------------------------------------
        */

        $createdPayments = DB::transaction(
            function () use (
                $payments,
                $paidAt,
                $firstStudent,
                $firstMonth
            ) {

                $results = [];

                foreach ($payments as $data) {

                    /*
                    |--------------------------------------------------------------------------
                    | Validate Student Code
                    |--------------------------------------------------------------------------
                    */

                    $code = trim(
                        (string) ($data['code'] ?? '')
                    );

                    if ($code === '') {
                        throw new \InvalidArgumentException(
                            'Student code is required.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Every Bulk Payment Must Belong To Same Student
                    |--------------------------------------------------------------------------
                    */

                    $student = $this->studentFinder
                        ->findStudent($code);

                    if (!$student) {
                        throw new \RuntimeException(
                            'Student not found.'
                        );
                    }

                    if (
                        (int) $student->id !==
                        (int) $firstStudent->id
                    ) {
                        throw new \InvalidArgumentException(
                            'All bulk payments must belong to the same student.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Every Bulk Payment Must Use Same Month
                    |--------------------------------------------------------------------------
                    */

                    $paymentMonth = trim(
                        (string) ($data['payment_month'] ?? '')
                    );

                    if ($paymentMonth === '') {
                        throw new \InvalidArgumentException(
                            'Payment month is required.'
                        );
                    }

                    /*
                    | Compare month only.
                    |
                    | Example:
                    |
                    | 2026-08-01
                    | 2026-08-15
                    |
                    | Both represent August.
                    |
                    */

                    try {
                        $firstMonthDate = \Illuminate\Support\Carbon::parse(
                            $firstMonth
                        )->startOfMonth();

                        $currentMonthDate = \Illuminate\Support\Carbon::parse(
                            $paymentMonth
                        )->startOfMonth();
                    } catch (\Throwable $e) {
                        throw new \InvalidArgumentException(
                            'Invalid payment month.'
                        );
                    }

                    if (
                        !$firstMonthDate->equalTo(
                            $currentMonthDate
                        )
                    ) {
                        throw new \InvalidArgumentException(
                            'All bulk payments must use the same payment month.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Force Same Paid At
                    |--------------------------------------------------------------------------
                    */

                    $data['paid_at'] = $paidAt;

                    /*
                    |--------------------------------------------------------------------------
                    | Find Enrollment
                    |--------------------------------------------------------------------------
                    */

                    $enrollment = $this->studentFinder
                        ->findEnrollment(
                            $student,
                            (int) $data['enrollment_id']
                        );

                    if (!$enrollment) {
                        throw new \RuntimeException(
                            'Student class enrollment not found.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create Payment
                    |--------------------------------------------------------------------------
                    */

                    $payment = $this->paymentMarker
                        ->mark(
                            $student,
                            $enrollment,
                            $data
                        );

                    $results[] = $payment;
                }

                return $results;
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Transaction Successfully Committed
        |--------------------------------------------------------------------------
        |
        | Send notifications ONLY after all payments are created.
        |
        */

        foreach ($createdPayments as $payment) {
            $this->queueNotification($payment);
        }

        /*
        |--------------------------------------------------------------------------
        | Build Payment Data
        |--------------------------------------------------------------------------
        */

        $paymentData = [];

        foreach ($createdPayments as $payment) {
            $paymentData[] = $this->dataBuilder
                ->payment($payment);
        }

        /*
        |--------------------------------------------------------------------------
        | ONE Bulk Receipt
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | ReceiptService::bulk() calls ->load() internally, so it requires
        | an Eloquent Collection (NOT a base Support Collection).
        |
        | collect($array) creates Illuminate\Support\Collection which does
        | NOT have ->load() → "Method Illuminate\Support\Collection::load
        | does not exist."
        |
        | Using Eloquent\Collection here fixes that.
        |
        */

        $receipt = $this->receiptService
            ->bulk(
                new \Illuminate\Database\Eloquent\Collection(
                    $createdPayments
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Calculate Summary
        |--------------------------------------------------------------------------
        */

        $totalFinalFee = 0;
        $totalDiscount = 0;
        $totalPaid = 0;

        foreach ($createdPayments as $payment) {

            $totalFinalFee +=
                (float) $payment
                    ->enrollment
                    ->final_fee;

            $totalDiscount +=
                (float) $payment
                    ->discount_amount;

            $totalPaid +=
                (float) $payment
                    ->amount;
        }

        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */

        return [
            'payments' => $paymentData,

            'receipt' => $receipt,

            'count' => count(
                $createdPayments
            ),

            'total_final_fee' => round(
                $totalFinalFee,
                2
            ),

            'total_discount' => round(
                $totalDiscount,
                2
            ),

            'total_paid' => round(
                $totalPaid,
                2
            ),

            'paid_at' => $paidAt->format(
                'Y-m-d H:i:s'
            ),
        ];
    }

    /**
     * Send payment notifications.
     *
     * PaymentNotificationService handles:
     *
     * FCM:
     *     Active student devices only.
     *
     * SMS:
     *     Guardian mobile.
     */
    private function queueNotification(
        Payment $payment
    ): void {
        /*
        |--------------------------------------------------------------------------
        | Load Required Relationships
        |--------------------------------------------------------------------------
        */

        $payment->load([
            'student',
            'enrollment.studentClass.teacher',
            'enrollment.studentClass.subject',
            'enrollment.studentClass.grade',
            'enrollment.classCategoryFee.category',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Send Notifications
        |--------------------------------------------------------------------------
        */

        $this->notificationService
            ->sendSuccess($payment);
    }
}