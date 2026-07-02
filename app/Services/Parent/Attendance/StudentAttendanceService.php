<?php

namespace App\Services\Parent\Attendance;

use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use Carbon\Carbon;

class StudentAttendanceService
{
    public function fetchAttendance(int $studentId): array
    {
        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        $student = Student::query()
            ->select([
                'id',
                'initial_name',
                'grade_id',
                'created_at',
            ])
            ->with([
                'grade:id,grade_name',
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
        | Active Enrollments
        |--------------------------------------------------------------------------
        */

        $enrollments = StudentClassEnrollment::query()
            ->select([
                'id',
                'student_class_id',
                'class_category_fee_id',
                'enrolled_at',
            ])
            ->where('student_id', $studentId)
            ->where('is_active', true)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Enrollment Map
        |--------------------------------------------------------------------------
        */

        $enrollmentMap = $enrollments->keyBy(
            'class_category_fee_id'
        );

        /*
        |--------------------------------------------------------------------------
        | Class Schedules (Single Query)
        |--------------------------------------------------------------------------
        */

        $scheduleCollection = ClassSchedule::query()
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

                'studentClass:id,class_name,teacher_id,grade_id',

                'studentClass.teacher:id,initials',

                'studentClass.grade:id,grade_name',

                'classCategoryFee:id,class_category_id',

                'classCategoryFee.category:id,category_name',

            ])
            ->whereIn(
                'class_category_fee_id',
                $enrollmentMap->keys()
            )
            ->whereIn('status', [
                'ongoing',
                'completed',
            ])
            // ✅ Only get schedules up to today
            ->where('class_date', '<=', Carbon::today())
            ->orderBy('class_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Remove classes before enrollment date
        |--------------------------------------------------------------------------
        */

        $scheduleCollection = $scheduleCollection
            ->filter(function ($schedule) use (
                $enrollmentMap,
                $student
            ) {

                $enrollment = $enrollmentMap->get(
                    $schedule->class_category_fee_id
                );

                $fromDate =
                    $enrollment?->enrolled_at
                    ?? $student->created_at;

                return $schedule
                    ->class_date
                    ->gte($fromDate);
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Free Class Attendances
        |--------------------------------------------------------------------------
        */

        $freeAttendances = StudentAttendance::query()
            ->select([
                'id',
                'student_id',
                'class_schedule_id',
                'student_class_enrollment_id',
            ])
            ->where('student_id', $studentId)
            ->whereNull('student_class_enrollment_id')
            ->whereHas('classSchedule', function ($query) {
                $query->whereIn('status', ['ongoing', 'completed'])
                      ->where('class_date', '<=', Carbon::today());
            })
            ->with([
                'classSchedule:id,student_class_id,class_category_fee_id,class_date,start_time,end_time,status',

                'classSchedule.studentClass:id,class_name,teacher_id,grade_id',

                'classSchedule.studentClass.teacher:id,initials',

                'classSchedule.studentClass.grade:id,grade_name',

                'classSchedule.classCategoryFee:id,class_category_id',

                'classSchedule.classCategoryFee.category:id,category_name',
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Merge Free Classes
        |--------------------------------------------------------------------------
        */

        foreach ($freeAttendances as $attendance) {

            if (
                !$attendance->classSchedule ||
                !in_array(
                    $attendance->classSchedule->status,
                    ['ongoing', 'completed']
                )
            ) {
                continue;
            }

            $scheduleCollection->push(
                $attendance->classSchedule
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Duplicate Schedule IDs
        |--------------------------------------------------------------------------
        */

        $scheduleCollection = $scheduleCollection
            ->unique('id')
            ->sortBy([
                ['class_date', 'desc'],
                ['start_time', 'desc'],
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Attendance Records (Single Query)
        |--------------------------------------------------------------------------
        */

        $attendanceRecords = StudentAttendance::query()
            ->select([
                'id',
                'student_id',
                'class_schedule_id',
                'mark_method',
                'student_class_enrollment_id',
                'attended_at',
                'marked_by',
            ])
            ->with([
                'markedBy:id,name',
            ])
            ->where('student_id', $studentId)
            ->whereIn(
                'class_schedule_id',
                $scheduleCollection->pluck('id')
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Attendance Lookup
        |--------------------------------------------------------------------------
        */

        $attendanceLookup = $attendanceRecords
            ->keyBy('class_schedule_id');

        /*
        |--------------------------------------------------------------------------
        | Build Class Response
        |--------------------------------------------------------------------------
        */

        $classes = [];

        $overallTotal = 0;
        $overallPresent = 0;
        $overallAbsent = 0;
        
        // ✅ Track unique class IDs
        $uniqueClassIds = [];

        foreach ($scheduleCollection as $schedule) {

            $attendance = $attendanceLookup->get(
                $schedule->id
            );

            $isPresent = !is_null($attendance);

            $overallTotal++;

            if ($isPresent) {
                $overallPresent++;
            } else {
                $overallAbsent++;
            }

            $classId = $schedule->student_class_id;

            // ✅ Track unique class IDs
            if (!in_array($classId, $uniqueClassIds)) {
                $uniqueClassIds[] = $classId;
            }

            if (!isset($classes[$classId])) {

                $classes[$classId] = [

                    'class_id' => $classId,

                    'class_name' =>
                    $schedule->studentClass?->class_name,

                    'category_name' =>
                    $schedule->classCategoryFee?->category?->category_name,

                    'grade_name' =>
                    $schedule->studentClass?->grade?->grade_name,

                    'teacher' =>
                    $schedule->studentClass?->teacher?->initials,

                    'total_schedules' => 0,

                    'present_classes' => 0,

                    'absent_classes' => 0,

                    'attendance_percentage' => 0,

                    'attendance_history' => [],
                ];
            }

            $classes[$classId]['total_schedules']++;

            if ($isPresent) {
                $classes[$classId]['present_classes']++;
            } else {
                $classes[$classId]['absent_classes']++;
            }

            $classes[$classId]['attendance_history'][] = [

                'schedule_id' => $schedule->id,

                'class_date' => $schedule->class_date?->format('Y-m-d'),

                'start_time' => $schedule->start_time,

                'end_time' => $schedule->end_time,

                'is_present' => $isPresent,

                'attended_at' => $attendance?->attended_at,

                'mark_method' => $attendance?->mark_method,

                'marked_by' => $attendance
                    ? [
                        'id' => $attendance->markedBy?->id,
                        'name' => $attendance->markedBy?->name,
                    ]
                    : null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Class Attendance Percentage
        |--------------------------------------------------------------------------
        */

        foreach ($classes as &$class) {

            $class['attendance_percentage'] =
                $class['total_schedules'] > 0
                ? round(
                    ($class['present_classes'] / $class['total_schedules']) * 100,
                    2
                )
                : 0;

            usort(
                $class['attendance_history'],
                function ($a, $b) {

                    return strtotime($b['class_date'])
                        <=> strtotime($a['class_date']);
                }
            );
        }

        unset($class);

        /*
        |--------------------------------------------------------------------------
        | Overall Percentage
        |--------------------------------------------------------------------------
        */

        $overallPercentage =
            $overallTotal > 0
            ? round(
                ($overallPresent / $overallTotal) * 100,
                2
            )
            : 0;

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
        | Final Response
        |--------------------------------------------------------------------------
        */

        return [
            'status' => true,
            'message' => 'Attendance fetched successfully.',
            'data' => [
                'summary' => [

                    // ✅ total_schedules - schedule count (total classes conducted)
                    'total_schedules' => $overallTotal,

                    // ✅ total_classes - unique class count (number of different subjects)
                    'total_classes' => count($uniqueClassIds),

                    'present_classes' => $overallPresent,

                    'absent_classes' => $overallAbsent,

                    'attendance_percentage' => $overallPercentage,

                ],

                'classes' => $classes,

            ],
        ];
    }
}