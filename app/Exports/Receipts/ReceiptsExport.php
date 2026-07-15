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

            'student_id' => $receipt['student_id'],

            'student_name' => $receipt['student_name'],

            'grade' => $receipt['grade'],

            'category' => $receipt['category'],

            'class_name' => $receipt['class_name'],

            'teacher_name' => $receipt['teacher_name'],

            'payment_month' => optional(
                $receipt['payment_month']
            )->format('F Y'),

            'paid_at' => optional(
                $receipt['paid_at']
            )->format('d-m-Y h:i A'),

            'amount' => number_format(
                (float) $receipt['amount'],
                2
            ),

            'date' => optional(
                $receipt['date']
            )->format('d-m-Y h:i A'),

            'status' => $receipt['status'],
        ];
    });
}

    public function headings(): array
    {
        return [

            'Receipt Number',

            'Receipt Type',

            'Student ID',

            'Student Name',

            'Grade',

            'Category',

            'Class',

            'Teacher',

            'Payment Month',

            'Paid At',

            'Amount',

            'Receipt Date',

            'Status',
        ];
    }
}