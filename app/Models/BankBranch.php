<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankBranch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bank_id',
        'branch_name',
        'branch_code',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
}