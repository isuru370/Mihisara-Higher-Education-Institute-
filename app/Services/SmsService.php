<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected string $baseUrl;
    protected string $userId;
    protected string $apiKey;
    protected string $senderId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.sms.base_url') ?? 'https://smsapi.chatbiz.net/v1', '/');
        $this->userId = (string) config('services.sms.user_id');
        $this->apiKey = (string) config('services.sms.api_key');
        $this->senderId = (string) config('services.sms.sender_id');
    }

    // Single SMS
    public function sendSms(string $recipient, string $message): array
    {
        try {
            $recipient = $this->formatNumber($recipient);

            $response = Http::timeout(15)->get("{$this->baseUrl}/send", [
                'user_id' => $this->userId,
                'api_key' => $this->apiKey,
                'sender_id' => $this->senderId,
                'recipient_contact_no' => $recipient,
                'message' => $message,
            ]);

            return [
                'success' => ($response->json()['status_code'] ?? null) == 204,
                'http_status' => $response->status(),
                'provider_status_code' => $response->json()['status_code'] ?? null,
                'message_id' => $response->json()['msg_id'] ?? null,
                'body' => $response->body(),
                'json' => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    // Bulk SMS
    public function sendBulkSms(array $numbers, string $message, string $campaign = 'LaravelCampaign'): array
    {
        try {
            $formattedNumbers = array_map(fn($number) => $this->formatNumber((string) $number), $numbers);

            $response = Http::asForm()
                ->timeout(20)
                ->post("{$this->baseUrl}/bulk", [
                    'user_id' => $this->userId,
                    'api_key' => $this->apiKey,
                    'sender_id' => $this->senderId,
                    'campaign_name' => $campaign,
                    'message' => $message,
                    'recipient_contact_no' => implode(',', $formattedNumbers),
                ]);

            return $this->buildResponse($response);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    // OTP SMS
    public function sendOtp(string $number): array
    {
        $otp = (string) random_int(100000, 999999);
        $message = "Your verification code is: {$otp}";

        $smsResponse = $this->sendSms($number, $message);

        return [
            'success' => $smsResponse['success'] ?? false,
            'otp' => $otp,
            'sms_response' => $smsResponse,
        ];
    }

    // Balance
    public function getBalance(): array
    {
        try {
            $response = Http::asForm()
                ->timeout(15)
                ->get("{$this->baseUrl}/getBalance", [
                    'user_id' => $this->userId,
                    'api_key' => $this->apiKey,
                ]);

            return $this->buildResponse($response);
        } catch (\Throwable $e) {
            return $this->exceptionResponse($e);
        }
    }

    private function buildResponse($response): array
    {
        $body = $response->json();

        if (!is_array($body)) {
            return [
                'success' => false,
                'http_status' => $response->status(),
                'error' => 'Invalid response from SMS provider',
                'body' => $response->body(),
            ];
        }

        $providerStatusCode = $body['status_code'] ?? null;

        if ($providerStatusCode == 211) {
            return [
                'success' => false,
                'http_status' => $response->status(),
                'provider_status_code' => 211,
                'error' => 'No Sender ID / Sender ID is not approved',
                'data' => $body,
            ];
        }

        return [
            'success' => $response->successful(),
            'http_status' => $response->status(),
            'provider_status_code' => $providerStatusCode,
            'data' => $body,
        ];
    }

    private function exceptionResponse(\Throwable $e): array
    {
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }

    // Format Sri Lanka numbers
    private function formatNumber(string $number): string
    {
        $number = preg_replace('/\D+/', '', trim($number));

        if (str_starts_with($number, '0')) {
            return '94' . substr($number, 1);
        }

        if (str_starts_with($number, '94')) {
            return $number;
        }

        return $number;
    }
}
