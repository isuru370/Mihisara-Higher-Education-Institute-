<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCard extends Model
{
    protected $fillable = [
        'student_id',
        'card_sequence',
        'card_number',
        'qr_code',
        'status',
        'is_current',
        'issued_at',
        'deactivated_at',
        'remarks',
    ];

    protected $casts = [
        'is_current'      => 'boolean',
        'issued_at'       => 'datetime',
        'deactivated_at'  => 'datetime',
    ];

    /**
     * Get the student assigned to this card.
     */
    public function student(): BelongsTo
{
    return $this->belongsTo(Student::class, 'student_id');
}

    public function histories()
{
    return $this->hasMany(StudentCardHistory::class, 'card_id');
}
}
