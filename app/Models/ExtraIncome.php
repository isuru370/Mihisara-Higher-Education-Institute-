<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExtraIncome extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'amount',
        'income_date',
        'reason',
        'income_type',
        'status',
        'receipt_number', // add this
        'user_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isReceived()
    {
        return $this->status === 'received';
    }
}
