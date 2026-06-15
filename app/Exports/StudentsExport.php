<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Student::with('grade')
            ->get()
            ->map(function ($student) {
                return [
                    'Custom ID' => $student->custom_id,
                    'QR Code' => $student->permanent_qr_active ? $student->custom_id : $student->temporary_qr_code,
                    'Full Name' => $student->full_name,
                    'Mobile' => $student->mobile,
                    'Guardian Mobile' => $student->guardian_mobile,
                    'Grade' => $student->grade->grade_name ?? 'N/A',
                    'Class Type' => $student->class_type,
                    'Admission' => $student->admission ? 'Paid' : 'Pending',
                    'Status' => $student->is_active ? 'Active' : 'Inactive',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Custom ID',
            'QR Code',
            'Full Name',
            'Mobile',
            'Guardian Mobile',
            'Grade',
            'Class Type',
            'Admission',
            'Status',
        ];
    }
}