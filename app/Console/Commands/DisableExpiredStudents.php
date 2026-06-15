<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class DisableExpiredStudents extends Command
{
    protected $signature = 'disable:expired-students';
    protected $description = 'Disable students after 2 months if QR inactive';

    public function handle()
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        $updated = Student::where('permanent_qr_active', false)
            ->where('student_disable', false)
            ->where('created_at', '<=', $twoMonthsAgo)
            ->update([
                'student_disable' => true,
                'is_active' => false,
            ]);

        $this->info("Disabled {$updated} expired students successfully.");

        return ConsoleCommand::SUCCESS;
    }
}

