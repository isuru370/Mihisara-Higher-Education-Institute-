<?php

namespace App\Exports\Receipts;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReceiptsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected Collection $receipts;

    public function __construct(Collection $receipts)
    {
        $this->receipts = $receipts;
    }

    public function collection()
    {
        return $this->receipts->map(function ($receipt) {
            return [
                'receipt_number' => $receipt['receipt_number'],
                'type' => $receipt['type'],
                'amount' => $receipt['amount'],
                'date' => $receipt['date']->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Receipt Number',
            'Type',
            'Amount',
            'Date',
        ];
    }
}
