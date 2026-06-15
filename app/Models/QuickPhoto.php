<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuickPhoto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'custom_id',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // helper
    public function isActive()
    {
        return $this->is_active;
    }
}