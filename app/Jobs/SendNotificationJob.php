<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Enums\NotificationStatus;
use App\Services\Firebase\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 120;
    public $maxExceptions = 3;
    
    protected int $notificationId;

    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
        $this->onQueue('notifications');
    }

    public function handle(FirebaseNotificationService $firebaseService): void
    {
        try {
            Log::info('Job started', [
                'notification_id' => $this->notificationId,
                'attempt' => $this->attempts(),
            ]);

            $notification = Notification::find($this->notificationId);

            if (!$notification) {
                Log::error('Notification not found', [
                    'notification_id' => $this->notificationId,
                ]);
                return;
            }

            Log::info('Notification found', [
                'notification_id' => $notification->id,
                'student_id' => $notification->student_id,
                'status' => $notification->status,
            ]);

            // Check if already sent or cancelled
            if ($notification->wasSent() || $notification->isCancelled()) {
                Log::info('Notification already processed', [
                    'notification_id' => $notification->id,
                    'status' => $notification->status,
                ]);
                return;
            }

            // Check if scheduled for later
            if ($notification->isScheduled()) {
                $delay = $notification->scheduled_at->diffInSeconds(now());
                if ($delay > 0) {
                    self::dispatch($notification->id)
                        ->onQueue('notifications')
                        ->delay(now()->addSeconds($delay));
                    
                    Log::info('Notification re-dispatched for scheduled time', [
                        'notification_id' => $notification->id,
                        'scheduled_at' => $notification->scheduled_at,
                        'delay_seconds' => $delay,
                    ]);
                    return;
                }
            }

            // Check if student exists and has active tokens
            try {
                $student = $notification->student;
                if (!$student) {
                    $notification->markAsFailed('Student not found');
                    Log::error('Student not found', [
                        'notification_id' => $notification->id,
                        'student_id' => $notification->student_id,
                    ]);
                    return;
                }

                // Check if student has active FCM tokens
                $tokensCount = $student->activeFcmTokens()->count();
                
                Log::info('Active tokens check', [
                    'notification_id' => $notification->id,
                    'student_id' => $student->id,
                    'tokens_count' => $tokensCount,
                ]);

                if ($tokensCount === 0) {
                    $notification->markAsFailed('No active FCM tokens at time of sending');
                    Log::warning('No active tokens for notification', [
                        'notification_id' => $notification->id,
                        'student_id' => $notification->student_id,
                    ]);
                    return;
                }

            } catch (\Exception $e) {
                Log::error('Error checking student tokens', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $notification->markAsFailed('Error checking student tokens: ' . $e->getMessage());
                return;
            }

            $notification->markAsProcessing();


            $firebaseService->send($notification);


        } catch (\Exception $e) {
            Log::error('Job failed with exception', [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        $notification = Notification::find($this->notificationId);
        
        if ($notification) {
            $notification->update([
                'status' => NotificationStatus::FAILED,
                'error_message' => 'Job failed after ' . $this->attempts() . ' attempts: ' . $exception->getMessage(),
            ]);
        }

        Log::error('SendNotificationJob failed permanently', [
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}