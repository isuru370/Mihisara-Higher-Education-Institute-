<?php


use App\Http\Controllers\API\Parent\Attendance\StudentAttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Parent\Auth\ParentAuthController;
use App\Http\Controllers\API\Parent\ClassSchedule\ClassScheduleController;
use App\Http\Controllers\API\Parent\Dashboard\DashboardController;
use App\Http\Controllers\API\Parent\Exam\ExamController;
use App\Http\Controllers\API\Parent\FCM\FcmTokenController;
use App\Http\Controllers\API\Parent\Notification\ParentNotificationController;
use App\Http\Controllers\API\Parent\Payment\StudentPaymentController;
use App\Http\Controllers\API\Parent\Result\ResultController;
use App\Http\Controllers\API\Parent\Teacher\TeacherController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/auth/login', [ParentAuthController::class, 'login']);

    Route::post('/auth/logout', [ParentAuthController::class, 'logout']);

    Route::post('/auth/change-password', [ParentAuthController::class, 'changePassword']);

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::post('/dashboard', [DashboardController::class, 'fetchDashboardData']);

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging
    |--------------------------------------------------------------------------
    */

    Route::prefix('/fcm')->group(function () {
        Route::post('/token', [FcmTokenController::class, 'store']);
        Route::post('/logout', [FcmTokenController::class, 'logout']);
    });

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    Route::post('/attendance', [StudentAttendanceController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Payment
    |--------------------------------------------------------------------------
    */

    Route::post('/payments', [StudentPaymentController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Exam
    |--------------------------------------------------------------------------
    */

    Route::post('/exams', [ExamController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Result
    |--------------------------------------------------------------------------
    */

    Route::post('/results', [ResultController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Teacher Details
    |--------------------------------------------------------------------------
    */

    Route::post('/teachers', [TeacherController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Class Schedule
    |--------------------------------------------------------------------------
    */

    Route::post('/schedule', [ClassScheduleController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | 🚀 PARENT NOTIFICATIONS
    |--------------------------------------------------------------------------
    */

    // Get all notifications (paginated) - POST
    Route::post('/notifications', [ParentNotificationController::class, 'index']);

    // Get notification details - POST
    Route::post('/notifications/{id}', [ParentNotificationController::class, 'show']);

    // Get unread notifications - POST
    Route::post('/notifications/unread', [ParentNotificationController::class, 'unread']);

    // Get unread count - POST
    Route::post('/notifications/unread/count', [ParentNotificationController::class, 'unreadCount']);

    // Mark as read - POST
    Route::post('/notifications/{id}/read', [ParentNotificationController::class, 'markAsRead']);

    // Mark all as read - POST
    Route::post('/notifications/read-all', [ParentNotificationController::class, 'markAllAsRead']);

    // Delete notification - POST
    Route::post('/notifications/{id}/delete', [ParentNotificationController::class, 'destroy']);

    // Delete all read notifications - POST
    Route::post('/notifications/read/delete', [ParentNotificationController::class, 'deleteRead']);

    // Get notification statistics - POST
    Route::post('/notifications/stats', [ParentNotificationController::class, 'stats']);
});
