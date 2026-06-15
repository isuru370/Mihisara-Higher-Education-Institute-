<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'student_class_id',
        'class_category_id',
        'class_hall_id',
        'exam_date',
        'start_time',
        'end_time',
        'status',
        'cancel_reason',
        'cancelled_by',
        'cancelled_at',
        'note',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function category()
    {
        return $this->belongsTo(ClassCategory::class, 'class_category_id');
    }

    public function hall()
    {
        return $this->belongsTo(ClassHall::class, 'class_hall_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function results()
    {
        return $this->hasMany(StudentResult::class);
    }

    public function cancel($userId, $reason = null)
    {
        return $this->update([
            'status' => 'cancelled',
            'cancel_reason' => $reason,
            'cancelled_by' => $userId,
            'cancelled_at' => now(),
        ]);
    }
}