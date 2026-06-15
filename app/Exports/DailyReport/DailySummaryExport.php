<?php

namespace App\Exports\DailyReport;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailySummaryExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected array $summary;
    protected string $date;

    public function __construct(array $summary, string $date)
    {
        $this->summary = $summary;
        $this->date = $date;
    }

    public function array(): array
    {
        return [
            ['Daily Financial Summary Report'],
            ['Date', \Carbon\Carbon::parse($this->date)->format('d F Y')],
            [''],
            ['Income Summary'],
            ['Student Payments', number_format($this->summary['payment_total'] ?? 0, 2)],
            ['Admission Fees', number_format($this->summary['admission_total'] ?? 0, 2)],
            ['Extra Income', number_format($this->summary['extra_income_total'] ?? 0, 2)],
            ['Total Income', number_format(
                ($this->summary['payment_total'] ?? 0) + 
                ($this->summary['admission_total'] ?? 0) + 
                ($this->summary['extra_income_total'] ?? 0), 2
            )],
            [''],
            ['Expense Summary'],
            ['Teacher Payments', number_format($this->summary['teacher_expense_total'] ?? 0, 2)],
            ['Organizer Payments', number_format($this->summary['organizer_expense_total'] ?? 0, 2)],
            ['Institute Expenses', number_format($this->summary['instituteExpencesTotal'] ?? 0, 2)],
            ['Total Expenses', number_format(
                ($this->summary['teacher_expense_total'] ?? 0) + 
                ($this->summary['organizer_expense_total'] ?? 0) + 
                ($this->summary['instituteExpencesTotal'] ?? 0), 2
            )],
            [''],
            ['NET BALANCE', number_format($this->summary['net_total'] ?? 0, 2)],
            [''],
            ['Generated on: ' . now()->format('d M Y, h:i A')],
        ];
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Daily Summary Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
            9 => ['font' => ['bold' => true, 'size' => 12]],
            15 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}