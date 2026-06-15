<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemUser extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'custom_id',
        'user_id',
        'full_name',
        'mobile',
        'nic',
        'bday',
        'gender',
        'address1',
        'address2',
        'address3',
        'is_active',
        'note',
    ];

    protected $casts = [
        'bday' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}