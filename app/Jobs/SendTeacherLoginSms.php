<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTeacherLoginSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [10, 30, 60];

    protected string $mobile;
    protected string $username;
    protected string $password;

    public function __construct(
        string $mobile,
        string $username,
        string $password
    ) {
        $this->mobile = $mobile;
        $this->username = $username;
        $this->password = $password;

        $this->onQueue('sms');
    }

    public function handle(SmsService $smsService): void
    {
        $appName = config('app.name');

        $message = "{$appName}\n\n"
            . "Teacher Login Details\n\n"
            . "Username: {$this->username}\n"
            . "Password: {$this->password}\n\n"
            . "Please keep your login details secure.";

        $response = $smsService->sendSms(
            $this->mobile,
            $message
        );

        if (!($response['success'] ?? false)) {

            Log::warning('Teacher login SMS failed', [
                'mobile' => $this->mobile,
                'attempt' => $this->attempts(),
                'response' => $response,
            ]);

            throw new \Exception($response['error'] ?? 'SMS sending failed');
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Teacher login SMS permanently failed', [
            'mobile' => $this->mobile,
            'error' => $exception->getMessage(),
        ]);
    }
}