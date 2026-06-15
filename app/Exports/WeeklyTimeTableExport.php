<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WeeklyTimeTableExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $schedules;
    protected $startOfWeek;
    protected $endOfWeek;

    public function __construct($schedules, $startOfWeek, $endOfWeek)
    {
        $this->schedules = $schedules;
        $this->startOfWeek = $startOfWeek;
        $this->endOfWeek = $endOfWeek;
    }

    public function collection()
    {
        return $this->schedules;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Date',
            'Day',
            'Start Time',
            'End Time',
            'Hall',
            'Class Name',
            'Grade',
            'Category',
            'Fee',
            'Status',
        ];
    }

    public function map($schedule): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $statusText = [
            'scheduled' => 'Scheduled',
            'ongoing'   => 'Ongoing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $classDate = $schedule->class_date ? Carbon::parse($schedule->class_date) : null;
        $startTime  = $schedule->start_time ? Carbon::parse($schedule->start_time) : null;
        $endTime    = $schedule->end_time ? Carbon::parse($schedule->end_time) : null;

        return [
            $rowNumber,
            $classDate ? $classDate->format('Y-m-d') : '-',
            $classDate ? $classDate->format('l') : '-',
            $startTime ? $startTime->format('h:i A') : '-',
            $endTime ? $endTime->format('h:i A') : '-',
            $schedule->hall->hall_name ?? 'N/A',
            $schedule->studentClass->class_name ?? 'N/A',
            $schedule->studentClass->grade->grade_name ?? 'N/A',
            $schedule->classCategoryFee->category->category_name ?? 'N/A',
            number_format($schedule->classCategoryFee->fee ?? 0, 2),
            $statusText[$schedule->status] ?? ucfirst((string) $schedule->status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:K1' => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
