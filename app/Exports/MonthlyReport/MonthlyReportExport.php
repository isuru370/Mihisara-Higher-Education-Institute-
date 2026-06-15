<?php

namespace App\Exports\MonthlyReport;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonthlyReportExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected Collection $rows;
    protected array $summary;
    protected string $title;

    public function __construct(array $rows, array $summary, string $title = 'Monthly Report')
    {
        $this->rows = collect($rows);
        $this->summary = $summary;
        $this->title = $title;
    }

    public function collection()
    {
        return $this->rows->map(function ($row) {
            return [
                'date' => $row['date'] ?? '',
                'payment_total' => $row['payment_total'] ?? 0,
                'admission_total' => $row['admission_total'] ?? 0,
                'extra_income_total' => $row['extra_income_total'] ?? 0,
                'teacher_expense_total' => $row['teacher_expense_total'] ?? 0,
                'organizer_expense_total' => $row['organizer_expense_total'] ?? 0,
                'institute_expense_total' => $row['institute_expense_total'] ?? 0,
                'net_total' => $row['net_total'] ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Payment Total',
            'Admission Total',
            'Extra Income Total',
            'Teacher Expense Total',
            'Organizer Expense Total',
            'Institute Expense Total',
            'Net Total',
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}