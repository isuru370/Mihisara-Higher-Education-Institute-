<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSchedulePattern extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_class_id',
        'class_category_fee_id',
        'class_hall_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'class_day',
        'is_active',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'class_schedule_pattern_id');
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function categoryFee()
    {
        return $this->belongsTo(ClassCategoryFee::class, 'class_category_fee_id');
    }

    public function hall()
    {
        return $this->belongsTo(ClassHall::class, 'class_hall_id');
    }

    public function pattern()
    {
        return $this->belongsTo(ClassSchedulePattern::class, 'class_schedule_pattern_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
