<?php

namespace App\Console\Commands;

use App\Models\StudentPortalLogin;
use App\Services\BulkPortalCredentialService;
use Illuminate\Console\Command;

class SendPortalCredentials extends Command
{
    /**
     * Command Signature
     */
    protected $signature = 'portal:send-credentials
                            {--student= : Send credentials to a single student username}
                            {--force : Skip confirmation prompt}';

    /**
     * Command Description
     */
    protected $description = 'Reset student portal passwords and send login credentials to parents';

    /**
     * Execute the console command.
     */
    public function handle(BulkPortalCredentialService $service)
    {
        $student = $this->option('student');
        $force = $this->option('force');

        // Build query
        $query = StudentPortalLogin::query();

        if ($student) {
            $query->where('username', $student);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('No student portal records found.');

            return Command::SUCCESS;
        }

        $this->newLine();

        $this->info('=========================================');
        $this->info(' Student Portal Credential Sender');
        $this->info('=========================================');

        if ($student) {
            $this->line("Mode      : Single Student");
            $this->line("Username  : {$student}");
        } else {
            $this->line("Mode      : Bulk");
        }

        $this->line("Total      : {$total}");

        $this->newLine();

        if (!$force) {

            if (!$this->confirm(
                'This will generate NEW passwords and send SMS. Continue?'
            )) {

                $this->warn('Operation cancelled.');

                return Command::SUCCESS;
            }
        }

        $bar = $this->output->createProgressBar($total);

        $bar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%%'
        );

        $bar->start();

        $start = microtime(true);

        try {

            $result = $service->sendCredentials(
                $student,
                function () use ($bar) {
                    $bar->advance();
                }
            );

            $bar->finish();

            $this->newLine(2);

            $time = round(microtime(true) - $start, 2);

            $this->info('=========================================');
            $this->info(' Completed');
            $this->info('=========================================');

            $this->table(
                ['Result', 'Count'],
                [
                    ['Success', $result['success']],
                    ['Failed', $result['failed']],
                    ['Skipped', $result['skipped']],
                    ['Processed', $total],
                    ['Time (Seconds)', $time],
                ]
            );

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            $bar->clear();

            $this->newLine();

            $this->error('Command failed.');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}