<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_class_enrollment_id',
        'issued_month',
        'is_issued',
        'issued_at',
        'issued_by',
        'note',
    ];

    protected $casts = [
        'issued_month' => 'date',
        'is_issued' => 'boolean',
        'issued_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(
            StudentClassEnrollment::class,
            'student_class_enrollment_id'
        );
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // helpers
    public function isIssued()
    {
        return $this->is_issued;
    }
}