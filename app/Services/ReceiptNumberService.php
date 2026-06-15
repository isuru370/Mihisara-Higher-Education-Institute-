<?php

namespace App\Services;

use App\Models\ReceiptCounter;
use Illuminate\Support\Facades\DB;

class ReceiptNumberService
{
    public static function generate(): string
    {
        return DB::transaction(function () {

            $counter = ReceiptCounter::lockForUpdate()->first();

            if (!$counter) {

                $counter = ReceiptCounter::create([
                    'last_number' => 0,
                ]);

                $counter = ReceiptCounter::lockForUpdate()->find($counter->id);
            }

            $nextNumber = $counter->last_number + 1;

            $counter->update([
                'last_number' => $nextNumber,
            ]);

            return 'REC-' . str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
        });
    }
}
