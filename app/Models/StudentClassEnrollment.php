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
        return $this->belongsTo(ClassCategoryFee::class, 'class_category_fee_id');
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

    public function getFinalFeeAttribute()
    {
        if ($this->is_free_card) {
            return 0;
        }

        $baseFee = !is_null($this->custom_fee)
            ? $this->custom_fee
            : $this->getDefaultFee();

        $discount = $this->discount_percentage ?? 0;

        return round($baseFee - ($baseFee * $discount / 100), 2);
    }

    public function getDefaultFee()
    {
        return $this->classCategoryFee?->fee ?? 0;
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceAttribute()
    {
        return max($this->final_fee - $this->paid_amount, 0);
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->paid_amount <= 0) {
            return 'unpaid';
        }

        if ($this->paid_amount < $this->final_fee) {
            return 'partial';
        }

        return 'paid';
    }
}
