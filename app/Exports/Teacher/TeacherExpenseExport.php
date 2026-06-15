<?php

namespace App\Exports\Teacher;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherExpenseExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected Collection $payments;
    protected $teacherId;
    protected $year;
    protected $month;

    public function __construct(Collection $payments, $teacherId, $year, $month)
    {
        $this->payments = $payments;
        $this->teacherId = $teacherId;
        $this->year = $year;
        $this->month = $month;
    }

    public function collection()
    {
        return $this->payments;
    }

    public function headings(): array
    {
        return [
            'Payment ID',
            'Teacher Name',
            'Payment Type',
            'Amount',
            'Payment Date',
            'Reason',
            'Note',
            'Status',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            optional($payment->user)->name,
            $payment->payment_type,
            $payment->amount,
            optional($payment->payment_date)->toDateString(),
            $payment->payment_method,
            $payment->reason,
            $payment->note,
            $payment->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}