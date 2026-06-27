<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'custom_id',
        'temporary_qr_code',
        'temporary_qr_code_expire_date',
        'full_name',
        'initial_name',
        'mobile',
        'whatsapp_mobile',
        'email',
        'nic',
        'bday',
        'gender',
        'address1',
        'address2',
        'address3',
        'guardian_fname',
        'guardian_lname',
        'guardian_nic',
        'guardian_mobile',
        'grade_id',
        'class_type',
        'admission',
        'student_school',
        'img_url',
        'last_image_update_at',
        'is_active',
        'permanent_qr_active',
        'student_disable',
    ];

    protected $casts = [
        'temporary_qr_code_expire_date' => 'datetime',
        'bday' => 'date',
        'last_image_update_at' => 'datetime',
        'admission' => 'boolean',
        'is_active' => 'boolean',
        'permanent_qr_active' => 'boolean',
        'student_disable' => 'boolean',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function temporaryIdCard()
    {
        return $this->hasOne(TemporaryIdCard::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentClassEnrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function admissionPayments()
    {
        return $this->hasMany(AdmissionPayment::class);
    }

    public function portalLogin()
    {
        return $this->hasOne(StudentPortalLogin::class);
    }

    public function results()
    {
        return $this->hasMany(StudentResult::class);
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    public function mobileDevices()
    {
        return $this->hasMany(MobileDevice::class);
    }
}
