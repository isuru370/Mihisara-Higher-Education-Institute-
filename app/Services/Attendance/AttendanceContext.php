<?php

namespace App\Services\Attendance;

use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Illuminate\Support\Collection;

class AttendanceContext
{
    /**
     * Student
     *
     * @var Student
     */
    public Student $student;

    /**
     * Student Enrollment
     *
     * @var StudentClassEnrollment
     */
    public StudentClassEnrollment $enrollment;

    /**
     * Current Attendance Schedule
     *
     * @var ClassSchedule
     */
    public ClassSchedule $schedule;

    /**
     * Today's Schedules
     *
     * @var Collection
     */
    public Collection $todaySchedules;


    /**
     * Create Context
     *
     * @param Student $student
     * @param StudentClassEnrollment $enrollment
     * @param ClassSchedule $schedule
     * @param Collection $todaySchedules
     */
    public function __construct(
        Student $student,
        StudentClassEnrollment $enrollment,
        ClassSchedule $schedule,
        Collection $todaySchedules,
    ) {
        $this->student = $student;
        $this->enrollment = $enrollment;
        $this->schedule = $schedule;
        $this->todaySchedules = $todaySchedules;
    }
}