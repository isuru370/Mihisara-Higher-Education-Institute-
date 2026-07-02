<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\Student;
use App\Enums\NotificationType;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Create a single notification.
     */
    public function create(array $data): Notification
    {
        return DB::transaction(function () use ($data) {
            $student = Student::findOrFail($data['student_id']);

            $notification = Notification::create([
                'student_id' => $student->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'type' => $data['type'] ?? NotificationType::GENERAL,
                'data' => $data['data'] ?? null,
                'status' => NotificationStatus::PENDING,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Dispatch to queue
            SendNotificationJob::dispatch($notification->id)
                ->onQueue('notifications');

            Log::info('Admin created notification', [
                'notification_id' => $notification->id,
                'student_id' => $student->id,
                'admin_id' => auth()->id(),
            ]);

            return $notification;
        });
    }

    /**
     * Send bulk notifications.
     */
    public function sendBulk(array $data): int
    {
        $studentIds = $data['student_ids'];
        $count = 0;

        foreach ($studentIds as $studentId) {
            try {
                $notification = Notification::create([
                    'student_id' => $studentId,
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'type' => $data['type'] ?? NotificationType::GENERAL,
                    'data' => $data['data'] ?? null,
                    'status' => NotificationStatus::PENDING,
                    'scheduled_at' => $data['scheduled_at'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                SendNotificationJob::dispatch($notification->id)
                    ->onQueue('notifications');

                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to send bulk notification', [
                    'student_id' => $studentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk notifications created', [
            'total' => count($studentIds),
            'success' => $count,
            'admin_id' => auth()->id(),
        ]);

        return $count;
    }

    /**
     * Retry a failed notification.
     */
    public function retry(Notification $notification): void
    {
        $notification->markAsPending();
        SendNotificationJob::dispatch($notification->id)->onQueue('notifications');

        Log::info('Notification retry queued', [
            'notification_id' => $notification->id,
            'admin_id' => auth()->id(),
        ]);
    }

    /**
     * Get notification statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => Notification::count(),
            'pending' => Notification::pending()->count(),
            'processing' => Notification::processing()->count(),
            'sent' => Notification::sent()->count(),
            'failed' => Notification::failed()->count(),
            'cancelled' => Notification::cancelled()->count(),
            'unread' => Notification::unread()->count(),
            'today' => Notification::whereDate('created_at', today())->count(),
            'this_week' => Notification::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'this_month' => Notification::whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Get recent notifications.
     */
    public function getRecent(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::with(['student', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notifications by student.
     */
    public function getByStudent(int $studentId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}