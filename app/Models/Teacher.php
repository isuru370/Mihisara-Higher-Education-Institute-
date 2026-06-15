<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'custom_id',
        'full_name',
        'initials',
        'email',
        'mobile',
        'nic',
        'bday',
        'gender',
        'address1',
        'address2',
        'address3',
        'is_active',
        'graduation_details',
        'experience',
        'account_number',
        'bank_branch_id',
    ];

    protected $casts = [
        'bday' => 'date',
        'is_active' => 'boolean',
    ];

    public function bankBranch()
    {
        return $this->belongsTo(BankBranch::class);
    }

    public function classes()
    {
        return $this->hasMany(StudentClass::class);
    }

    public function schedules()
    {
        return $this->hasManyThrough(
            ClassSchedule::class,
            StudentClass::class
        );
    }

    
}
