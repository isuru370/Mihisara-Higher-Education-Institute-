<?php

namespace App\Services\Notification;

use App\Models\Student;
use App\Models\Notification;
use App\Models\FcmToken;
use App\Enums\NotificationType;
use App\Enums\NotificationStatus;
use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotificationService
{
    /**
     * Send notification to a single student
     */
    public function send(array $data): Notification
    {
        $this->validateNotificationData($data);

        return DB::transaction(function () use ($data) {
            $student = Student::findOrFail($data['student_id']);

            // Check if student has active tokens
            $hasTokens = FcmToken::where('student_id', $student->id)
                ->where('is_active', true)
                ->latest('created_at')
                ->first();

            $notification = Notification::create([
                'student_id' => $student->id,
                'title' => $data['title'],
                'body' => $data['message'],
                'type' => $data['type'] ?? NotificationType::GENERAL,
                'status' => $hasTokens ? NotificationStatus::PENDING : NotificationStatus::FAILED,
                'data' => $data['data'] ?? null,
                'created_by' => auth()->id(),
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'error_message' => $hasTokens ? null : 'No active FCM token found.',
            ]);

            if ($hasTokens) {
                $this->dispatchNotification($notification);
            }

            Log::info('Notification created', [
                'notification_id' => $notification->id,
                'student_id' => $student->id,
                'has_tokens' => $hasTokens,
                'scheduled' => $notification->scheduled_at,
            ]);

            return $notification;
        });
    }

    /**
     * Dispatch notification to queue
     */
    protected function dispatchNotification(Notification $notification): void
    {
        if ($notification->isScheduled()) {
            $delay = $notification->scheduled_at->diffInSeconds(now());
            SendNotificationJob::dispatch($notification->id)
                ->onQueue('notifications')
                ->delay(now()->addSeconds($delay));
        } else {
            SendNotificationJob::dispatch($notification->id)
                ->onQueue('notifications');
        }
    }

    /**
     * Send notification immediately (synchronous)
     */
    public function sendNow(array $data): Notification
    {
        $this->validateNotificationData($data);

        $notification = DB::transaction(function () use ($data) {
            $student = Student::findOrFail($data['student_id']);

            return Notification::create([
                'student_id' => $student->id,
                'title' => $data['title'],
                'body' => $data['message'],
                'type' => $data['type'] ?? NotificationType::GENERAL,
                'status' => NotificationStatus::PROCESSING,
                'data' => $data['data'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        // Send synchronously
        dispatch_sync(new SendNotificationJob($notification->id));

        return $notification;
    }

    /**
     * Send to multiple students
     */
    public function sendToMany(array $studentIds, array $data): array
    {
        $this->validateBulkData($studentIds, $data);

        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($studentIds),
        ];

        DB::transaction(function () use ($studentIds, $data, &$results) {
            $students = Student::whereIn('id', $studentIds)
                ->where('is_active', true)
                ->get();

            if ($students->isEmpty()) {
                throw new \RuntimeException('No active students found');
            }

            foreach ($students as $student) {
                try {
                    $hasTokens = FcmToken::where('student_id', $student->id)
                        ->where('is_active', true)
                        ->exists();

                    $notification = Notification::create([
                        'student_id' => $student->id,
                        'title' => $data['title'],
                        'body' => $data['message'],
                        'type' => $data['type'] ?? NotificationType::GENERAL,
                        'status' => $hasTokens ? NotificationStatus::PENDING : NotificationStatus::FAILED,
                        'data' => $data['data'] ?? null,
                        'created_by' => auth()->id(),
                        'error_message' => $hasTokens ? null : 'No active FCM token found.',
                    ]);

                    if ($hasTokens) {
                        SendNotificationJob::dispatch($notification->id)
                            ->onQueue('notifications');

                        $results['success'][] = [
                            'id' => $notification->id,
                            'student_id' => $student->id,
                            'student_name' => $student->name ?? 'Unknown',
                        ];
                    } else {
                        $results['failed'][] = [
                            'student_id' => $student->id,
                            'student_name' => $student->name ?? 'Unknown',
                            'reason' => 'No active FCM token',
                        ];
                    }
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->name ?? 'Unknown',
                        'reason' => $e->getMessage(),
                    ];
                }
            }
        });

        Log::info('Bulk notifications processed', [
            'total' => $results['total'],
            'success' => count($results['success']),
            'failed' => count($results['failed']),
        ]);

        return $results;
    }

    /**
     * Send to all active students
     */
    public function sendToAll(array $data): array
    {
        $studentIds = Student::where('is_active', true)->pluck('id')->toArray();

        if (empty($studentIds)) {
            throw new \RuntimeException('No active students found');
        }

        return $this->sendToMany($studentIds, $data);
    }

    /**
     * Send to students by grade
     */
    public function sendToGrade(string $grade, array $data): array
    {
        $studentIds = Student::where('grade', $grade)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            throw new \RuntimeException("No active students found in grade: {$grade}");
        }

        return $this->sendToMany($studentIds, $data);
    }

    /**
     * Send to students by class
     */
    public function sendToClass(string $class, array $data): array
    {
        $studentIds = Student::where('class', $class)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            throw new \RuntimeException("No active students found in class: {$class}");
        }

        return $this->sendToMany($studentIds, $data);
    }

    /**
     * Retry failed notification
     */
    public function retry(Notification $notification): void
    {
        if ($notification->status !== NotificationStatus::FAILED) {
            throw new \InvalidArgumentException('Only failed notifications can be retried');
        }

        if ($notification->retry_count >= 3) {
            throw new \RuntimeException('Maximum retry attempts exceeded');
        }

        // Increment retry count
        $notification->increment('retry_count');

        // Reset status to pending
        $notification->update([
            'status' => NotificationStatus::PENDING,
            'error_message' => null,
        ]);

        SendNotificationJob::dispatch($notification->id)->onQueue('notifications');

        Log::info('Notification retry queued', [
            'notification_id' => $notification->id,
            'retry_count' => $notification->retry_count,
        ]);
    }

    /**
     * Cancel pending notification
     */
    public function cancel(Notification $notification): void
    {
        if ($notification->status !== NotificationStatus::PENDING) {
            throw new \InvalidArgumentException('Only pending notifications can be cancelled');
        }

        $notification->update([
            'status' => NotificationStatus::CANCELLED,
        ]);

        Log::info('Notification cancelled', [
            'notification_id' => $notification->id,
        ]);
    }

    /**
     * Get notification status
     */
    public function getStatus(int $notificationId): array
    {
        $notification = Notification::findOrFail($notificationId);

        return [
            'id' => $notification->id,
            'status' => $notification->status,
            'status_label' => NotificationStatus::label($notification->status),
            'title' => $notification->title,
            'body' => $notification->body,
            'type' => $notification->type,
            'type_label' => NotificationType::label($notification->type),
            'type_icon' => NotificationType::icon($notification->type),
            'sent_at' => $notification->sent_at,
            'read_at' => $notification->read_at,
            'scheduled_at' => $notification->scheduled_at,
            'error_message' => $notification->error_message,
            'retry_count' => $notification->retry_count,
            'created_at' => $notification->created_at,
            'student' => [
                'id' => $notification->student->id,
                'name' => $notification->student->name ?? 'Unknown',
            ],
        ];
    }

    /**
     * Validate single notification data
     */
    protected function validateNotificationData(array $data): void
    {
        $validator = Validator::make($data, [
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:1000',
            'type' => 'nullable|string|in:' . implode(',', NotificationType::all()),
            'data' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate bulk notification data
     */
    protected function validateBulkData(array $studentIds, array $data): void
    {
        if (empty($studentIds)) {
            throw new \InvalidArgumentException('Student IDs cannot be empty');
        }

        $validator = Validator::make($data, [
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:1000',
            'type' => 'nullable|string|in:' . implode(',', NotificationType::all()),
            'data' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Get pending notifications count
     */
    public function getPendingCount(): int
    {
        return Notification::where('status', NotificationStatus::PENDING)->count();
    }

    /**
     * Get failed notifications count
     */
    public function getFailedCount(): int
    {
        return Notification::where('status', NotificationStatus::FAILED)->count();
    }

    /**
     * Get notifications for a student
     */
    public function getStudentNotifications(int $studentId, int $limit = 20): \Illuminate\Support\Collection
    {
        return Notification::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notifications with filters
     */
    public function getNotifications(array $filters = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Notification::query();

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('body', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Delete old notifications
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        $deleted = Notification::where('created_at', '<', now()->subDays($days))
            ->whereIn('status', [NotificationStatus::SENT, NotificationStatus::FAILED, NotificationStatus::CANCELLED])
            ->delete();

        Log::info('Old notifications deleted', [
            'days' => $days,
            'deleted_count' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Get notification statistics
     */
    public function getStats(): array
    {
        $total = Notification::count();
        $pending = $this->getPendingCount();
        $processing = Notification::where('status', NotificationStatus::PROCESSING)->count();
        $sent = Notification::where('status', NotificationStatus::SENT)->count();
        $failed = $this->getFailedCount();
        $cancelled = Notification::where('status', NotificationStatus::CANCELLED)->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'processing' => $processing,
            'sent' => $sent,
            'failed' => $failed,
            'cancelled' => $cancelled,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
            'by_type' => $this->getStatsByType(),
            'by_date' => $this->getStatsByDate(),
        ];
    }

    /**
     * Get statistics by type
     */
    protected function getStatsByType(): array
    {
        $stats = [];
        foreach (NotificationType::all() as $type) {
            $stats[$type] = Notification::where('type', $type)->count();
        }
        return $stats;
    }

    /**
     * Get statistics by date (last 7 days)
     */
    protected function getStatsByDate(): array
    {
        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $stats[$date->format('Y-m-d')] = Notification::whereDate('created_at', $date)->count();
        }
        return $stats;
    }
}
