<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassHall extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'hall_name',
        'hall_type',
        'hall_price',
        'is_active',
    ];

    protected $casts = [
        'hall_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}