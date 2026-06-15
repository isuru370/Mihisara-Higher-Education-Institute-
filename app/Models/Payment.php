<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_class_enrollment_id',
        'user_id',
        'mark_method',
        'amount',
        'discount_amount',
        'paid_at',
        'payment_month',
        'payment_method',
        'status',
        'receipt_number',
        'reference_number',
        'is_synced',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_month' => 'date',
        'is_synced' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->paid_at)) {
                $payment->paid_at = now();
            }

            if (empty($payment->payment_month) && $payment->paid_at) {
                $payment->payment_month = date('Y-m-01', strtotime($payment->paid_at));
            }

            if (empty($payment->payment_method)) {
                $payment->payment_method = 'cash';
            }

            if (empty($payment->status)) {
                $payment->status = 'completed';
            }

            if ($payment->discount_amount === null) {
                $payment->discount_amount = 0;
            }

            if ($payment->is_synced === null) {
                $payment->is_synced = true;
            }
        });

        static::created(function ($payment) {
            if ($payment->status !== 'completed') {
                return;
            }

            $payment->loadMissing('enrollment.studentClass');

            $enrollment = $payment->enrollment;
            $studentClass = $enrollment?->studentClass;

            if (!$enrollment || !$studentClass) {
                return;
            }

            $config = ClassPaymentConfig::where('student_class_id', $studentClass->id)
                ->where('is_active', true)
                ->latest()
                ->first();

            if (!$config) {
                return;
            }

            $paymentAmount = (float) $payment->amount;

            $teacherPercentage = (float) ($config->teacher_percentage ?? 0);
            $organizerPercentage = (float) ($config->organizer_percentage ?? 0);
            $institutionPercentage = max(100 - ($teacherPercentage + $organizerPercentage), 0);

            $teacherAmount = round($paymentAmount * $teacherPercentage / 100, 2);
            $organizerAmount = round($paymentAmount * $organizerPercentage / 100, 2);
            $institutionAmount = round($paymentAmount - $teacherAmount - $organizerAmount, 2);

            PaymentSplitSnapshot::create([
                'payment_id' => $payment->id,
                'student_class_id' => $studentClass->id,
                'student_class_enrollment_id' => $enrollment->id,
                'class_payment_config_id' => $config->id,
                'teacher_id' => $config->teacher_id,
                'organizer_id' => $config->organizer_id,
                'created_by' => $payment->user_id,
                'payment_amount' => $paymentAmount,
                'teacher_percentage' => $teacherPercentage,
                'organizer_percentage' => $organizerPercentage,
                'institution_percentage' => $institutionPercentage,
                'teacher_amount' => $teacherAmount,
                'organizer_amount' => $organizerAmount,
                'institution_amount' => $institutionAmount,
                'payment_date' => $payment->paid_at,
            ]);
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(
            StudentClassEnrollment::class,
            'student_class_enrollment_id'
        );
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function splitSnapshot()
    {
        return $this->hasOne(PaymentSplitSnapshot::class);
    }

    public function getExpectedAmountAttribute()
    {
        return $this->enrollment?->final_fee ?? 0;
    }

    public function getBalanceAttribute()
    {
        return max($this->expected_amount - $this->amount, 0);
    }

    public function getIsFullyPaidAttribute()
    {
        return $this->amount >= $this->expected_amount;
    }
}