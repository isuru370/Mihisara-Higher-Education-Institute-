<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // 🔥 helpers

    public function isExpired()
    {
        return now()->gt($this->expires_at);
    }

    public function isVerified()
    {
        return !is_null($this->verified_at);
    }
}