<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ZipArchive;

class DatabaseBackupController extends Controller
{
    // ✅ Max upload size: 200MB
    private const MAX_BACKUP_SIZE_MB = 200;

    public function index()
    {
        $logs = BackupLog::latest()->paginate(20);
        return view('admin.setting.index', compact('logs'));
    }

    public function export(Request $request)
    {
        $log = BackupLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'export',
            'status'     => 'pending',
            'message'    => 'Database backup export started.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'started_at' => now(),
        ]);

        try {

            $connection = config('database.default');
            $db = config("database.connections.{$connection}");

            $host     = $db['host'];
            $port     = $db['port'];
            $database = $db['database'];
            $username = $db['username'];
            $password = $db['password'];

            $tempDir = storage_path('app/backup-temp');

            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            $fileName = 'backup_' . now()->format('Y_m_d_H_i_s');

            $sqlFile = $tempDir . '/' . $fileName . '.sql';

            $command = sprintf(
                'mysqldump --skip-ssl --host=%s --port=%s --user=%s %s > %s',
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
                    $process->getErrorOutput()
                );
            }

            $zipFile = $tempDir . '/' . $fileName . '.zip';

            $zip = new ZipArchive();

            if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
                throw new \Exception('Unable to create zip file.');
            }

            $zip->addFile(
                $sqlFile,
                basename($sqlFile)
            );

            $zip->close();

            File::delete($sqlFile);

            $log->update([
                'status'       => 'success',
                'file_name'    => basename($zipFile),
                'message'      => 'Database backup exported successfully.',
                'completed_at' => now(),
            ]);

            return response()->download(
                $zipFile,
                basename($zipFile)
            )->deleteFileAfterSend(true);
        } catch (\Throwable $e) {

            Log::error('Backup Export Failed', [
                'message' => $e->getMessage(),
            ]);

            $log->update([
                'status'       => 'failed',
                'message'      => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return back()->with(
                'error',
                'Database export failed.'
            );
        }
    }

    public function import(Request $request)
    {
        // ✅ Validate file type + size
        $maxKilobytes = self::MAX_BACKUP_SIZE_MB * 1024;

        $request->validate([
            'backup_file' => [
                'required',
                'file',
                'mimes:zip',
                "max:{$maxKilobytes}",
            ],
        ]);

        // ✅ Log starts as 'pending'
        $log = BackupLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'import',
            'status'     => 'pending',
            'message'    => 'Database backup import started.',
            'file_name'  => $request->file('backup_file')->getClientOriginalName(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'started_at' => now(),
        ]);

        $tempDir = storage_path('app/backup-temp/import-' . now()->format('YmdHis') . '-' . uniqid());
        File::ensureDirectoryExists($tempDir);

        try {
            $zipPath = $request->file('backup_file')->getRealPath();
            $zip = new ZipArchive();

            if ($zip->open($zipPath) !== true) {
                $log->update([
                    'status'       => 'failed',
                    'message'      => 'Unable to open backup zip file.',
                    'completed_at' => now(),
                ]);
                return back()->with('error', 'Unable to open backup zip file.');
            }

            // ✅ Path Traversal Attack Protection
            // ZIP entries වල "../" වැනි dangerous paths තිබේදැයි check කරනවා
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                $resolvedPath = realpath($tempDir) . DIRECTORY_SEPARATOR . $entryName;

                // entry path tempDir ඇතුළේ නැත්නම් reject කරනවා
                if (!str_starts_with(
                    realpath(dirname($resolvedPath)) ?: '',
                    realpath($tempDir)
                )) {
                    $zip->close();
                    $log->update([
                        'status'       => 'failed',
                        'message'      => 'Malicious ZIP entry detected: ' . $entryName,
                        'completed_at' => now(),
                    ]);
                    return back()->with('error', 'Invalid backup file. Upload rejected.');
                }
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $sqlFile = $this->findFirstSqlFile($tempDir);

            if (!$sqlFile) {
                $log->update([
                    'status'       => 'failed',
                    'message'      => 'SQL dump not found inside backup zip.',
                    'completed_at' => now(),
                ]);
                return back()->with('error', 'SQL dump not found inside backup zip.');
            }

            // ✅ SQL file content basic validation (SQL injection via file name prevent)
            if (!$this->isValidSqlFile($sqlFile)) {
                $log->update([
                    'status'       => 'failed',
                    'message'      => 'SQL file content validation failed.',
                    'completed_at' => now(),
                ]);
                return back()->with('error', 'Invalid SQL file content.');
            }

            $connection = config('database.default');
            $db = config("database.connections.{$connection}");

            $host     = $db['host']     ?? '127.0.0.1';
            $port     = $db['port']     ?? 3306;
            $database = $db['database'] ?? '';
            $username = $db['username'] ?? '';
            $password = $db['password'] ?? '';

            // ✅ Password shell argument ලෙස pass නොකර env variable ලෙස pass කරනවා
            $command = sprintf(
                'mysql --host=%s --port=%s --user=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );

            $process = Process::fromShellCommandline($command, null, [
                'MYSQL_PWD' => $password,
            ]);

            $process->setTimeout(300); // ✅ 5 min timeout (null නෙමේ — server hang prevent)
            $process->run();

            if (!$process->isSuccessful()) {
                $log->update([
                    'status'       => 'failed',
                    'message'      => 'Import failed: ' . $process->getErrorOutput(),
                    'completed_at' => now(),
                ]);
                return back()->with('error', 'Database import failed.');
            }

            $log->update([
                'status'       => 'success',
                'message'      => 'Database backup imported successfully.',
                'completed_at' => now(),
            ]);

            return back()->with('success', 'Database imported successfully.');
        } catch (\Throwable $e) {
            $log->update([
                'status'       => 'failed',
                'message'      => 'Import failed: ' . $e->getMessage(),
                'completed_at' => now(),
            ]);
            return back()->with('error', 'Database import failed.');
        } finally {
            // ✅ Temp files සෑම විටම cleanup වෙනවා
            File::deleteDirectory($tempDir);
        }
    }

    // ✅ Recursive SQL file finder
    private function findFirstSqlFile(string $directory): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.sql')) {
                return $file->getRealPath();
            }
        }

        return null;
    }

    // ✅ SQL file basic validation — empty file හෝ binary file reject කරනවා
    private function isValidSqlFile(string $filePath): bool
    {
        if (!is_readable($filePath)) {
            return false;
        }

        $size = filesize($filePath);

        // Empty file reject
        if ($size === 0) {
            return false;
        }

        // First 512 bytes read කරලා text content දැයි check කරනවා
        $handle = fopen($filePath, 'rb');
        $sample = fread($handle, 512);
        fclose($handle);

        // Binary content (null bytes) තිබේනම් reject
        if (str_contains($sample, "\x00")) {
            return false;
        }

        return true;
    }
}
