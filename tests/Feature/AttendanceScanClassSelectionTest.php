<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\StudentAttendance;
use App\Services\Attendance\AttendanceScanService;
use App\Services\Attendance\AttendanceStudentInfoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Student has History 4:30-6:00 PM and Maths 6:00-8:00 PM on the same day.
 * Attendance opens one hour before a class, so at 5:30 PM both are open.
 */
class AttendanceScanClassSelectionTest extends TestCase
{
    use RefreshDatabase;

    private const STUDENT_ID = 1;
    private const HISTORY_ID = 10;
    private const MATHS_ID = 20;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_two_open_classes_ask_which_class(): void
    {
        $result = $this->check('17:30');

        $this->assertTrue($result['requires_selection']);
        $this->assertSame(
            [self::HISTORY_ID, self::MATHS_ID],
            array_column($result['options'], 'class_schedule_id')
        );
        $this->assertSame([false, false], array_column($result['options'], 'already_marked'));
    }

    public function test_chosen_class_is_used(): void
    {
        $result = $this->check('17:30', self::MATHS_ID);

        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('requires_selection', $result);
        $this->assertSame(self::MATHS_ID, $result['schedule']->id);
    }

    public function test_marked_class_is_skipped_and_other_class_is_used(): void
    {
        $this->markAttendance(self::HISTORY_ID);

        $result = $this->check('17:30');

        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('requires_selection', $result);
        $this->assertSame(self::MATHS_ID, $result['schedule']->id);
    }

    public function test_choosing_a_marked_class_is_rejected(): void
    {
        $this->markAttendance(self::HISTORY_ID);

        $result = $this->check('17:30', self::HISTORY_ID);

        $this->assertFalse($result['success']);
        $this->assertSame(409, $result['status_code']);
    }

    public function test_choosing_a_class_that_is_not_open_is_rejected(): void
    {
        // 3:45 PM: only History is open (Maths opens at 5:00 PM).
        $result = $this->check('15:45', self::MATHS_ID);

        $this->assertFalse($result['success']);
        $this->assertSame(422, $result['status_code']);
    }

    public function test_single_open_class_is_marked_without_asking(): void
    {
        $result = $this->check('15:45');

        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('requires_selection', $result);
        $this->assertSame(self::HISTORY_ID, $result['schedule']->id);
    }

    public function test_all_open_classes_marked_returns_conflict(): void
    {
        $this->markAttendance(self::HISTORY_ID);
        $this->markAttendance(self::MATHS_ID);

        $result = $this->check('17:30');

        $this->assertFalse($result['success']);
        $this->assertSame(409, $result['status_code']);
    }

    private function check(string $time, ?int $classScheduleId = null): array
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 ' . $time));

        $service = new class(app(AttendanceStudentInfoService::class)) extends AttendanceScanService {
            public function window(int $studentId, $schedules, ?int $classScheduleId): array
            {
                return $this->checkAttendanceWindow($studentId, $schedules, $classScheduleId);
            }
        };

        return $service->window(self::STUDENT_ID, $this->todaySchedules(), $classScheduleId);
    }

    private function todaySchedules()
    {
        return collect([
            $this->schedule(self::HISTORY_ID, '16:30:00', '18:00:00'),
            $this->schedule(self::MATHS_ID, '18:00:00', '20:00:00'),
        ]);
    }

    private function schedule(int $id, string $start, string $end): ClassSchedule
    {
        $schedule = new ClassSchedule([
            'class_date' => '2026-10-21',
            'start_time' => $start,
            'end_time' => $end,
        ]);

        $schedule->id = $id;

        return $schedule;
    }

    private function markAttendance(int $scheduleId): void
    {
        StudentAttendance::query()->insert([
            'student_id' => self::STUDENT_ID,
            'class_schedule_id' => $scheduleId,
            'attended_at' => '2026-10-21 16:00:00',
            'mark_method' => 'qr_mobile',
        ]);
    }
}
