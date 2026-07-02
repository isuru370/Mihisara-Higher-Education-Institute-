<?php

namespace App\Services\Parent\Exam;

use App\Models\Exam;
use App\Models\Student;
use App\Models\StudentClassEnrollment;

class ExamService
{
    public function fetchExams(
        int $studentId
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        $student = Student::query()
            ->select([
                'id',
                'initial_name',
            ])
            ->find($studentId);

        if (!$student) {
            return [
                'status' => false,
                'message' => 'Student not found.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Student Active Classes
        |--------------------------------------------------------------------------
        */

        $classIds = StudentClassEnrollment::query()
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->pluck('student_class_id');

        if ($classIds->isEmpty()) {
            return [
                'status' => true,
                'message' => 'No classes found.',
                'data' => [
                    'summary' => [
                        'total_classes' => 0,
                        'total_exams' => 0,
                    ],
                    'classes' => [],
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Exams
        |--------------------------------------------------------------------------
        */

        $exams = Exam::query()
            ->select([
                'id',
                'title',
                'student_class_id',
                'class_category_id',
                'class_hall_id',
                'exam_date',
                'start_time',
                'end_time',
                'status',
            ])
            ->with([
                'studentClass:id,class_name,subject_id,teacher_id',
                'studentClass.subject:id,subject_name',
                'studentClass.teacher:id,full_name',

                'category:id,category_name',

                'hall:id,hall_name',
            ])
            ->whereIn('student_class_id', $classIds)
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Process Classes
        |--------------------------------------------------------------------------
        */

        $classes = [];
        $totalExams = 0;

        foreach ($exams as $exam) {

            $classId = $exam->student_class_id;

            if (!isset($classes[$classId])) {

                $classes[$classId] = [
                    'class_id' => $classId,
                    'class_name' => $exam->studentClass?->class_name,
                    'subject_name' => $exam->studentClass?->subject?->subject_name,
                    'teacher' => $exam->studentClass?->teacher?->full_name,
                    'exam_count' => 0,
                    'exams' => [],
                ];
            }

            $classes[$classId]['exam_count']++;

            $classes[$classId]['exams'][] = [
                'exam_id' => $exam->id,
                'title' => $exam->title,
                'exam_date' => $exam->exam_date?->format('Y-m-d'),
                'start_time' => $exam->start_time,
                'end_time' => $exam->end_time,
                'status' => $exam->status,

                'category' => [
                    'id' => $exam->category?->id,
                    'name' => $exam->category?->category_name,
                ],

                'hall' => [
                    'id' => $exam->hall?->id,
                    'name' => $exam->hall?->hall_name,
                ],
            ];

            $totalExams++;
        }

        /*
        |--------------------------------------------------------------------------
        | Sort Classes
        |--------------------------------------------------------------------------
        */

        $classes = collect($classes)
            ->sortBy('class_name')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            'status' => true,
            'message' => 'Exam list fetched successfully.',
            'data' => [
                'summary' => [
                    'total_classes' => $classes->count(),
                    'total_exams' => $totalExams,
                ],

                'classes' => $classes,
            ],
        ];
    }
}
