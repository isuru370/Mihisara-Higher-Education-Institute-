<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'device_uuid',
        'device_name',
        'platform',
        'app_version',
        'api_token',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}