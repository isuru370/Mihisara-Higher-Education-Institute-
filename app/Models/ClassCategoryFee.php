<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassCategoryFee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_class_id',
        'class_category_id',
        'fee',
        'is_active',
        'note',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class, 'student_class_id');
    }

    public function category()
    {
        return $this->belongsTo(ClassCategory::class, 'class_category_id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'class_category_fee_id');
    }
}
