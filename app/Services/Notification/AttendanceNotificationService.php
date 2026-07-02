<?php

namespace App\Services\Notification;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\Notification;
use App\Models\FcmToken;
use App\Enums\NotificationType;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendAttendanceSuccessSmsJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceNotificationService
{
    /**
     * Send attendance notification (SMS + FCM)
     */
    public function sendSuccess(
        Student $student,
        StudentAttendance $attendance,
        ?StudentClassEnrollment $enrollment = null
    ): void {
        try {
            // Prepare attendance data
            $data = $this->prepareData($student, $attendance, $enrollment);

            // Send FCM Push Notification
            $this->sendFcmNotification($student, $attendance, $data);

            // Send SMS Notification
            //$this->sendSmsNotification($student, $attendance, $enrollment, $data);

            Log::info('Attendance notification sent successfully', [
                'student_id' => $student->id,
                'attendance_id' => $attendance->id,
                'fcm_sent' => $this->hasActiveTokens($student->id),
                'sms_sent' => (bool) $student->guardian_mobile,
            ]);

        } catch (\Exception $e) {
            Log::error('Attendance notification failed', [
                'student_id' => $student->id ?? null,
                'attendance_id' => $attendance->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send FCM push notification
     */
    private function sendFcmNotification(
        Student $student,
        StudentAttendance $attendance,
        array $data
    ): void {
        // Check if student has active FCM tokens
        if (!$this->hasActiveTokens($student->id)) {
            Log::info('No active FCM tokens', ['student_id' => $student->id]);
            return;
        }

        // Build notification message
        $message = $this->buildFcmMessage($data);

        // Create notification record
        $notification = Notification::create([
            'student_id' => $student->id,
            'title' => 'Attendance Marked!',
            'body' => $message,
            'type' => NotificationType::ATTENDANCE,
            'data' => [
                'attendance_id' => $attendance->id,
                'student_id' => $student->id,
                'student_name' => $data['student_name'],
                'class_name' => $data['class_name'],
                'grade' => $data['grade'],
                'category' => $data['category'],
                'date' => $data['date'],
                'time' => $data['time'],
                'mark_method' => $data['mark_method'],
            ],
            'status' => NotificationStatus::PENDING,
            'created_by' => auth()->id(),
        ]);

        // Dispatch to queue
        SendNotificationJob::dispatch($notification->id)->onQueue('notifications');

        Log::info('Attendance FCM queued', [
            'notification_id' => $notification->id,
            'student_id' => $student->id,
            'attendance_id' => $attendance->id,
        ]);
    }

    /**
     * Send SMS notification
     */
    private function sendSmsNotification(
        Student $student,
        StudentAttendance $attendance,
        ?StudentClassEnrollment $enrollment,
        array $data
    ): void {
        $guardianMobile = $student->guardian_mobile;

        if (!$guardianMobile) {
            Log::info('No guardian mobile', ['student_id' => $student->id]);
            return;
        }

        $message = $this->buildSmsMessage($student, $attendance, $enrollment, $data);

        SendAttendanceSuccessSmsJob::dispatch($guardianMobile, $message);

        Log::info('Attendance SMS queued', [
            'student_id' => $student->id,
            'mobile' => $guardianMobile,
        ]);
    }

    /**
     * Prepare attendance data
     */
    private function prepareData(
        Student $student,
        StudentAttendance $attendance,
        ?StudentClassEnrollment $enrollment
    ): array {
        return [
            'student_name' => $student->initial_name ?? 'Student',
            'class_name' => $enrollment?->studentClass?->class_name ?? 'N/A',
            'grade' => $enrollment?->studentClass?->grade?->grade_name ?? 'N/A',
            'category' => $enrollment?->classCategoryFee?->category?->category_name ?? 'N/A',
            'date' => $attendance->attended_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'time' => $attendance->attended_at?->format('H:i') ?? now()->format('H:i'),
            'mark_method' => $attendance->mark_method ?? 'manual',
        ];
    }

    /**
     * Build FCM message
     */
    private function buildFcmMessage(array $data): string
    {
        return sprintf(
            "Dear Parent,\n\nAttendance has been marked for %s.\n\n Class: %s\n Grade: %s\n Category: %s\n Date: %s\n Time: %s\n\nThank you!",
            $data['student_name'],
            $data['class_name'],
            $data['grade'],
            $data['category'],
            $data['date'],
            $data['time']
        );
    }

    /**
     * Build SMS message
     */
    private function buildSmsMessage(
        Student $student,
        StudentAttendance $attendance,
        ?StudentClassEnrollment $enrollment,
        array $data
    ): string {
        return sprintf(
            'Attendance marked. Student: %s, Class: %s, Category: %s, Grade: %s, Date: %s, Time: %s. Thank you.',
            $data['student_name'],
            $data['class_name'],
            $data['category'],
            $data['grade'],
            $data['date'],
            $data['time']
        );
    }

    /**
     * Check if student has active FCM tokens
     */
    private function hasActiveTokens(int $studentId): bool
    {
        return FcmToken::where('student_id', $studentId)
            ->where('is_active', true)
            ->exists();
    }
}