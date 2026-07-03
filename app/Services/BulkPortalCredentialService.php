<?php

namespace App\Services;

use App\Models\StudentPortalLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkPortalCredentialService
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send portal credentials.
     *
     * @param string|null $studentUsername
     * @param callable|null $progress
     * @return array
     */
    public function sendCredentials(
        ?string $studentUsername = null,
        ?callable $progress = null
    ): array {

        $success = 0;
        $failed = 0;
        $skipped = 0;

        $query = StudentPortalLogin::with('student');

        if ($studentUsername) {
            $query->where('username', $studentUsername);
        }

        $query->chunk(100, function ($logins) use (
            &$success,
            &$failed,
            &$skipped,
            $progress
        ) {

            foreach ($logins as $login) {

                try {

                    if (!$login->student) {

                        $skipped++;

                        $this->advanceProgress($progress);

                        continue;
                    }

                    if (empty($login->student->guardian_mobile)) {

                        Log::warning('Guardian mobile not found.', [
                            'student_id' => $login->student->id,
                            'username'   => $login->username,
                        ]);

                        $skipped++;

                        $this->advanceProgress($progress);

                        continue;
                    }

                    $plainPassword = $this->generateStudentPassword(
                        $login->student->initial_name,
                        $login->student->guardian_mobile
                    );

                    $message = $this->buildSmsMessage(
                        $login->username,
                        $plainPassword
                    );

                    $response = $this->sendSmsWithRetry(
                        $login->student->guardian_mobile,
                        $message
                    );

                    if (!($response['success'] ?? false)) {

                        $failed++;

                        Log::warning('SMS sending failed.', [
                            'student_id' => $login->student->id,
                            'username'   => $login->username,
                            'response'   => $response,
                        ]);

                        $this->advanceProgress($progress);

                        continue;
                    }

                    DB::transaction(function () use (
                        $login,
                        $plainPassword
                    ) {

                        // Model mutator will hash automatically.
                        $login->password = $plainPassword;
                        $login->save();

                    });

                    $success++;

                    Log::info('Portal credentials sent.', [
                        'student_id' => $login->student->id,
                        'username'   => $login->username,
                    ]);

                } catch (\Throwable $e) {

                    $failed++;

                    Log::error('Bulk credential error.', [
                        'student_id' => optional($login->student)->id,
                        'username'   => $login->username,
                        'message'    => $e->getMessage(),
                    ]);
                }

                $this->advanceProgress($progress);

                // Prevent API flooding.
                usleep(500000);
            }
        });

        return [
            'success' => $success,
            'failed'  => $failed,
            'skipped' => $skipped,
        ];
    }

    /**
     * Retry SMS sending.
     */
    private function sendSmsWithRetry(
        string $mobile,
        string $message,
        int $maxAttempts = 3
    ): array {

        $lastResponse = [];

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {

            $lastResponse = $this->smsService->sendSms(
                $mobile,
                $message
            );

            if ($lastResponse['success'] ?? false) {
                return $lastResponse;
            }

            Log::warning('SMS retry failed.', [
                'attempt' => $attempt,
                'mobile'  => $mobile,
                'response'=> $lastResponse,
            ]);

            if ($attempt < $maxAttempts) {
                sleep(2);
            }
        }

        return $lastResponse;
    }

    /**
     * SMS template.
     */
    private function buildSmsMessage(
        string $username,
        string $password
    ): string {

        $appName = config('app.name');

        $playStore =
            "https://play.google.com/apps/internaltest/4700199862235636842";

        return "{$appName} Student Portal\n\n"
            . "Username: {$username}\n"
            . "Password: {$password}\n\n"
            . "Download Parent App:\n"
            . "{$playStore}\n\n"
            . "Please keep your login details secure.";
    }

    /**
     * Password generator.
     */
    private function generateStudentPassword(
        string $initialName,
        string $guardianMobile
    ): string {

        $letters = preg_replace('/[^A-Za-z]/', '', $initialName);

        $initials = strtoupper(substr($letters, 0, 2));

        if (strlen($initials) < 2) {
            $initials = str_pad($initials, 2, 'X');
        }

        $digits = preg_replace('/\D/', '', $guardianMobile);

        $lastFour = substr($digits, -4);

        $random =
            chr(random_int(65, 90)) .
            chr(random_int(97, 122));

        return $initials . $lastFour . $random;
    }

    /**
     * Advance progress bar if available.
     */
    private function advanceProgress(?callable $progress): void
    {
        if ($progress) {
            $progress();
        }
    }
}