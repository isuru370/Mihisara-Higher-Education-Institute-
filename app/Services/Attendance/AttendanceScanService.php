<?php

namespace App\Services\Attendance;

use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\StudentQRService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceScanService
{
    /**
     * Student Info Service
     *
     * @var AttendanceStudentInfoService
     */
    protected $studentInfoService;

    /**
     * Constructor
     *
     * @param AttendanceStudentInfoService $studentInfoService
     */
    public function __construct(
        AttendanceStudentInfoService $studentInfoService
    ) {
        $this->studentInfoService = $studentInfoService;
    }

    /**
     * Scan Student QR
     *
     * When more than one class is open for the student at this time and no
     * $classScheduleId is given, nothing is chosen: the result carries
     * `requires_selection` = true and the open classes, so the caller can ask
     * which class the student came for and scan again with that schedule id.
     *
     * @param string $qrCode
     * @param int|null $classScheduleId Class chosen by the staff member
     * @return array
     */
    public function scan($qrCode, ?int $classScheduleId = null)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | Read QR
            |--------------------------------------------------------------------------
            */

            $qrResult = StudentQRService::read($qrCode);

            if (!$qrResult['success']) {

                return $this->error(
                    $qrResult['message'],
                    $qrResult['status_code']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Load Student
            |--------------------------------------------------------------------------
            */

            $student = $this->getStudent(
                $qrResult['student_id']
            );

            if (!$student) {

                return $this->error(
                    'Student record not found.',
                    404
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Active Enrollments
            |--------------------------------------------------------------------------
            */

            $enrollments = $this->getActiveEnrollments(
                $student
            );

            if ($enrollments->isEmpty()) {

                return $this->error(
                    'This student is not enrolled in any active classes.',
                    404
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Today's Schedules
            |--------------------------------------------------------------------------
            */

            $todaySchedules = $this->getTodaySchedules(
                $enrollments
            );

            if ($todaySchedules->isEmpty()) {

                return $this->error(
                    'Student found. No classes are scheduled for today.',
                    404
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Attendance Window
            |--------------------------------------------------------------------------
            */

            $attendanceWindow = $this->checkAttendanceWindow(

                $student->id,

                $todaySchedules,

                $classScheduleId

            );

            /*
            |--------------------------------------------------------------------------
            | More Than One Class Open - Ask Which One
            |--------------------------------------------------------------------------
            */

            if (!empty($attendanceWindow['requires_selection'])) {

                return $this->selection(
                    $attendanceWindow['message'],
                    $student,
                    $attendanceWindow['options']
                );
            }

            if (!$attendanceWindow['success']) {

                return $this->error(

                    $attendanceWindow['message'],

                    $attendanceWindow['status_code']

                );
            }

            /*
            |--------------------------------------------------------------------------
            | Current Schedule
            |--------------------------------------------------------------------------
            */

            $schedule = $attendanceWindow['schedule'];

            /*
            |--------------------------------------------------------------------------
            | Student Enrollment
            |--------------------------------------------------------------------------
            */

            $enrollment = $enrollments->first(function ($enrollment) use ($schedule) {

                return

                    $enrollment->student_class_id == $schedule->student_class_id

                    &&

                    $enrollment->class_category_fee_id == $schedule->class_category_fee_id;
            });

            if (!$enrollment) {

                return $this->error(

                    'Student enrollment not found.',

                    404

                );
            }

            /*
            |--------------------------------------------------------------------------
            | Attendance Context
            |--------------------------------------------------------------------------
            */

            $context = new AttendanceContext(

                $student,

                $enrollment,

                $schedule,

                $todaySchedules,

            );

            /*
            |--------------------------------------------------------------------------
            | Student Attendance Information
            |--------------------------------------------------------------------------
            */

            $studentInfo = $this->studentInfoService
                ->build($context);

            /*
            |--------------------------------------------------------------------------
            | Success Response
            |--------------------------------------------------------------------------
            */

            return $this->success(

                $attendanceWindow['message'],

                [

                    'student' => $this->studentPayload($student),

                    'schedule' => $this->schedulePayload($schedule),

                    'enrollment' => $studentInfo['enrollment'],

                    'last_payment' => $studentInfo['last_payment'],

                    'attendance' => $studentInfo['attendance'],

                    'tute' => $studentInfo['tute'],

                ]

            );
        } catch (Throwable $e) {

            Log::error('Attendance Scan Failed', [

                'qr_code' => $qrCode,

                'message' => $e->getMessage(),

                'file' => $e->getFile(),

                'line' => $e->getLine(),

            ]);

            return $this->error(

                'Something went wrong while scanning.',

                500

            );
        }
    }

    /**
     * Load Active Student
     */
    /**
     * Load Active Student
     */
    protected function getStudent($studentId)
    {
        return Student::query()

            ->select([
                'id',
                'custom_id',
                'temporary_qr_code',
                'permanent_qr_active',
                'initial_name',
                'guardian_mobile',
                'img_url',
                'grade_id',
            ])

            ->with([
                'grade:id,grade_name',
            ])

            ->find($studentId);
    }

    /**
     * Get Student Active Enrollments
     */
    protected function getActiveEnrollments(Student $student)
    {
        return $student->enrollments()

            ->where('is_active', true)

            ->with([

                /*
                |--------------------------------------------------------------------------
                | Class
                |--------------------------------------------------------------------------
                */

                'studentClass:id,class_name,teacher_id,subject_id,grade_id',

                'studentClass.teacher:id,full_name',

                'studentClass.subject:id,subject_name',

                'studentClass.grade:id,grade_name',

                /*
                |--------------------------------------------------------------------------
                | Category
                |--------------------------------------------------------------------------
                */

                'classCategoryFee:id,class_category_id,fee',

                'classCategoryFee.category:id,category_name',

                /*
                |--------------------------------------------------------------------------
                | Payments
                |--------------------------------------------------------------------------
                */

                'payments' => function ($query) {

                    $query

                        ->where('status', 'completed')

                        ->latest('paid_at');
                },

            ])

            ->get();
    }

    /**
     * Get Today's Scheduled Classes
     */
    protected function getTodaySchedules($enrollments)
    {
        $studentClassIds = $enrollments

            ->pluck('student_class_id')

            ->unique()

            ->values()

            ->toArray();

        $categoryFeeIds = $enrollments

            ->pluck('class_category_fee_id')

            ->unique()

            ->values()

            ->toArray();

        return ClassSchedule::query()

            ->select([
                'id',
                'student_class_id',
                'class_category_fee_id',
                'class_hall_id',
                'class_date',
                'start_time',
                'end_time',
            ])

            ->with([

                'studentClass:id,class_name,teacher_id,subject_id,grade_id',

                'studentClass.teacher:id,full_name',

                'studentClass.subject:id,subject_name',

                'studentClass.grade:id,grade_name',

                'classCategoryFee:id,class_category_id',

                'classCategoryFee.category:id,category_name',

                'hall:id,hall_name',

            ])

            ->whereIn(
                'student_class_id',
                $studentClassIds
            )

            ->whereIn(
                'class_category_fee_id',
                $categoryFeeIds
            )

            ->whereDate(
                'class_date',
                today()
            )

            ->whereIn('status', [
                'scheduled',
                'ongoing',
            ])

            ->where(
                'is_active',
                true
            )

            ->orderBy('start_time')

            ->get();
    }

    /**
     * Check Attendance Window
     *
     * A class is "open" from one hour before it starts until it ends.
     * Cancelled / inactive classes never reach here (see getTodaySchedules).
     *
     * - $classScheduleId given: mark that class if it is open and not marked.
     * - Otherwise, skip classes already marked. One open class left: use it.
     *   More than one: return requires_selection with every open class
     *   (marked ones included, flagged already_marked) so staff can choose.
     */
    protected function checkAttendanceWindow(
        int $studentId,
        $todaySchedules,
        ?int $classScheduleId = null
    ): array {

        $now = Carbon::now();

        $nextAttendanceOpen = null;

        $openSchedules = collect();

        foreach ($todaySchedules as $schedule) {

            $classStart = $schedule->class_date
                ->copy()
                ->setTimeFromTimeString(
                    $schedule->start_time
                );

            $classEnd = $schedule->class_date
                ->copy()
                ->setTimeFromTimeString(
                    $schedule->end_time
                );

            $attendanceOpen = $classStart
                ->copy()
                ->subHour();

            if ($now->between($attendanceOpen, $classEnd)) {

                $openSchedules->push($schedule);

                continue;
            }

            if ($attendanceOpen->gt($now)) {

                if (
                    is_null($nextAttendanceOpen)
                    ||
                    $attendanceOpen->lt($nextAttendanceOpen)
                ) {

                    $nextAttendanceOpen = $attendanceOpen;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | No Class Open Right Now
        |--------------------------------------------------------------------------
        */

        if ($openSchedules->isEmpty()) {

            if ($nextAttendanceOpen) {

                return [

                    'success' => false,

                    'status_code' => 403,

                    'message' =>
                    'Attendance opens at ' .
                        $nextAttendanceOpen->format('h:i A') .
                        '.',

                ];
            }

            return [

                'success' => false,

                'status_code' => 403,

                'message' =>
                'All scheduled classes for today have finished.',

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Already Marked Schedules
        |--------------------------------------------------------------------------
        */

        $markedScheduleIds = StudentAttendance::query()

            ->where('student_id', $studentId)

            ->whereIn('class_schedule_id', $openSchedules->pluck('id'))

            ->pluck('class_schedule_id')

            ->map(function ($id) {
                return (int) $id;
            })

            ->all();

        $isMarked = function ($schedule) use ($markedScheduleIds) {
            return in_array((int) $schedule->id, $markedScheduleIds, true);
        };

        /*
        |--------------------------------------------------------------------------
        | Class Chosen By Staff
        |--------------------------------------------------------------------------
        */

        if (!is_null($classScheduleId)) {

            $chosen = $openSchedules->first(function ($schedule) use ($classScheduleId) {
                return (int) $schedule->id === $classScheduleId;
            });

            if (!$chosen) {

                return [

                    'success' => false,

                    'status_code' => 422,

                    'message' =>
                    'The selected class is not open for attendance now.',

                ];
            }

            if ($isMarked($chosen)) {

                return [

                    'success' => false,

                    'status_code' => 409,

                    'message' =>
                    'Attendance has already been marked.',

                ];
            }

            return [

                'success' => true,

                'status_code' => 200,

                'message' =>
                'Attendance can be marked.',

                'schedule' => $chosen,

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Skip Classes Already Marked
        |--------------------------------------------------------------------------
        */

        $unmarked = $openSchedules->reject($isMarked)->values();

        if ($unmarked->isEmpty()) {

            return [

                'success' => false,

                'status_code' => 409,

                'message' =>
                'Attendance has already been marked.',

            ];
        }

        if ($unmarked->count() === 1) {

            return [

                'success' => true,

                'status_code' => 200,

                'message' =>
                'Attendance can be marked.',

                'schedule' => $unmarked->first(),

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | More Than One Class Open - Staff Must Choose
        |--------------------------------------------------------------------------
        */

        return [

            'success' => true,

            'status_code' => 200,

            'requires_selection' => true,

            'message' =>
            'This student has more than one class right now. Select the class.',

            'options' => $openSchedules->map(function ($schedule) use ($isMarked) {

                return array_merge(

                    ['class_schedule_id' => $schedule->id],

                    $this->schedulePayload($schedule),

                    ['already_marked' => $isMarked($schedule)]

                );
            })->values()->all(),

        ];
    }

    /**
     * Student Payload
     */
    protected function studentPayload(Student $student): array
    {
        return [

            'id' => $student->id,

            'student_code' => $student->permanent_qr_active
                ? $student->custom_id
                : $student->temporary_qr_code,

            'initial_name' => $student->initial_name,

            'guardian_mobile' => $student->guardian_mobile,

            'img_url' => $student->img_url,

            'grade' => [
                'id' => optional($student->grade)->id,
                'grade_name' => optional($student->grade)->grade_name,
            ],

        ];
    }

    /**
     * Schedule Payload
     */
    protected function schedulePayload(ClassSchedule $schedule): array
    {
        return [

            'id' => $schedule->id,

            'class_date' => $schedule->class_date->format('Y-m-d'),

            'start_time' => $schedule->start_time,

            'end_time' => $schedule->end_time,

            'hall' => optional($schedule->hall)->hall_name,

            'class' => [

                'id' => optional($schedule->studentClass)->id,

                'class_name' => optional($schedule->studentClass)->class_name,

                'teacher' => optional(optional($schedule->studentClass)->teacher)->full_name,

                'subject' => optional(optional($schedule->studentClass)->subject)->subject_name,

                'grade' => optional(optional($schedule->studentClass)->grade)->grade_name,

                'category' => optional(optional($schedule->classCategoryFee)->category)->category_name,

            ],

        ];
    }

    /**
     * Selection Response (nothing marked, staff must choose a class)
     */
    protected function selection(
        string $message,
        Student $student,
        array $options
    ): array {

        return [

            'success' => true,

            'status_code' => 200,

            'requires_selection' => true,

            'message' => $message,

            'data' => [

                'student' => $this->studentPayload($student),

                'options' => $options,

            ],

        ];
    }

    /**
     * Success Response
     */
    protected function success(
        string $message,
        array $data = []
    ): array {

        return [

            'success' => true,

            'status_code' => 200,

            'message' => $message,

            'data' => $data,

        ];
    }

    /**
     * Error Response
     */
    protected function error(
        string $message,
        int $statusCode = 422
    ): array {

        return [

            'success' => false,

            'status_code' => $statusCode,

            'message' => $message,

            'data' => null,

        ];
    }
}
