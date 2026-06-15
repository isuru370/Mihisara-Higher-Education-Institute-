<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'class_schedule_pattern_id',
        'student_class_id',
        'class_category_fee_id',
        'class_hall_id',
        'class_date',
        'start_time',
        'end_time',
        'day_of_week',
        'status',
        'is_active',
        'cancel_reason',
        'cancelled_by',
        'cancelled_at',
        'note',
    ];

    protected $casts = [
        'class_date' => 'date',
        'cancelled_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // 🔗 Relationships

    public function pattern()
    {
        return $this->belongsTo(ClassSchedulePattern::class, 'class_schedule_pattern_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function classCategoryFee()
    {
        return $this->belongsTo(ClassCategoryFee::class, 'class_category_fee_id');
    }

    public function hall()
    {
        return $this->belongsTo(ClassHall::class, 'class_hall_id');
    }


    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function studentAttendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    // 🔍 Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('class_date', $date);
    }

    // ⚙️ Actions

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
