<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'token',
        'device_name',
        'device_type',
        'app_version',
        'is_active',
        'last_login_at',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the student that owns this FCM token.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
