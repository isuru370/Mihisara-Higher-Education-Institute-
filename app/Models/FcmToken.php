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
        'device_id',      // ✅ Add this
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

    /**
     * Scope: Active tokens.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Inactive tokens.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Android devices.
     */
    public function scopeAndroid($query)
    {
        return $query->where('device_type', 'android');
    }

    /**
     * Scope: iOS devices.
     */
    public function scopeIos($query)
    {
        return $query->where('device_type', 'ios');
    }

    /**
     * Get device type label.
     */
    public function getDeviceTypeLabelAttribute(): string
    {
        return match ($this->device_type) {
            'android' => 'Android',
            'ios' => 'iOS',
            default => 'Unknown',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Mask token for display.
     */
    public function getMaskedTokenAttribute(): string
    {
        return substr($this->token, 0, 15) . '...' . substr($this->token, -6);
    }

    /**
     * Get device icon.
     */
    public function getDeviceIconAttribute(): string
    {
        return match ($this->device_type) {
            'android' => 'bi-phone',
            'ios' => 'bi-apple',
            default => 'bi-device',
        };
    }
}