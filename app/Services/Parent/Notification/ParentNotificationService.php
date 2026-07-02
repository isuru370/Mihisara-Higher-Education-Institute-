<?php

namespace App\Services\Parent\Notification;

use App\Models\Notification;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ParentNotificationService
{
    /**
     * Get all notifications for a student
     */
    public function getNotifications(int $studentId, array $filters = [], int $perPage = 20)
    {
        $query = Notification::where('student_id', $studentId)
            ->with(['student', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get notification details
     */
    public function getNotificationDetails(int $notificationId, int $studentId): ?Notification
    {
        $notification = Notification::where('id', $notificationId)
            ->where('student_id', $studentId)
            ->with(['student', 'creator'])
            ->first();

        if ($notification) {
            // Mark as read if not already read
            if (is_null($notification->read_at)) {
                $notification->markAsRead();
            }
        }

        return $notification;
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(int $studentId): int
    {
        return Notification::where('student_id', $studentId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get all unread notifications
     */
    public function getUnreadNotifications(int $studentId, int $limit = 20)
    {
        return Notification::where('student_id', $studentId)
            ->whereNull('read_at')
            ->with(['student', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId, int $studentId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('student_id', $studentId)
            ->first();

        if (!$notification) {
            return false;
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
            Log::info('Notification marked as read', [
                'notification_id' => $notificationId,
                'student_id' => $studentId,
            ]);
        }

        return true;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(int $studentId): int
    {
        $updated = Notification::where('student_id', $studentId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Log::info('All notifications marked as read', [
            'student_id' => $studentId,
            'count' => $updated,
        ]);

        return $updated;
    }

    /**
     * Get notification statistics
     */
    public function getStats(int $studentId): array
    {
        $total = Notification::where('student_id', $studentId)->count();
        $unread = $this->getUnreadCount($studentId);
        $read = $total - $unread;

        // Get counts by type
        $types = [];
        $typeLabels = NotificationType::all();
        foreach ($typeLabels as $type) {
            $types[$type] = Notification::where('student_id', $studentId)
                ->where('type', $type)
                ->count();
        }

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
            'by_type' => $types,
        ];
    }

    /**
     * Delete notification
     */
    public function delete(int $notificationId, int $studentId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('student_id', $studentId)
            ->first();

        if (!$notification) {
            return false;
        }

        $notification->delete();

        Log::info('Notification deleted', [
            'notification_id' => $notificationId,
            'student_id' => $studentId,
        ]);

        return true;
    }

    /**
     * Delete all read notifications
     */
    public function deleteReadNotifications(int $studentId): int
    {
        $deleted = Notification::where('student_id', $studentId)
            ->whereNotNull('read_at')
            ->delete();

        Log::info('Read notifications deleted', [
            'student_id' => $studentId,
            'count' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Format notification data for API response
     */
    public function formatNotification($notification): array
    {
        $data = [
            'id' => $notification->id,
            'title' => $notification->title,
            'body' => $notification->body,
            'type' => $notification->type,
            'type_label' => NotificationType::label($notification->type),
            'type_icon' => NotificationType::icon($notification->type),
            'status' => $notification->status,
            'status_label' => NotificationStatus::label($notification->status),
            'is_read' => !is_null($notification->read_at),
            'read_at' => $notification->read_at?->toISOString(),
            'sent_at' => $notification->sent_at?->toISOString(),
            'scheduled_at' => $notification->scheduled_at?->toISOString(),
            'created_at' => $notification->created_at->toISOString(),
            'data' => $notification->data,
        ];

        // Add student info
        if ($notification->student) {
            $data['student'] = [
                'id' => $notification->student->id,
                'name' => $notification->student->initial_name ?? 'Student',
                'custom_id' => $notification->student->custom_id,
            ];
        }

        // Add creator info
        if ($notification->creator) {
            $data['created_by'] = [
                'id' => $notification->creator->id,
                'name' => $notification->creator->name,
            ];
        }

        return $data;
    }

    /**
     * Format notification list for API response
     */
    public function formatNotificationList($notifications): array
    {
        if ($notifications instanceof LengthAwarePaginator) {
            $notifications = $notifications->getCollection();
        }

        if (is_array($notifications)) {
            $notifications = collect($notifications);
        }

        return $notifications->map(function ($notification) {
            return $this->formatNotification($notification);
        })->values()->toArray();
    }
}
