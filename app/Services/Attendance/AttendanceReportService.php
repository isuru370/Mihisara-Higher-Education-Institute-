<?php

namespace App\Services\Attendance;

use App\Models\ClassSchedule;
use App\Models\Payment;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use Carbon\Carbon;
use Exception;

class AttendanceReportService
{
    /**
     * Generate Attendance Report
     */
    public function generate(
        int $scheduleId
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Get Schedule
        |--------------------------------------------------------------------------
        */

        $schedule = $this->getSchedule(
            $scheduleId
        );

        if (!$schedule) {
            throw new Exception(
                'Schedule not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Enrollments
        |--------------------------------------------------------------------------
        */

        $enrollments = $this->getEnrollments(
            $schedule
        );

        /*
        |--------------------------------------------------------------------------
        | Get Attendance Records
        |--------------------------------------------------------------------------
        */

        $attendanceRecords = $this->getAttendanceRecords(
            $schedule->id
        );

        /*
        |--------------------------------------------------------------------------
        | Get Payment Records
        |--------------------------------------------------------------------------
        */

        $paymentRecords = $this->getPaymentRecords(
            $schedule,
            $enrollments
        );

        /*
        |--------------------------------------------------------------------------
        | Build Report
        |--------------------------------------------------------------------------
        */

        return [

            'schedule' => $schedule,

            'summary' => $this->buildSummary(
                $enrollments,
                $attendanceRecords,
                $paymentRecords
            ),

            'students' => $this->buildStudentReport(
                $enrollments,
                $attendanceRecords,
                $paymentRecords
            ),

        ];
    }

    /**
     * Get Schedule
     */
    /**
     * Get Schedule
     */
    protected function getSchedule(
        int $scheduleId
    ): ?ClassSchedule {

        return ClassSchedule::query()

            ->select([
                'id',
                'student_class_id',
                'class_category_fee_id',
                'class_date',
                'start_time',
                'end_time',
                'status',
            ])

            ->with([

                'studentClass:id,class_name',

                'classCategoryFee:id,class_category_id',

                'classCategoryFee.category:id,category_name',

            ])

            ->where(
                'id',
                $scheduleId
            )

            ->whereIn(
                'status',
                [
                    'ongoing',
                    'completed',
                ]
            )

            ->where(
                'is_active',
                true
            )

            ->first();
    }

    /**
     * Get Enrollments
     */
    /**
     * Get Student Enrollments
     */
    /**
     * Get Student Enrollments
     */
    protected function getEnrollments(
        ClassSchedule $schedule
    ) {

        return StudentClassEnrollment::query()

            ->select([

                'student_class_enrollments.id',

                'student_class_enrollments.student_id',

                'student_class_enrollments.student_class_id',

                'student_class_enrollments.class_category_fee_id',

                'student_class_enrollments.is_free_card',

                'student_class_enrollments.custom_fee',

                'student_class_enrollments.discount_percentage',

            ])

            ->with([
                'student:id,custom_id,initial_name,img_url,guardian_mobile,grade_id',
                'student.grade:id,grade_name',
            ])

            ->join(
                'students',
                'students.id',
                '=',
                'student_class_enrollments.student_id'
            )

            ->orderBy('student_id')

            ->where(
                'student_class_enrollments.student_class_id',
                $schedule->student_class_id
            )

            ->where(
                'student_class_enrollments.class_category_fee_id',
                $schedule->class_category_fee_id
            )

            ->where(
                'student_class_enrollments.is_active',
                true
            )

            ->orderBy('students.custom_id')

            ->get();
    }

    /**
     * Get Attendance Records
     */
    /**
     * Get Attendance Records
     */
    protected function getAttendanceRecords(
        int $scheduleId
    ) {

        return StudentAttendance::query()

            ->with([
                'student:id,custom_id,initial_name,img_url,guardian_mobile,grade_id',
                'student.grade:id,grade_name',
            ])

            ->select([
                'id',
                'student_id',
                'student_class_enrollment_id',
                'class_schedule_id',
                'attended_at',
            ])

            ->where(
                'class_schedule_id',
                $scheduleId
            )

            ->get();
    }

    /**
     * Get Payment Records
     */
    /**
     * Get Payment Records
     */
    protected function getPaymentRecords(
        ClassSchedule $schedule,
        $enrollments
    ) {

        /*
    |--------------------------------------------------------------------------
    | Enrollment IDs
    |--------------------------------------------------------------------------
    */

        $enrollmentIds = $enrollments
            ->pluck('id')
            ->toArray();

        /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

        $paymentDate = Carbon::parse($schedule->class_date);

        return Payment::query()

            ->select([
                'id',
                'student_class_enrollment_id',
                'payment_month',
                'paid_at',
                'status',
                'amount',
                'receipt_number',
            ])

            ->whereIn(
                'student_class_enrollment_id',
                $enrollmentIds
            )

            ->whereYear(
                'payment_month',
                $paymentDate->year
            )

            ->whereMonth(
                'payment_month',
                $paymentDate->month
            )

            ->where(
                'status',
                'completed'
            )

            ->orderByDesc('paid_at')

            ->get()

            ->unique('student_class_enrollment_id')

            ->keyBy('student_class_enrollment_id');
    }

    /**
     * Build Summary
     */
    /**
     * Build Summary
     */
    protected function buildSummary(
        $enrollments,
        $attendanceRecords,
        $paymentRecords
    ): array {

        /*
    |--------------------------------------------------------------------------
    | Total Students
    |--------------------------------------------------------------------------
    */

        $totalStudents = $enrollments->count();

        /*
    |--------------------------------------------------------------------------
    | Present Students
    |--------------------------------------------------------------------------
    */

        $presentStudents = $attendanceRecords

            ->whereNotNull(
                'student_class_enrollment_id'
            )

            ->count();

        /*
    |--------------------------------------------------------------------------
    | Absent Students
    |--------------------------------------------------------------------------
    */

        $absentStudents = max(
            $totalStudents - $presentStudents,
            0
        );

        /*
    |--------------------------------------------------------------------------
    | Paid Students
    |--------------------------------------------------------------------------
    */

        $paidStudents = $paymentRecords->count();

        /*
    |--------------------------------------------------------------------------
    | Unpaid Students
    |--------------------------------------------------------------------------
    */

        $unpaidStudents = max(
            $totalStudents - $paidStudents,
            0
        );

        /*
    |--------------------------------------------------------------------------
    | Attendance Percentage
    |--------------------------------------------------------------------------
    */

        $attendancePercentage = $totalStudents > 0

            ? round(
                ($presentStudents / $totalStudents) * 100
            )

            : 0;

        /*
    |--------------------------------------------------------------------------
    | Payment Percentage
    |--------------------------------------------------------------------------
    */

        $paymentPercentage = $totalStudents > 0

            ? round(
                ($paidStudents / $totalStudents) * 100
            )

            : 0;

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return [

            'total_students' => $totalStudents,

            'present_students' => $presentStudents,

            'absent_students' => $absentStudents,

            'attendance_percentage' => $attendancePercentage,

            'paid_students' => $paidStudents,

            'unpaid_students' => $unpaidStudents,

            'payment_percentage' => $paymentPercentage,

        ];
    }

    /**
     * Build Student Report
     */
    /**
     * Build Student Report
     */
    /**
     * Build Student Report
     */
    /**
     * Build Student Report
     */
    protected function buildStudentReport(
        $enrollments,
        $attendanceRecords,
        $paymentRecords
    ): array {

        /*
    |--------------------------------------------------------------------------
    | Key Attendance Records by Enrollment ID
    |--------------------------------------------------------------------------
    */

        $attendanceByEnrollment = $attendanceRecords

            ->whereNotNull('student_class_enrollment_id')

            ->keyBy('student_class_enrollment_id');

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        $students = [];

        /*
    |--------------------------------------------------------------------------
    | Enrolled Students
    |--------------------------------------------------------------------------
    */

        foreach ($enrollments as $enrollment) {

            $attendance = $attendanceByEnrollment->get(
                $enrollment->id
            );

            $payment = $paymentRecords->get(
                $enrollment->id
            );

            $student = $enrollment->student;

            $students[] = [

                'enrollment_id' => $enrollment->id,

                'enrollment_status' => true,

                'student' => [

                    'id' => $student->id,

                    'student_code' => $student->custom_id,

                    'initial_name' => $student->initial_name,

                    'guardian_mobile' => $student->guardian_mobile,

                    'img_url' => $student->img_url,

                    'grade' => optional(
                        $student->grade
                    )->grade_name,

                ],

                'attendance' => [

                    'is_present' => $attendance !== null,

                    'attended_at' => optional(
                        $attendance
                    )->attended_at,

                ],

                'payment' => [

                    'is_paid' => $payment !== null,

                    'payment_month' => optional(
                        $payment
                    )->payment_month,

                    'paid_at' => optional(
                        $payment
                    )->paid_at,

                    'amount' => optional(
                        $payment
                    )->amount,

                    'receipt_number' => optional(
                        $payment
                    )->receipt_number,

                ],

            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Attendance Without Enrollment
    |--------------------------------------------------------------------------
    */

        foreach ($attendanceRecords as $attendance) {

            if ($attendance->student_class_enrollment_id !== null) {
                continue;
            }

            $student = $attendance->student;

            $students[] = [

                'enrollment_id' => null,

                'enrollment_status' => false,

                'student' => [

                    'id' => $student->id,

                    'student_code' => $student->custom_id,

                    'initial_name' => $student->initial_name,

                    'guardian_mobile' => $student->guardian_mobile,

                    'img_url' => $student->img_url,

                    'grade' => optional(
                        $student->grade
                    )->grade_name,

                ],

                'attendance' => [

                    'is_present' => true,

                    'attended_at' => $attendance->attended_at,

                ],

                'payment' => [

                    'is_paid' => false,

                    'payment_month' => null,

                    'paid_at' => null,

                    'amount' => null,

                    'receipt_number' => null,

                ],

            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Sort by Student Code
    |--------------------------------------------------------------------------
    */

        return collect($students)

            ->sortBy(function ($item) {

                return $item['student']['student_code'];
            })

            ->values()

            ->toArray();
    }
}
