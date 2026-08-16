<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private Drive $drive;
    private string $folderId;

    public function __construct()
    {
        $client = new Client();

        $client->setClientId(
            config('services.google_drive.client_id')
        );

        $client->setClientSecret(
            config('services.google_drive.client_secret')
        );

        $client->setAccessType('offline');

        $client->addScope(
            Drive::DRIVE_FILE
        );

        $refreshToken = config(
            'services.google_drive.refresh_token'
        );

        if (empty($refreshToken)) {
            throw new \Exception(
                'Google Drive refresh token is missing.'
            );
        }

        $token = $client->fetchAccessTokenWithRefreshToken(
            $refreshToken
        );

        if (isset($token['error'])) {
            throw new \Exception(
                'Google OAuth token refresh failed: ' .
                    json_encode($token)
            );
        }

        if (empty($token['access_token'])) {
            throw new \Exception(
                'Google OAuth access token was not returned.'
            );
        }

        // Explicitly attach the access token
        $client->setAccessToken($token);

        $this->drive = new Drive($client);

        $this->folderId = config(
            'services.google_drive.folder_id'
        );

        if (empty($this->folderId)) {
            throw new \Exception(
                'Google Drive folder ID is missing.'
            );
        }
    }

    /**
     * Upload a file to Google Drive.
     */
    public function upload(
        string $filePath,
        string $fileName
    ): DriveFile {
        if (!file_exists($filePath)) {
            throw new \Exception(
                "Backup file not found: {$filePath}"
            );
        }

        if (empty($this->folderId)) {
            throw new \Exception(
                'Google Drive folder ID is not configured.'
            );
        }

        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$this->folderId],
        ]);

        try {
            $uploadedFile = $this->drive->files->create(
                $fileMetadata,
                [
                    'data' => file_get_contents($filePath),
                    'mimeType' => 'application/zip',
                    'uploadType' => 'multipart',
                    'fields' => 'id,name,size,webViewLink',
                ]
            );

            Log::info('Google Drive backup uploaded', [
                'file_id' => $uploadedFile->getId(),
                'file_name' => $uploadedFile->getName(),
            ]);

            return $uploadedFile;
        } catch (\Throwable $e) {

            Log::error('Google Drive upload failed', [
                'file' => $fileName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
