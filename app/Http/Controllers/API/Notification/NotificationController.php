<?php

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\SendNotificationRequest;
use App\Http\Requests\Notification\BulkNotificationRequest;
use App\Models\Notification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Send notification to a single student
     */
    public function send(SendNotificationRequest $request): JsonResponse
    {
        try {
            $notification = $this->notificationService->send(
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => $notification->wasFailed()
                    ? 'Notification created but student has no active devices.'
                    : 'Notification queued successfully.',
                'data' => [
                    'notification_id' => $notification->id,
                    'student_id' => $notification->student_id,
                    'status' => $notification->status,
                    'status_label' => \App\Enums\NotificationStatus::label($notification->status),
                    'scheduled_at' => $notification->scheduled_at,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            Log::error('Notification send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Send notification immediately (synchronous)
     */
    public function sendNow(SendNotificationRequest $request): JsonResponse
    {
        try {
            $notification = $this->notificationService->sendNow(
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification sent immediately.',
                'data' => [
                    'notification_id' => $notification->id,
                    'student_id' => $notification->student_id,
                    'status' => $notification->status,
                    'status_label' => \App\Enums\NotificationStatus::label($notification->status),
                    'sent_at' => $notification->sent_at,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Immediate notification send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send bulk notifications to multiple students
     */
    public function sendBulk(BulkNotificationRequest $request): JsonResponse
    {
        try {
            $result = $this->notificationService->sendToMany(
                $request->student_ids,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Bulk notifications processed.',
                'data' => [
                    'total' => $result['total'],
                    'success_count' => count($result['success']),
                    'failed_count' => count($result['failed']),
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                ],
            ], Response::HTTP_ACCEPTED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Bulk notification send failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_count' => count($request->student_ids ?? []),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send notification to all active students
     */
    public function sendToAll(BulkNotificationRequest $request): JsonResponse
    {
        try {
            $result = $this->notificationService->sendToAll(
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent to all students.',
                'data' => [
                    'total' => $result['total'],
                    'success_count' => count($result['success']),
                    'failed_count' => count($result['failed']),
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                ],
            ], Response::HTTP_ACCEPTED);
        } catch (\Exception $e) {
            Log::error('Send to all failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send notification to a specific grade
     */
    public function sendToGrade(SendNotificationRequest $request, string $grade): JsonResponse
    {
        try {
            $result = $this->notificationService->sendToGrade(
                $grade,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => "Notifications sent to grade: {$grade}",
                'data' => [
                    'grade' => $grade,
                    'total' => $result['total'],
                    'success_count' => count($result['success']),
                    'failed_count' => count($result['failed']),
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                ],
            ], Response::HTTP_ACCEPTED);
        } catch (\Exception $e) {
            Log::error('Send to grade failed', [
                'grade' => $grade,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notification status
     */
    public function status(int $id): JsonResponse
    {
        try {
            $status = $this->notificationService->getStatus($id);

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Get notification status failed', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification status: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retry failed notification
     */
    public function retry(int $id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            $this->notificationService->retry($notification);

            return response()->json([
                'success' => true,
                'message' => 'Notification retry queued.',
                'data' => [
                    'notification_id' => $notification->id,
                    'retry_count' => $notification->retry_count + 1,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            Log::error('Retry notification failed', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retry notification: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel pending notification
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            $this->notificationService->cancel($notification);

            return response()->json([
                'success' => true,
                'message' => 'Notification cancelled.',
                'data' => [
                    'notification_id' => $notification->id,
                    'status' => $notification->status,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            Log::error('Cancel notification failed', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel notification: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get student notification history
     */
    public function history(int $studentId): JsonResponse
    {
        try {
            $notifications = $this->notificationService->getStudentNotifications($studentId);

            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $studentId,
                    'total' => $notifications->count(),
                    'notifications' => $notifications->map(function ($notification) {
                        return [
                            'id' => $notification->id,
                            'title' => $notification->title,
                            'body' => $notification->body,
                            'type' => $notification->type,
                            'type_label' => \App\Enums\NotificationType::label($notification->type),
                            'status' => $notification->status,
                            'status_label' => \App\Enums\NotificationStatus::label($notification->status),
                            'sent_at' => $notification->sent_at,
                            'read_at' => $notification->read_at,
                            'created_at' => $notification->created_at,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get notification history failed', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification history: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
                'data' => [
                    'notification_id' => $notification->id,
                    'read_at' => $notification->read_at,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error('Mark as read failed', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notification statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->notificationService->getStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Get notification stats failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification statistics: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notifications with filters
     */
    public function index(\App\Http\Requests\Notification\NotificationFilterRequest $request): JsonResponse
    {
        try {
            $notifications = $this->notificationService->getNotifications(
                $request->validated(),
                $request->input('per_page', 20)
            );

            return response()->json([
                'success' => true,
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            Log::error('Get notifications list failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $request->validated(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete old notifications
     */
    public function deleteOld(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $request->validate([
                'days' => 'nullable|integer|min:1|max:365',
            ]);

            $days = $request->input('days', 30);
            $deleted = $this->notificationService->deleteOldNotifications($days);

            return response()->json([
                'success' => true,
                'message' => "Deleted {$deleted} notifications older than {$days} days.",
                'data' => [
                    'deleted_count' => $deleted,
                    'days' => $days,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Delete old notifications failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'days' => $request->input('days', 30),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete old notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
