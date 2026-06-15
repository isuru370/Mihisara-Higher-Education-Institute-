<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstitutePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'amount',
        'payment_date',
        'reason',
        'reason_code',
        'payment_type',
        'status',
        'user_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🔥 helpers

    public function isExpense()
    {
        return $this->payment_type === 'expense';
    }

    public function isIncome()
    {
        return $this->payment_type === 'income';
    }
}
