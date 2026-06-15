<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Student;

class StudentObserver
{
     public function created(Student $student): void
    {
        ActivityLog::create([
            'table_name' => $student->getTable(),
            'record_id'  => $student->id,
            'action'     => 'created',
            'new_values' => $student->toArray(),
            'user_id'    => auth()->id(),
        ]);
    }

    /**
     * UPDATE
     */
    public function updated(Student $student): void
    {
        ActivityLog::create([
            'table_name' => $student->getTable(),
            'record_id'  => $student->id,
            'action'     => 'updated',
            'old_values' => $student->getOriginal(),
            'new_values' => $student->getChanges(),
            'user_id'    => auth()->id(),
        ]);
    }

    /**
     * DELETE
     */
    public function deleted(Student $student): void
    {
        ActivityLog::create([
            'table_name' => $student->getTable(),
            'record_id'  => $student->id,
            'action'     => 'deleted',
            'old_values' => $student->toArray(),
            'user_id'    => auth()->id(),
        ]);
    }
}
