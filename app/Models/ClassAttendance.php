<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAttendance extends Model
{
    use HasFactory;

    protected $table = 'class_attendances';

    protected $appends = ['classAttendanceId'];

    protected $fillable = [
        'start',
        'end',
        'status',
        'class_category_has_student_class_id',
        'start_time',
        'end_time',
        'day_of_week',
        'is_ongoing',
        'class_hall_id',
        'date'
    ];

    protected $casts = [
        'id'       => 'integer',

        // Foreign keys
        'class_category_has_student_class_id' => 'integer',
        'class_hall_id' => 'integer',

        // Status & flags
        'status'     => 'integer',
        'is_ongoing' => 'boolean',

        // Date & time
        'date'       => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getClassAttendanceIdAttribute()
    {
        return $this->id;
    }

}
