<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_name',
        'code',
        'is_schedulable',
        'is_active',
    ];

    protected $casts = [
        'is_schedulable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // 🔹 default fees per class
    public function classCategoryFees()
    {
        return $this->hasMany(ClassCategoryFee::class, 'class_category_id');
    }

    // 🔹 students enrolled in this category
    public function enrollments()
    {
        return $this->hasMany(StudentClassEnrollment::class, 'class_category_id');
    }

    // 🔹 classes that use this category
    public function classes()
    {
        return $this->belongsToMany(
            StudentClass::class,
            'class_category_fees',
            'class_category_id',
            'student_class_id'
        );
    }
}
