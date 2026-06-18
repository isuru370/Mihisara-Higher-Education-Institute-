<?php

namespace App\Services\ParentHub;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParentHubService
{
    public function registerStudent(
        string $username,
        string $studentCustomId
    ): bool {

        try {

            $response = Http::timeout(10)
                ->post(
                    rtrim(
                        config('services.parent_hub.url'),
                        '/'
                    ) . '/register-student',
                    [

                        'secret_key' =>
                        config('services.parent_hub.secret'),

                        'username' => $username,

                        'student_custom_id' =>
                        $studentCustomId,

                        'institute_code' =>
                        config('services.institute.code'),

                        'status' => true,
                    ]
                );

            if (!$response->successful()) {

                Log::error(
                    'Parent Hub Register Failed',
                    [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                );

                return false;
            }

            return true;
        } catch (\Throwable $e) {

            Log::error(
                'Parent Hub Register Error',
                [
                    'message' => $e->getMessage(),
                    'username' => $username,
                ]
            );

            return false;
        }
    }
    public function registerInstitute(): bool
    {
        try {

            $response = Http::timeout(10)
                ->post(
                    rtrim(
                        config('services.parent_hub.url'),
                        '/'
                    ) . '/register-institute',
                    [

                        'secret_key' =>
                        config('services.parent_hub.secret'),

                        'institute_code' =>
                        config('services.institute.code'),

                        'institute_name' =>
                        config('app.name'),

                        'api_url' =>
                        config('app.url'),

                        'contact_email' =>
                        config('services.institute.contact_email'),

                        'contact_mobile' =>
                        config('services.institute.contact_mobile'),

                        'app_version' =>
                        config('app.version'),


                    ]
                );

            if (!$response->successful()) {

                Log::error(
                    'Parent Hub Registration Failed',
                    [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                );

                return false;
            }

            return true;
        } catch (\Throwable $e) {

            Log::error(
                'Institute Registration Failed',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return false;
        }
    }
}
