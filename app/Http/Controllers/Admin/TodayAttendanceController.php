<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TodayAttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!$request->class_schedule_id, 404, 'Class schedule is required');

        $schedule = ClassSchedule::with(['studentClass', 'classCategoryFee', 'hall'])
            ->findOrFail($request->class_schedule_id);

        $today = Carbon::today();

        $enrollments = StudentClassEnrollment::with('student')
            ->where('is_active',true)
            ->where('student_class_id', $schedule->student_class_id)
            ->where('class_category_fee_id', $schedule->class_category_fee_id)
            ->get();

        $todayAttendances = StudentAttendance::with('student')
            ->where('class_schedule_id', $schedule->id)
            ->whereDate('attended_at', $today)
            ->get();

        $attendanceByEnrollment = $todayAttendances
            ->whereNotNull('student_class_enrollment_id')
            ->keyBy('student_class_enrollment_id');

        $attendanceByStudent = $todayAttendances
            ->whereNull('student_class_enrollment_id')
            ->keyBy('student_id');

        $students = $enrollments->map(function ($enrollment) use ($attendanceByEnrollment, $attendanceByStudent) {
            $attendance = $attendanceByEnrollment->get($enrollment->id)
                ?? $attendanceByStudent->get($enrollment->student_id);

            return [
                'student' => $enrollment->student,
                'enrollment' => $enrollment,
                'attendance' => $attendance,
                'status' => $attendance ? 'present' : 'absent',
            ];
        });

        $notEnrolledAttendances = $todayAttendances->whereNull('student_class_enrollment_id');

        return view('admin.today_attendance.index', compact(
            'schedule',
            'students',
            'today',
            'notEnrolledAttendances'
        ));
    }
}