<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSplitSnapshot extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'payment_id',
        'student_class_id',
        'student_class_enrollment_id',
        'class_payment_config_id',
        'teacher_id',
        'organizer_id',
        'created_by',
        'payment_amount',
        'teacher_percentage',
        'organizer_percentage',
        'institution_percentage',
        'teacher_amount',
        'organizer_amount',
        'institution_amount',
        'payment_date',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'teacher_percentage' => 'decimal:2',
        'organizer_percentage' => 'decimal:2',
        'institution_percentage' => 'decimal:2',
        'teacher_amount' => 'decimal:2',
        'organizer_amount' => 'decimal:2',
        'institution_amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function studentClass()
    {
        return $this->belongsTo(StudentClass::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(StudentClassEnrollment::class, 'student_class_enrollment_id');
    }

    public function paymentConfig()
    {
        return $this->belongsTo(ClassPaymentConfig::class, 'class_payment_config_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Organizer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
