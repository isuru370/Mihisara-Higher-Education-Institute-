<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'amount',
        'is_active',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(AdmissionPayment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}