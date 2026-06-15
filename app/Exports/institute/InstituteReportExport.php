<?php

namespace App\Exports\institute;

use App\Models\InstitutePayment;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InstituteReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = InstitutePayment::query()
            ->with(['createdBy']);

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween('payment_date', [
                $this->filters['start_date'],
                $this->filters['end_date'],
            ]);
        }

        if (!empty($this->filters['payment_type'])) {
            $query->where('payment_type', $this->filters['payment_type']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('payment_date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Reason',
            'Payment Type',
            'Status',
            'Amount',
            'Created By',
            'Note',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : '-',
            $payment->reason ?? '-',
            ucfirst($payment->payment_type ?? '-'),
            ucfirst($payment->status ?? '-'),
            (float) $payment->amount,
            $payment->createdBy->name ?? '-',
            $payment->note ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
