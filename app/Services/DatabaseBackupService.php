<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Create a MySQL database backup and return the ZIP path.
     */
    public function createBackup(): string
    {
        $connection = config('database.default');
        $db = config("database.connections.{$connection}");

        $host     = $db['host'];
        $port     = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $tempDir = storage_path('app/backup-temp');

        File::ensureDirectoryExists($tempDir);

        $fileName = 'backup_' . now()->format('Y_m_d_H_i_s');

        $sqlFile = $tempDir . '/' . $fileName . '.sql';
        $zipFile = $tempDir . '/' . $fileName . '.zip';

        /*
        |--------------------------------------------------------------------------
        | MySQL Dump
        |--------------------------------------------------------------------------
        */

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        $process = Process::fromShellCommandline(
            $command,
            null,
            [
                'MYSQL_PWD' => $password,
            ]
        );

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(
                'MySQL dump failed: ' .
                    $process->getErrorOutput()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate SQL file
        |--------------------------------------------------------------------------
        */

        if (!File::exists($sqlFile) || File::size($sqlFile) === 0) {
            throw new \Exception(
                'MySQL dump created an empty SQL file.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create ZIP
        |--------------------------------------------------------------------------
        */

        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
            throw new \Exception(
                'Unable to create backup ZIP file.'
            );
        }

        $zip->addFile(
            $sqlFile,
            basename($sqlFile)
        );

        $zip->close();

        /*
        |--------------------------------------------------------------------------
        | Delete temporary SQL file
        |--------------------------------------------------------------------------
        */

        File::delete($sqlFile);

        /*
        |--------------------------------------------------------------------------
        | Verify ZIP
        |--------------------------------------------------------------------------
        */

        if (!File::exists($zipFile) || File::size($zipFile) === 0) {
            throw new \Exception(
                'Backup ZIP file was not created.'
            );
        }

        return $zipFile;
    }

    /**
     * Delete a local backup file.
     */
    public function deleteBackup(string $filePath): void
    {
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
