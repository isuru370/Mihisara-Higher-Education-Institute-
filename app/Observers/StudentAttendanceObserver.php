<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\StudentAttendance;

class StudentAttendanceObserver
{
    /**
     * CREATE
     */
    public function created(StudentAttendance $attendance): void
    {
        //
    }

    /**
     * UPDATE
     */
    public function updated(StudentAttendance $attendance): void
    {
        //
    }

    /**
     * FORCE DELETE
     */
    public function forceDeleted(StudentAttendance $attendance): void
    {
        ActivityLog::create([
            'table_name' => $attendance->getTable(),
            'record_id'  => $attendance->id,
            'action'     => 'force_deleted',
            'old_values' => [
                'attendance' => $attendance->toArray(),
            ],
            'new_values' => null,
            'user_id'     => auth()->id(),
        ]);
    }
}