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
        'status',
        'reason',
        'is_updated',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'is_updated' => 'boolean',
    ];

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

    // 🔥 percentage helper
    public function getPercentageAttribute()
    {
        if (!$this->marks || !$this->max_marks) {
            return 0;
        }

        return round(($this->marks / $this->max_marks) * 100, 2);
    }
}