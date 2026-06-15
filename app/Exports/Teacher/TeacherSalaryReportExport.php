<?php

namespace App\Exports\Teacher;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TeacherSalaryReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected array $report;
    protected int $year;
    protected int $month;

    public function __construct(array $report, int $year, int $month)
    {
        $this->report = $report;
        $this->year = $year;
        $this->month = $month;
    }

    public function array(): array
    {
        return array_map(function ($row) {
            return [
                $row['teacher_id'],
                $row['custom_id'],
                $row['initials'],
                $row['gross_income'],
                $row['advance_deduction'],
                $row['salary_paid_status'],
            ];
        }, $this->report);
    }

    public function headings(): array
    {
        return [
            'Teacher ID',
            'Custom ID',
            'Initials',
            'Gross Income',
            'Advance Deduction',
            'Salary Status',
        ];
    }
}