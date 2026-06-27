<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

class StudentPortalLogin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'institute_code',
        'username',
        'password',
        'is_verified',
        'is_active',
        'otp',
        'otp_expires_at',
        'last_login_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'otp_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->institute_code)) {
                $model->institute_code = config('services.institute.code');
            }
        });
    }

    // 🔐 auto hash password
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}