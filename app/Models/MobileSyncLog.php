<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileSyncLog extends Model
{
    protected $fillable = [
        'mobile_device_id',
        'sync_batch_uuid',
        'sync_type',
        'total_items',
        'success_items',
        'failed_items',
        'status',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(MobileDevice::class, 'mobile_device_id');
    }

    // 🔥 helpers
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }
}