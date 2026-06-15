<?php

namespace App\Exports\Teacher;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeacherWithStudentPaymentDateReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private array $report,
        private int $year,
        private int $month
    ) {
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->report['classes'] as $class) {
            foreach ($class['categories'] as $category) {
                foreach ($category['students']['paid'] as $student) {
                    $rows[] = $this->makeRow($class, $category, $student);
                }

                foreach ($category['students']['partial'] as $student) {
                    $rows[] = $this->makeRow($class, $category, $student);
                }

                foreach ($category['students']['unpaid'] as $student) {
                    $rows[] = $this->makeRow($class, $category, $student);
                }

                foreach ($category['students']['freecard'] as $student) {
                    $rows[] = $this->makeRow($class, $category, $student);
                }
            }
        }

        return new Collection($rows);
    }

    private function makeRow(array $class, array $category, array $student): array
    {
        return [
            'year' => $this->year,
            'month' => $this->month,

            'teacher_id' => $this->report['teacher']['id'],
            'teacher_custom_id' => $this->report['teacher']['custom_id'],
            'teacher_initials' => $this->report['teacher']['initials'],

            'class_name' => $class['class_name'],
            'grade_name' => $class['grade_name'],

            'category_name' => $category['category_name'],
            'class_fee' => $category['fee'],

            'student_code' => $student['student_code'],
            'initial_name' => $student['initial_name'],
            'guardian_mobile' => $student['guardian_mobile'],

            'status' => $student['status'],
            'is_free_card' => $student['is_free_card'] ? 'Yes' : 'No',

            'custom_fee' => $student['custom_fee'],
            'discount_percentage' => $student['discount_percentage'],
            'final_fee' => $student['final_fee'],
            'paid_amount' => $student['paid_amount'],
            'balance' => $student['balance'],
        ];
    }

    public function headings(): array
    {
        return [
            'Year',
            'Month',
            'Teacher ID',
            'Teacher Code',
            'Teacher Initials',
            'Class',
            'Grade',
            'Category',
            'Class Fee',
            'Student Code',
            'Student Name',
            'Guardian Mobile',
            'Status',
            'Free Card',
            'Custom Fee',
            'Discount %',
            'Final Fee',
            'Paid Amount',
            'Balance',
        ];
    }
}