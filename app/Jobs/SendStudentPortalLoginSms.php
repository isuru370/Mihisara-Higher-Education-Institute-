<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendStudentPortalLoginSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [10, 30, 60];

    protected string $guardianNumber;
    protected string $username;
    protected string $password;

    public function __construct(
        string $guardianNumber,
        string $username,
        string $password
    ) {
        $this->guardianNumber = $guardianNumber;
        $this->username = $username;
        $this->password = $password;

        $this->onQueue('sms');
    }

    public function handle(SmsService $smsService): void
    {
        $appName = config('app.name');

        $playStoreLink = "https://play.google.com/apps/internaltest/4700199862235636842";

        $message = "{$appName} Student Portal\n\n"
            . "Username: {$this->username}\n"
            . "Password: {$this->password}\n\n"
            . "Download Parent App:\n"
            . "{$playStoreLink}\n\n"
            . "Please keep your login details secure.";

        $response = $smsService->sendSms(
            $this->guardianNumber,
            $message
        );

        if (!($response['success'] ?? false)) {

            Log::warning('Student portal SMS failed', [
                'guardian_number' => $this->guardianNumber,
                'attempt' => $this->attempts(),
                'response' => $response,
            ]);

            throw new \Exception($response['error'] ?? 'SMS sending failed');
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Student portal SMS permanently failed', [
            'guardian_number' => $this->guardianNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}
