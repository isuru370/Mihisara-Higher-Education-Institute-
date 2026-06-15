<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'user_id',
        'payment_type',
        'amount',
        'payment_date',
        'reason_code',
        'reason',
        'status',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentReason()
    {
        return $this->belongsTo(
            PaymentReason::class,
            'reason_code',
            'reason_code'
        );
    }

    // 🔥 helpers

    public function isAdvance()
    {
        return $this->payment_type === 'advance';
    }

    public function isDeduction()
    {
        return $this->payment_type === 'deduction';
    }

    public function isOther()
    {
        return $this->payment_type === 'other';
    }
}