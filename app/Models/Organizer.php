<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organizer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'mobile',
        'email',
        'nic',
        'is_active',
        'created_by',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentConfigs()
    {
        return $this->hasMany(ClassPaymentConfig::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}