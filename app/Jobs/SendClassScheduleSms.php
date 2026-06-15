<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendClassScheduleSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [10, 30, 60];

    protected $guardianNumber;
    protected $message;

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

        if (!($response['success'] ?? false)) {

            Log::warning('Class schedule SMS failed', [
                'guardian_number' => $this->guardianNumber,
                'attempt' => $this->attempts(),
                'response' => $response,
            ]);

            throw new \Exception(
                $response['provider_message'] ?? 'SMS sending failed'
            );
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Class schedule SMS permanently failed', [
            'guardian_number' => $this->guardianNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}