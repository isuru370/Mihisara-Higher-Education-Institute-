<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [10, 30, 60];

    protected $guardianNumber;
    protected $message;

    public function __construct($guardianNumber, $message)
    {
        $this->guardianNumber = $guardianNumber;
        $this->message = $message;
        $this->onQueue('sms');
    }

    public function handle(SmsService $smsService): void
    {
        $response = $smsService->sendSms($this->guardianNumber, $this->message);

        if (!($response['success'] ?? false)) {

            Log::warning('SMS sending failed', [
                'guardian_number' => $this->guardianNumber,
                'attempt' => $this->attempts(),
                'response' => $response
            ]);

            throw new \Exception($response['provider_message'] ?? 'SMS sending failed');
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SMS job permanently failed after ' . $this->attempts() . ' attempts', [
            'guardian_number' => $this->guardianNumber,
            'error' => $exception->getMessage()
        ]);
    }
}
