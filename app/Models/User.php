<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function setPasswordAttribute($value)
    {
        if (!empty($value) && strlen($value) !== 60) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function systemUser()
    {
        return $this->hasOne(SystemUser::class);
    }

    

    public function userType()
    {
        return $this->belongsTo(UserType::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
