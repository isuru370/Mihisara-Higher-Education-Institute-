<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentIdCard extends Model
{
    protected $table = 'student_id_cards';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'student_id',
        'card_no',

        // card status
        'status',

        // registration status
        'registration_status',

        // financial
        'student_fee',
        'print_cost',
        'profit',

        // reissue
        'is_reissue',
        'reissue_from_id',

        // dates
        'downloaded_at',
        'deleted_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'student_fee'        => 'decimal:2',
        'print_cost'         => 'decimal:2',
        'profit'             => 'decimal:2',

        'is_reissue'         => 'boolean',

        'downloaded_at'      => 'datetime',
        'deleted_at'         => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function reissueFrom(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'reissue_from_id'
        );
    }


    public function getCalculatedProfitAttribute(): float
    {
        return (float) $this->student_fee
            - (float) $this->print_cost;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    // Download status
    public function markAsDownloaded(): void
    {
        $this->update([
            'status'        => 'downloaded',
            'downloaded_at' => now(),
        ]);
    }

    // Active status
    public function markAsActive(): void
    {
        $this->update([
            'status' => 'active',
        ]);
    }

    // Delete card
    public function markAsDeleted(): void
    {
        $this->update([
            'status'     => 'deleted',
            'deleted_at' => now(),
        ]);
    }

    // Registration complete
    public function markRegistrationCompleted(): void
    {
        $this->update([
            'registration_status' => 'completed',
        ]);
    }

    // Registration incomplete
    public function markRegistrationIncomplete(): void
    {
        $this->update([
            'registration_status' => 'incomplete',
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function ($card) {

            if (empty($card->card_no)) {

                $lastId = self::max('id') + 1;

                $card->card_no =
                    'SID-' .
                    str_pad($lastId, 5, '0', STR_PAD_LEFT);
            }

            $card->profit =
                $card->student_fee
                - $card->print_cost;
        });

        static::updating(function ($card) {

            $card->profit =
                $card->student_fee
                - $card->print_cost;
        });
    }
}
