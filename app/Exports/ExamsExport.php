<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Exam::with([
            'studentClass',
            'category',
            'hall',
            'results'
        ])->get()->map(function ($exam) {
            return [
                'title'       => $exam->title,
                'class'       => $exam->studentClass->class_name ?? 'N/A',
                'category'    => $exam->category->category_name ?? 'N/A',
                'hall'        => $exam->hall->hall_name ?? 'N/A',
                'exam_date'   => $exam->exam_date?->format('Y-m-d'),
                'start_time'  => $exam->start_time,
                'end_time'    => $exam->end_time,
                'status'      => ucfirst($exam->status),
                'results'     => $exam->results->count(),
                'created_at'  => $exam->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Title',
            'Class',
            'Category',
            'Hall',
            'Exam Date',
            'Start Time',
            'End Time',
            'Status',
            'Results Count',
            'Created At'
        ];
    }
}
