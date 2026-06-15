<?php

namespace App\Exports;

use App\Models\StudentClass;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentClassesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return StudentClass::with([
                'teacher',
                'subject',
                'grade',
                'paymentConfig.organizer',
            ])
            ->latest()
            ->get()
            ->map(function ($class) {
                return [
                    'Class Name' => $class->class_name,
                    'Class Type' => ucfirst($class->class_type),
                    'Medium' => $class->medium,

                    'Teacher' => $class->teacher->full_name ?? 'N/A',
                    'Teacher ID' => $class->teacher->custom_id ?? 'N/A',

                    'Grade' => $class->grade->grade_name ?? 'N/A',
                    'Subject' => $class->subject->subject_name ?? 'N/A',

                    'Teacher Percentage' => $class->paymentConfig->teacher_percentage ?? '0.00',
                    'Organizer Percentage' => $class->paymentConfig->organizer_percentage ?? '0.00',
                    'Institution Percentage' => $class->paymentConfig->institution_percentage ?? '0.00',

                    'Organizer' => $class->paymentConfig->organizer->name ?? 'None',

                    'Effective From' => optional($class->paymentConfig?->effective_from)->format('Y-m-d') ?? 'N/A',
                    'Effective To' => optional($class->paymentConfig?->effective_to)->format('Y-m-d') ?? 'N/A',

                    'Active Status' => $class->is_active ? 'Active' : 'Inactive',
                    'Ongoing Status' => $class->is_ongoing ? 'Ongoing' : 'Not Ongoing',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Class Name',
            'Class Type',
            'Medium',
            'Teacher',
            'Teacher ID',
            'Grade',
            'Subject',
            'Teacher Percentage',
            'Organizer Percentage',
            'Institution Percentage',
            'Organizer',
            'Effective From',
            'Effective To',
            'Active Status',
            'Ongoing Status',
        ];
    }
}