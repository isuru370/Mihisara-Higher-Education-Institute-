<?php

namespace App\Services\Notification;

use App\Models\Payment;
use App\Models\Notification;
use App\Models\FcmToken;
use App\Enums\NotificationType;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendPaymentSms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PaymentNotificationService
{
    /**
     * Send payment success notification
     */
    public function sendSuccess(Payment $payment): void
    {
        try {
            $student = $payment->student;

            if (!$this->validateStudent($student, $payment)) {
                return;
            }

            $this->sendFcmNotification($payment);
            $this->sendSmsNotification($payment);

            $this->logSuccess($payment);

        } catch (\Exception $e) {
            $this->logError($payment, $e);
        }
    }

    /**
     * Send FCM push notification
     */
    private function sendFcmNotification(Payment $payment): void
    {
        $studentId = $payment->student?->id;

        if (!$this->hasActiveTokens($studentId)) {
            Log::info('No active FCM tokens', ['student_id' => $studentId]);
            return;
        }

        $notification = Notification::create([
            'student_id' => $studentId,
            'title' => $this->getTitle(),
            'body' => $this->formatMessage($payment),
            'type' => NotificationType::PAYMENT,
            'data' => $this->buildPayload($payment),
            'status' => NotificationStatus::PENDING,
            'created_by' => auth()->id(),
        ]);

        SendNotificationJob::dispatch($notification->id)->onQueue('notifications');

        Log::info('Payment notification queued', [
            'payment_id' => $payment->id,
            'notification_id' => $notification->id,
        ]);
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification(Payment $payment): void
    {
        $mobile = $payment->student?->guardian_mobile;

        if (!$mobile) {
            return;
        }

        $message = $this->formatSmsMessage($payment);
        //SendPaymentSms::dispatch($mobile, $message);

        Log::info('Payment SMS queued', [
            'payment_id' => $payment->id,
            'mobile' => $mobile,
        ]);
    }

    /**
     * Validate student exists
     */
    private function validateStudent($student, Payment $payment): bool
    {
        if (!$student) {
            Log::warning('Student not found', ['payment_id' => $payment->id]);
            return false;
        }
        return true;
    }

    /**
     * Check if student has active FCM tokens
     */
    private function hasActiveTokens(?int $studentId): bool
    {
        if (!$studentId) {
            return false;
        }

        return FcmToken::where('student_id', $studentId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Get notification title
     */
    private function getTitle(): string
    {
        return 'Payment Confirmed!';
    }

    /**
     * Format push notification message
     */
    private function formatMessage(Payment $payment): string
    {
        $data = $this->getPaymentData($payment);

        return sprintf(
            "Dear %s,\n\nPayment of Rs. %s for %s has been received for %s.\n\n Class: %s\n Grade: %s\n Receipt: %s\n\nThank you!",
            $data['parent_name'],
            $data['amount'],
            $data['month'],
            $data['student_name'],
            $data['class_name'],
            $data['grade'],
            $data['receipt']
        );
    }

    /**
     * Format SMS message
     */
    private function formatSmsMessage(Payment $payment): string
    {
        $data = $this->getPaymentData($payment);

        return sprintf(
            "Payment received. Student: %s, Class: %s, Amount: Rs. %s, Month: %s, Receipt: %s. Thank you.",
            $data['student_name'],
            $data['class_name'],
            $data['amount'],
            $data['month'],
            $data['receipt']
        );
    }

    /**
     * Build notification payload data
     */
    private function buildPayload(Payment $payment): array
    {
        $data = $this->getPaymentData($payment);

        return [
            'payment_id' => $payment->id,
            'receipt_number' => $data['receipt'],
            'amount' => $data['amount_raw'],
            'payment_month' => $data['month_raw'],
            'student_name' => $data['student_name'],
            'class_name' => $data['class_name'],
            'grade' => $data['grade'],
            'category' => $data['category'],
        ];
    }

    /**
     * Get formatted payment data
     */
    private function getPaymentData(Payment $payment): array
    {
        return [
            'student_name' => $payment->student?->initial_name ?? 'Student',
            'parent_name' => $payment->student?->guardian_name ?? 'Parent',
            'amount' => number_format((float) $payment->amount, 2),
            'amount_raw' => $payment->amount,
            'month' => Carbon::parse($payment->payment_month)->format('F Y'),
            'month_raw' => Carbon::parse($payment->payment_month)->format('Y-m'),
            'class_name' => $payment->enrollment?->studentClass?->class_name ?? 'N/A',
            'grade' => $payment->enrollment?->studentClass?->grade?->grade_name ?? 'N/A',
            'category' => $payment->enrollment?->classCategoryFee?->category?->category_name ?? 'N/A',
            'receipt' => $payment->receipt_number ?? 'N/A',
        ];
    }

    /**
     * Log success
     */
    private function logSuccess(Payment $payment): void
    {
        Log::info('Payment notification sent', [
            'payment_id' => $payment->id,
            'student_id' => $payment->student?->id,
            'amount' => $payment->amount,
        ]);
    }

    /**
     * Log error
     */
    private function logError(Payment $payment, \Exception $e): void
    {
        Log::error('Payment notification failed', [
            'payment_id' => $payment->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}