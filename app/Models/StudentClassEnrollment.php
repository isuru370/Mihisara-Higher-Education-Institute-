<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentClassEnrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_class_id',
        'class_category_fee_id',
        'class_category_fee_option_id',
        'is_active',
        'is_free_card',
        'custom_fee',
        'custom_fee_reason',
        'discount_percentage',
        'discount_reason',
        'enrolled_at',
        'left_at',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_free_card' => 'boolean',
        'custom_fee' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'enrolled_at' => 'date',
        'left_at' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function classCategoryFee()
    {
        return $this->belongsTo(
            ClassCategoryFee::class,
            'class_category_fee_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Selected Fee Option
    |--------------------------------------------------------------------------
    */

    public function classCategoryFeeOption()
    {
        return $this->belongsTo(
            ClassCategoryFeeOption::class,
            'class_category_fee_option_id'
        );
    }

    public function category()
    {
        return $this->hasOneThrough(
            ClassCategory::class,
            ClassCategoryFee::class,
            'id',
            'id',
            'class_category_fee_id',
            'class_category_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Final Fee
    |--------------------------------------------------------------------------
    */

    public function getFinalFeeAttribute()
    {
        if ($this->is_free_card) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Custom fee
        |--------------------------------------------------------------------------
        |
        | Keep this for backward compatibility with existing enrollments.
        |
        */

        if (!is_null($this->custom_fee)) {
            $baseFee = $this->custom_fee;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Selected fee option
        |--------------------------------------------------------------------------
        */ elseif (
            !is_null($this->class_category_fee_option_id) &&
            $this->classCategoryFeeOption
        ) {
            $baseFee = $this->classCategoryFeeOption->fee;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Original category fee
        |--------------------------------------------------------------------------
        */ else {
            $baseFee = $this->getDefaultFee();
        }

        $discount = $this->discount_percentage ?: 0;

        return round(
            $baseFee - ($baseFee * $discount / 100),
            2
        );
    }

    public function getDefaultFee()
    {
        if ($this->classCategoryFee) {
            return $this->classCategoryFee->fee;
        }

        return 0;
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return max(
            $this->final_fee - $this->paid_amount,
            0
        );
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->paid_amount >= $this->final_fee) {
            return 'paid';
        }

        return 'unpaid';
    }
}
