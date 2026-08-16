<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use Illuminate\Console\Command;

class TestGoogleDriveUpload extends Command
{
    protected $signature = 'google-drive:test';

    protected $description = 'Test Google Drive upload';

    public function handle(GoogleDriveService $googleDrive)
    {
        $testFile = storage_path(
            'app/backup-test.txt'
        );

        file_put_contents(
            $testFile,
            'Nexora Google Drive backup test - ' . now()
        );

        try {

            $uploaded = $googleDrive->upload(
                $testFile,
                'nexora-google-drive-test.txt'
            );

            $this->info(
                'Upload successful!'
            );

            $this->info(
                'Google Drive File ID: ' .
                $uploaded->getId()
            );

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            $this->error(
                'Upload failed!'
            );

            $this->error(
                $e->getMessage()
            );

            return Command::FAILURE;

        } finally {

            if (file_exists($testFile)) {
                unlink($testFile);
            }
        }
    }
}