<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherSalary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'user_id',
        'salary_year',
        'salary_month',
        'gross_amount',
        'advance_deduction',
        'other_deduction',
        'bonus_amount',
        'net_amount',
        'status',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'salary_year' => 'integer',
        'salary_month' => 'integer',
        'gross_amount' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFinalNetAmountAttribute()
    {
        return max(
            $this->gross_amount + $this->bonus_amount - $this->advance_deduction - $this->other_deduction,
            0
        );
    }
}