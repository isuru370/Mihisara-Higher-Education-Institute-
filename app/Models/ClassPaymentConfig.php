<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassPaymentConfig extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_class_id',
        'teacher_id',
        'organizer_id',
        'teacher_percentage',
        'organizer_percentage',
        'institution_percentage',
        'effective_from',
        'effective_to',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'teacher_percentage' => 'decimal:2',
        'organizer_percentage' => 'decimal:2',
        'institution_percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function paymentConfig()
    {
        return $this->hasOne(ClassPaymentConfig::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
