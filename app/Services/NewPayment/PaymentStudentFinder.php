<?php

namespace App\Services\NewPayment;

use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Illuminate\Database\Eloquent\Collection;

class PaymentStudentFinder
{
    /**
     * Find active student using:
     * - Student Custom ID
     * - Temporary QR Code
     */
    public function findStudent(string $code): ?Student
    {
        $code = trim($code);

        if ($code === '') {
            return null;
        }

        return Student::query()
            ->where('is_active', true)
            ->where(function ($query) use ($code) {
                $query->where('custom_id', $code)
                    ->orWhere('temporary_qr_code', $code);
            })
            ->with([
                'grade',
            ])
            ->first();
    }

    /**
     * Get all active class enrollments.
     */
    public function getActiveEnrollments(
        Student $student
    ): Collection {
        return StudentClassEnrollment::query()
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->with([
                'studentClass.teacher',
                'studentClass.subject',
                'studentClass.grade',

                'classCategoryFee.category',
            ])
            ->get();
    }

    /**
     * Find one active enrollment belonging to the student.
     */
    public function findEnrollment(
        Student $student,
        int $enrollmentId
    ): ?StudentClassEnrollment {
        return StudentClassEnrollment::query()
            ->where('id', $enrollmentId)
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->with([
                'studentClass.teacher',
                'studentClass.subject',
                'studentClass.grade',

                'classCategoryFee.category',
            ])
            ->first();
    }
}