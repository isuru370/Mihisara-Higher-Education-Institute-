<?php

namespace App\Http\Controllers\API\Parent\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Notification\NotificationRequest;
use App\Http\Requests\Parent\Notification\NotificationFilterRequest;
use App\Services\Parent\Notification\ParentNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ParentNotificationController extends Controller
{
    protected ParentNotificationService $notificationService;

    public function __construct(ParentNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications (POST)
     */
    public function index(NotificationFilterRequest $request): JsonResponse
    {
        try {
            // ✅ Get student_id from request body
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $notifications = $this->notificationService->getNotifications(
                (int) $studentId,
                $request->validated(),
                $request->input('per_page', 20)
            );

            $formattedData = $this->notificationService->formatNotificationList(
                $notifications->items()
            );

            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => [
                    'items' => $formattedData,
                    'pagination' => [
                        'total' => $notifications->total(),
                        'per_page' => $notifications->perPage(),
                        'current_page' => $notifications->currentPage(),
                        'last_page' => $notifications->lastPage(),
                        'from' => $notifications->firstItem(),
                        'to' => $notifications->lastItem(),
                    ],
                    'stats' => $this->notificationService->getStats((int) $studentId),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notification details (POST)
     */
    public function show(NotificationRequest $request, int $id): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $notification = $this->notificationService->getNotificationDetails($id, (int) $studentId);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $formattedData = $this->notificationService->formatNotification($notification);

            return response()->json([
                'success' => true,
                'message' => 'Notification details retrieved',
                'data' => $formattedData,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get notification details', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification details: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get unread notifications (POST)
     */
    public function unread(NotificationRequest $request): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $limit = $request->input('limit', 20);

            $notifications = $this->notificationService->getUnreadNotifications((int) $studentId, $limit);
            $formattedData = $this->notificationService->formatNotificationList($notifications);

            return response()->json([
                'success' => true,
                'message' => 'Unread notifications retrieved',
                'data' => [
                    'items' => $formattedData,
                    'count' => $notifications->count(),
                    'total_unread' => $this->notificationService->getUnreadCount((int) $studentId),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get unread notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get unread count (POST)
     */
    public function unreadCount(NotificationRequest $request): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $count = $this->notificationService->getUnreadCount((int) $studentId);

            return response()->json([
                'success' => true,
                'message' => 'Unread count retrieved',
                'data' => [
                    'unread_count' => $count,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get unread count', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mark notification as read (POST)
     */
    public function markAsRead(NotificationRequest $request, int $id): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $updated = $this->notificationService->markAsRead($id, (int) $studentId);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
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
     * Mark all notifications as read (POST)
     */
    public function markAllAsRead(NotificationRequest $request): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $updated = $this->notificationService->markAllAsRead((int) $studentId);

            return response()->json([
                'success' => true,
                'message' => "{$updated} notifications marked as read",
                'data' => [
                    'marked_count' => $updated,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete notification (POST)
     */
    public function destroy(NotificationRequest $request, int $id): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $deleted = $this->notificationService->delete($id, (int) $studentId);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete notification', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete all read notifications (POST)
     */
    public function deleteRead(NotificationRequest $request): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $deleted = $this->notificationService->deleteReadNotifications((int) $studentId);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} read notifications deleted",
                'data' => [
                    'deleted_count' => $deleted,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete read notifications', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete read notifications: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get notification statistics (POST)
     */
    public function stats(NotificationRequest $request): JsonResponse
    {
        try {
            $studentId = $request->input('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required',
                ], Response::HTTP_BAD_REQUEST);
            }

            $stats = $this->notificationService->getStats((int) $studentId);

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved',
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get notification stats', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notification statistics: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}