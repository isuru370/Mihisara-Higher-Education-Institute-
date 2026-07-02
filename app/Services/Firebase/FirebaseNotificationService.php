<?php

namespace App\Services\Firebase;

use App\Models\Notification;
use App\Models\FcmToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected Client $client;
    protected string $projectId;
    
    // FCM error codes that indicate token is permanently invalid
    private const INVALID_TOKEN_ERRORS = [
        'UNREGISTERED',
        'NOT_FOUND',
        'INVALID_ARGUMENT', // Sometimes invalid token format
        'SENDER_ID_MISMATCH',
    ];

    private const CACHE_KEY = 'firebase_access_token';
    private const CACHE_TTL = 3300; // 55 minutes (less than 1 hour)

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'http_errors' => false,
        ]);

        $this->projectId = config('services.firebase.project_id');
        
        if (empty($this->projectId)) {
            throw new \RuntimeException('Firebase project ID is not configured.');
        }
    }

    /**
     * Generate Google OAuth Access Token with caching
     */
    private function getAccessToken(): string
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $credentials = new ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    storage_path('app/firebase/service-account.json')
                );

                $httpHandler = HttpHandlerFactory::build();
                $token = $credentials->fetchAuthToken($httpHandler);

                if (!isset($token['access_token'])) {
                    throw new \RuntimeException('No access token returned from Google.');
                }

                Log::info('Firebase access token generated and cached', [
                    'expires_in' => $token['expires_in'] ?? 'unknown',
                ]);

                return $token['access_token'];
            } catch (\Exception $e) {
                Log::error('Failed to generate Firebase access token', [
                    'error' => $e->getMessage(),
                ]);
                throw new \RuntimeException('Unable to generate Firebase Access Token: ' . $e->getMessage());
            }
        });
    }

    public function send(Notification $notification): void
    {
        try {
            $tokens = FcmToken::where('student_id', $notification->student_id)
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                $notification->markAsFailed('No active FCM token.');
                Log::info('No active FCM tokens found', [
                    'notification_id' => $notification->id,
                    'student_id' => $notification->student_id,
                ]);
                return;
            }

            // Get access token once for all tokens
            $accessToken = $this->getAccessToken();
            
            $successCount = 0;
            $failedTokens = [];

            foreach ($tokens as $token) {
                try {
                    $this->sendToToken($accessToken, $token, $notification);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedTokens[] = [
                        'token' => $this->maskToken($token),
                        'error' => $e->getMessage(),
                    ];
                    
                    $this->handleTokenError($token, $e);
                }
            }

            // Log summary
            Log::info('Firebase notification completed', [
                'notification_id' => $notification->id,
                'student_id' => $notification->student_id,
                'total_tokens' => count($tokens),
                'success_count' => $successCount,
                'failed_count' => count($failedTokens),
            ]);

            if ($successCount === 0) {
                $notification->markAsFailed('All FCM tokens failed.');
            } else {
                $notification->markAsSent();
                
                if ($successCount < count($tokens)) {
                    Log::warning('Partial success', [
                        'notification_id' => $notification->id,
                        'success_count' => $successCount,
                        'total_tokens' => count($tokens),
                    ]);
                }
            }

        } catch (\Throwable $e) {
            Log::error('Firebase notification failed', [
                'notification_id' => $notification->id ?? null,
                'student_id' => $notification->student_id ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $notification->markAsFailed($e->getMessage());
        }
    }

    private function sendToToken(
        string $accessToken,
        string $deviceToken,
        Notification $notification
    ): void {
        $endpoint = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $this->projectId
        );

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $notification->title,
                    'body' => $notification->body,
                ],
                'data' => [
                    'notification_id' => (string) $notification->id,
                    'type' => $notification->type,
                    'timestamp' => (string) now()->timestamp,
                ],
            ],
        ];

        // Add platform-specific configs only if needed
        $payload['message']['apns'] = [
            'payload' => [
                'aps' => [
                    'sound' => 'default',
                    // 'badge' => 1, // Removed - Flutter app handles badge internally
                ],
            ],
        ];

        $payload['message']['android'] = [
            'notification' => [
                'sound' => 'default',
                // 'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Optional for FlutterFire
            ],
        ];

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $statusCode = $response->getStatusCode();
        $responseBody = json_decode($response->getBody()->getContents(), true);

        if ($statusCode !== 200) {
            $errorMessage = $responseBody['error']['message'] ?? 'Unknown error';
            $errorCode = $responseBody['error']['status'] ?? 'UNKNOWN';
            
            Log::error('Firebase API error', [
                'status_code' => $statusCode,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'notification_id' => $notification->id,
                'token' => $this->maskToken($deviceToken),
            ]);

            throw new \RuntimeException(
                sprintf('Firebase API error: [%s] %s', $errorCode, $errorMessage),
                $statusCode
            );
        }

        // Log only the message name (not full response)
        Log::info('FCM message sent', [
            'notification_id' => $notification->id,
            'token' => $this->maskToken($deviceToken),
            'message_name' => $responseBody['name'] ?? 'unknown',
        ]);
    }

    /**
     * Handle token errors and deactivate invalid tokens
     */
    private function handleTokenError(string $token, \Exception $exception): void
    {
        $errorMessage = $exception->getMessage();
        $shouldDeactivate = false;

        // Check for permanent token errors (only 404 and specific FCM codes)
        if ($exception->getCode() === 404) {
            $shouldDeactivate = true;
        }

        // Check FCM error codes
        foreach (self::INVALID_TOKEN_ERRORS as $errorCode) {
            if (strpos($errorMessage, $errorCode) !== false) {
                $shouldDeactivate = true;
                break;
            }
        }

        if ($shouldDeactivate) {
            try {
                FcmToken::where('token', $token)
                    ->update([
                        'is_active' => false,
                        'updated_at' => now(),
                    ]);

                Log::info('FCM token deactivated', [
                    'token' => $this->maskToken($token),
                    'reason' => $exception->getMessage(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to deactivate FCM token', [
                    'token' => $this->maskToken($token),
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            // Log but don't deactivate (401, rate limit, etc.)
            Log::warning('FCM token error (not deactivated)', [
                'token' => $this->maskToken($token),
                'error' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ]);
        }
    }

    /**
     * Mask token for logging purposes
     */
    private function maskToken(string $token): string
    {
        return substr($token, 0, 10) . '...' . substr($token, -6);
    }
}