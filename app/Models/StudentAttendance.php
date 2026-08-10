<?php

namespace App\Models;

use App\Observers\StudentAttendanceObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAttendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'local_uuid',
        'student_id',
        'class_schedule_id',
        'student_class_enrollment_id',
        'attended_at',
        'mark_method',
        'marked_by',
        'is_synced',
        'note',
    ];

    protected $casts = [
        'attended_at' => 'datetime',
        'is_synced' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::observe(StudentAttendanceObserver::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(
            StudentClassEnrollment::class,
            'student_class_enrollment_id'
        );
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}