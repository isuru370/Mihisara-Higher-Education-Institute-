<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentResult extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'exam_id',
        'user_id',
        'marks',
        'max_marks',
        'percentage',
        'grade',
        'rank',
        'status',
        'reason',
        'remark',
        'is_updated',
        'is_absent',  // ✅ Added
    ];

    protected $casts = [
        'marks' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'rank' => 'integer',
        'is_updated' => 'boolean',
        'is_absent' => 'boolean',  // ✅ Added
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getPercentageAttribute($value)
    {
        if (!is_null($value)) {
            return $value;
        }

        if ($this->is_absent) {
            return null;
        }

        if (!$this->marks || !$this->max_marks) {
            return null;
        }

        return round(
            ($this->marks / $this->max_marks) * 100,
            2
        );
    }

    public function getGradeAttribute($value)
    {
        if ($this->is_absent) {
            return 'ABS';
        }

        return $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function calculateGrade(): string
    {
        if ($this->is_absent) {
            return 'ABS';
        }

        $percentage = $this->percentage;

        if (is_null($percentage)) {
            return 'N/A';
        }

        return match (true) {
            $percentage >= 75 => 'A',
            $percentage >= 65 => 'B',
            $percentage >= 55 => 'C',
            $percentage >= 35 => 'S',
            default => 'F',
        };
    }

    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isAbsent(): bool
    {
        return $this->is_absent || $this->status === 'absent';
    }

    public function hasMarks(): bool
    {
        return !$this->is_absent && !is_null($this->marks);
    }
}