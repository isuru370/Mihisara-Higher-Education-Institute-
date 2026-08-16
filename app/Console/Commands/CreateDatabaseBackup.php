<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use App\Services\DatabaseBackupService;
use App\Services\GoogleDriveService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateDatabaseBackup extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Create and upload MySQL database backup to Google Drive';

    public function handle(
        DatabaseBackupService $backupService,
        GoogleDriveService $googleDriveService
    ) {
        $startedAt = now();
        $backupPath = null;

        $log = BackupLog::create([
            'user_id'     => null,
            'action'      => 'automatic',
            'status'      => 'pending',
            'file_name'   => null,
            'ip_address'  => null,
            'user_agent'  => 'Laravel Scheduler',
            'message'     => 'Automatic database backup started.',
            'started_at'  => $startedAt,
            'completed_at'=> null,
        ]);

        $this->info('Starting database backup...');

        try {

            // -------------------------------------------------
            // 1. Create database backup
            // -------------------------------------------------

            $backupPath = $backupService->createBackup();

            $fileName = basename($backupPath);

            $this->info(
                'Database backup created successfully.'
            );

            $this->line(
                'Local backup: ' . $backupPath
            );

            // -------------------------------------------------
            // 2. Upload to Google Drive
            // -------------------------------------------------

            $this->info(
                'Uploading backup to Google Drive...'
            );

            $uploadedFile = $googleDriveService->upload(
                $backupPath,
                $fileName
            );

            $googleDriveFileId = $uploadedFile->getId();

            $this->info(
                'Backup uploaded to Google Drive successfully.'
            );

            $this->line(
                'Google Drive File ID: ' . $googleDriveFileId
            );

            // -------------------------------------------------
            // 3. Delete local backup
            // -------------------------------------------------

            $backupService->deleteBackup(
                $backupPath
            );

            $this->info(
                'Local backup deleted successfully.'
            );

            // -------------------------------------------------
            // 4. Update backup log
            // -------------------------------------------------

            $log->update([
                'status' => 'success',
                'file_name' => $fileName,
                'message' =>
                    'Database backup uploaded to Google Drive successfully. ' .
                    'Google Drive File ID: ' . $googleDriveFileId,
                'completed_at' => now(),
            ]);

            $this->info(
                'Database backup process completed successfully.'
            );

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            // -------------------------------------------------
            // 5. Failure handling
            // -------------------------------------------------

            Log::error(
                'Automatic database backup failed',
                [
                    'error' => $e->getMessage(),
                ]
            );

            $log->update([
                'status' => 'failed',
                'file_name' => $backupPath
                    ? basename($backupPath)
                    : null,
                'message' =>
                    'Automatic database backup failed: ' .
                    $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->error(
                'Database backup failed.'
            );

            $this->error(
                $e->getMessage()
            );

            /*
             * IMPORTANT
             *
             * If Google Drive upload fails,
             * the local backup is NOT deleted.
             */

            if ($backupPath && file_exists($backupPath)) {

                $this->warn(
                    'Local backup was kept because the upload failed.'
                );

                $this->line(
                    'Backup file: ' . $backupPath
                );
            }

            return Command::FAILURE;
        }
    }
}