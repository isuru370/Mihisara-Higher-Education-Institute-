<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAttendanceSuccessSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;
    public array $backoff = [10, 30, 60];

    protected string $guardianNumber;
    protected string $message;

    public function __construct(string $guardianNumber, string $message)
    {
        $this->guardianNumber = $guardianNumber;
        $this->message = $message;

        $this->onQueue('sms');
    }

    public function handle(SmsService $smsService): void
    {
        $response = $smsService->sendSms(
            $this->guardianNumber,
            $this->message
        );

        if (! ($response['success'] ?? false)) {
            Log::warning('Attendance SMS sending failed', [
                'guardian_number' => $this->guardianNumber,
                'attempt' => $this->attempts(),
                'response' => $response,
            ]);

            throw new \Exception(
                $response['error']
                    ?? $response['provider_message']
                    ?? 'Attendance SMS sending failed'
            );
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Attendance SMS job permanently failed after ' . $this->attempts() . ' attempts', [
            'guardian_number' => $this->guardianNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}
