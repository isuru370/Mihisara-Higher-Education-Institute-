<?php

namespace App\Exports;

use App\Models\Exam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamResultsExport implements FromCollection, WithHeadings
{
    protected $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function collection()
    {
        return $this->exam->results()
            ->with('student')
            ->orderByDesc('marks')
            ->get()
            ->map(function ($result) {

                return [
                    'student'    => $result->student->full_name ?? 'N/A',
                    'marks'      => $result->marks,
                    'max_marks'  => $result->max_marks,
                    'percentage' => $result->percentage,
                    'grade'      => $result->grade,
                    'rank'       => $result->rank,
                    'status'     => ucfirst($result->status),
                    'remark'     => $result->remark,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Student',
            'Marks',
            'Max Marks',
            'Percentage',
            'Grade',
            'Rank',
            'Status',
            'Remark',
        ];
    }
}