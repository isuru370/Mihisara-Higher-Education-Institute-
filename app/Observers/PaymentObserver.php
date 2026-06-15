<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Payment;

class PaymentObserver
{
    /**
     * CREATE
     */
    public function created(Payment $payment): void
    {
        ActivityLog::create([
            'table_name' => $payment->getTable(),
            'record_id'  => $payment->id,
            'action'     => 'created',
            'new_values' => $payment->toArray(),
            'user_id'    => auth()->id(),
        ]);
    }

    /**
     * UPDATE
     */
    public function updated(Payment $payment): void
    {
        ActivityLog::create([
            'table_name' => $payment->getTable(),
            'record_id'  => $payment->id,
            'action'     => 'updated',
            'old_values' => $payment->getOriginal(),
            'new_values' => $payment->getChanges(),
            'user_id'    => auth()->id(),
        ]);
    }

    /**
     * FORCE DELETE
     */
    public function forceDeleted(Payment $payment): void
    {
        ActivityLog::create([
            'table_name' => $payment->getTable(),
            'record_id'  => $payment->id,
            'action' => 'force_deleted',
            'old_values' => [
                'payment' => $payment->toArray(),
            ],
            'new_values' => null,
            'user_id' => auth()->id(),
        ]);
    }
}
