<?php

namespace App\Services\Parent\Result;

use App\Models\Exam;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentResult;

class ResultService
{
    public function fetchResult(
        int $studentId,
        int $examId
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
        | Exam
        |--------------------------------------------------------------------------
        */

        $exam = Exam::query()
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
            ->find($examId);

        if (!$exam) {
            return [
                'status' => false,
                'message' => 'Exam not found.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Student Enrollment
        |--------------------------------------------------------------------------
        */

        $isEnrolled = StudentClassEnrollment::query()
            ->where('student_id', $studentId)
            ->where('student_class_id', $exam->student_class_id)
            ->where('is_active', true)
            ->exists();

        if (!$isEnrolled) {
            return [
                'status' => false,
                'message' => 'Student is not enrolled in this class.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Check Results Published
        |--------------------------------------------------------------------------
        */

        $resultsPublished = StudentResult::query()
            ->where('exam_id', $examId)
            ->exists();

        if (!$resultsPublished) {
            return [
                'status' => false,
                'message' => 'Results are not published yet.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Student Result
        |--------------------------------------------------------------------------
        */

        $studentResult = StudentResult::query()
            ->select([
                'id',
                'student_id',
                'exam_id',
                'marks',
                'max_marks',
                'percentage',
                'grade',
                'rank',
                'status',
                'remark',
                'is_absent',
            ])
            ->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->first();

        if (!$studentResult) {
            return [
                'status' => false,
                'message' => 'Student result not found.',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Top Rankings
        |--------------------------------------------------------------------------
        */

        $topRankings = StudentResult::query()
            ->select([
                'student_id',
                'marks',
                'max_marks',
                'percentage',
                'grade',
                'rank',
                'is_absent',
            ])
            ->with([
                'student:id,initial_name',
            ])
            ->where('exam_id', $examId)
            ->whereNotNull('rank')
            ->whereNotNull('marks')
            ->orderBy('rank')
            ->limit(10)
            ->get()
            ->map(function ($result) {

                return [
                    'rank' => $result->rank,

                    'student' => [
                        'id' => $result->student?->id,
                        'initial_name' => $result->student?->initial_name,
                    ],

                    'marks' => (float) $result->marks,
                    'max_marks' => (float) $result->max_marks,
                    'percentage' => (float) $result->percentage,
                    'grade' => $result->grade,
                    'is_absent' => (bool) $result->is_absent,
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return [
            'status' => true,
            'message' => 'Result fetched successfully.',
            'data' => [

                'exam' => [

                    'id' => $exam->id,
                    'title' => $exam->title,
                    'exam_date' => $exam->exam_date?->format('Y-m-d'),
                    'start_time' => $exam->start_time,
                    'end_time' => $exam->end_time,
                    'status' => $exam->status,

                    'student_class' => [
                        'id' => $exam->studentClass?->id,
                        'class_name' => $exam->studentClass?->class_name,
                    ],

                    'subject' => [
                        'id' => $exam->studentClass?->subject?->id,
                        'subject_name' => $exam->studentClass?->subject?->subject_name,
                    ],

                    'teacher' => [
                        'id' => $exam->studentClass?->teacher?->id,
                        'full_name' => $exam->studentClass?->teacher?->full_name,
                    ],

                    'category' => [
                        'id' => $exam->category?->id,
                        'category_name' => $exam->category?->category_name,
                    ],

                    'hall' => [
                        'id' => $exam->hall?->id,
                        'hall_name' => $exam->hall?->hall_name,
                    ],
                ],

                'student_result' => [

                    'marks' => (float) $studentResult->marks,
                    'max_marks' => (float) $studentResult->max_marks,
                    'percentage' => (float) $studentResult->percentage,
                    'grade' => $studentResult->grade,
                    'rank' => $studentResult->rank,
                    'status' => $studentResult->status,
                    'remark' => $studentResult->remark,
                    'is_absent' => (bool) $studentResult->is_absent,
                ],

                'top_rankings' => $topRankings,
            ],
        ];
    }
}