<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_name',
        'institute_code',
        'subdomain',
        'web_url',
        'database_name',
        'contact_person',
        'contact_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Institute has one mobile app
     */
    public function mobileApp()
    {
        return $this->hasOne(MobileApp::class);
    }

    /**
     * Scope active institutes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get full admin URL
     */
    public function getAdminUrlAttribute()
    {
        return rtrim($this->web_url, '/') . '/admin';
    }

    /**
     * Check if institute is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}