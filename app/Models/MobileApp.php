<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'app_name',
        'package_name',
        'current_version',
        'build_number',
        'latest_version',
        'min_supported_version',
        'api_url',
        'apk_url',
        'apk_file_name',
        'force_update',
        'release_notes',
        'last_release_date',
        'status',
    ];

    protected $casts = [
        'force_update' => 'boolean',
        'last_release_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mobile app belongs to institute
     */
    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * Scope active apps
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if force update enabled
     */
    public function requiresForceUpdate(): bool
    {
        return $this->force_update;
    }

    /**
     * Get version display
     */
    public function getVersionLabelAttribute(): string
    {
        return "{$this->current_version} ({$this->build_number})";
    }

    /**
     * Check app status
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}