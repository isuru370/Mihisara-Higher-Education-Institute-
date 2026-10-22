<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassCategoryFeeOption extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'class_category_fee_id',
        'label',
        'fee',
        'is_default',
        'is_active',
        'note',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function classCategoryFee()
    {
        return $this->belongsTo(
            ClassCategoryFee::class,
            'class_category_fee_id'
        );
    }

    public function enrollments()
    {
        return $this->hasMany(
            StudentClassEnrollment::class,
            'class_category_fee_option_id'
        );
    }
}