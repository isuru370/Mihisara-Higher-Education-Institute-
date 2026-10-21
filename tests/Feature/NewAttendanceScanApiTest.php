<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * POST /api/new-attendance/scan, as the mobile app calls it.
 *
 * Student ST029 has History 4:30-6:00 PM and Maths 6:00-8:00 PM today.
 */
class NewAttendanceScanApiTest extends TestCase
{
    use RefreshDatabase;

    private int $historyScheduleId;
    private int $mathsScheduleId;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Http::fake();

        $this->seedStudentWithTwoClasses();

        $adminTypeId = DB::table('user_types')->insertGetId([
            'name' => 'Admin', 'code' => 'ADMIN', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $user = new User();
        $user->forceFill([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => 'secret-pass',
            'is_active' => true,
            'user_type_id' => $adminTypeId,
        ])->save();

        Sanctum::actingAs($user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_two_open_classes_return_selection_and_mark_nothing(): void
    {
        $this->clockAt('17:30');

        $this->scan()
            ->assertOk()
            ->assertJsonPath('requires_selection', true)
            ->assertJsonPath('data.student.student_code', 'ST029')
            ->assertJsonPath('data.options.0.class_schedule_id', $this->historyScheduleId)
            ->assertJsonPath('data.options.0.class.subject', 'History')
            ->assertJsonPath('data.options.1.class_schedule_id', $this->mathsScheduleId)
            ->assertJsonPath('data.options.1.class.subject', 'Maths');

        $this->assertSame(0, DB::table('student_attendances')->count());
    }

    public function test_chosen_class_is_marked(): void
    {
        $this->clockAt('17:30');

        $this->scan($this->mathsScheduleId)
            ->assertOk()
            ->assertJsonPath('requires_selection', false)
            ->assertJsonPath('data.schedule.id', $this->mathsScheduleId)
            ->assertJsonPath('data.schedule.class.subject', 'Maths')
            ->assertJsonPath('data.attendance.already_marked', false)
            ->assertJsonPath('data.attendance.present_count', 1)
            ->assertJsonStructure(['data' => [
                'student', 'schedule', 'enrollment', 'attendance', 'tute',
            ]]);

        $this->assertDatabaseHas('student_attendances', [
            'class_schedule_id' => $this->mathsScheduleId,
            'mark_method' => 'qr_mobile',
        ]);
        $this->assertDatabaseMissing('student_attendances', [
            'class_schedule_id' => $this->historyScheduleId,
        ]);
    }

    public function test_cancelled_class_is_skipped(): void
    {
        DB::table('class_schedules')
            ->where('id', $this->historyScheduleId)
            ->update(['status' => 'cancelled']);

        $this->clockAt('17:30');

        $this->scan()
            ->assertOk()
            ->assertJsonPath('requires_selection', false)
            ->assertJsonPath('data.schedule.id', $this->mathsScheduleId);
    }

    public function test_after_one_class_is_marked_the_other_is_used(): void
    {
        $this->clockAt('16:00');
        $this->scan()->assertOk()->assertJsonPath('data.schedule.id', $this->historyScheduleId);

        $this->clockAt('17:30');
        $this->scan()
            ->assertOk()
            ->assertJsonPath('requires_selection', false)
            ->assertJsonPath('data.schedule.id', $this->mathsScheduleId);

        $this->assertSame(2, DB::table('student_attendances')->count());
    }

    public function test_choosing_a_marked_class_returns_409(): void
    {
        $this->clockAt('16:00');
        $this->scan()->assertOk();

        $this->clockAt('17:30');
        $this->scan($this->historyScheduleId)
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_unknown_code_returns_404_with_message(): void
    {
        $this->clockAt('17:30');

        $this->postJson('/api/new-attendance/scan', ['code' => 'NOPE', 'mark_method' => 'qr_mobile'])
            ->assertStatus(404)
            ->assertJsonPath('message', 'QR code invalid');
    }

    public function test_too_early_returns_403_with_opening_time(): void
    {
        $this->clockAt('14:00');

        $this->scan()
            ->assertStatus(403)
            ->assertJsonPath('message', 'Attendance opens at 03:30 PM.');
    }

    public function test_current_month_paid_is_reported(): void
    {
        $this->pay($this->mathsScheduleId, '2026-10-01');

        $this->clockAt('17:30');

        $this->scan($this->mathsScheduleId)
            ->assertOk()
            ->assertJsonPath('data.current_month_payment.month', '2026-10')
            ->assertJsonPath('data.current_month_payment.month_label', 'October 2026')
            ->assertJsonPath('data.current_month_payment.is_paid', true)
            ->assertJsonPath('data.current_month_payment.is_free_card', false);
    }

    public function test_payment_for_last_month_is_not_paid_for_this_month(): void
    {
        $this->pay($this->mathsScheduleId, '2026-09-01');

        $this->clockAt('17:30');

        $this->scan($this->mathsScheduleId)
            ->assertOk()
            ->assertJsonPath('data.last_payment.payment_month', '2026-09')
            ->assertJsonPath('data.current_month_payment.is_paid', false);
    }

    public function test_payment_for_another_class_does_not_count(): void
    {
        $this->pay($this->historyScheduleId, '2026-10-01');

        $this->clockAt('17:30');

        $this->scan($this->mathsScheduleId)
            ->assertOk()
            ->assertJsonPath('data.current_month_payment.is_paid', false);
    }

    public function test_free_card_is_reported(): void
    {
        DB::table('student_class_enrollments')
            ->where('student_class_id', $this->classIdFor($this->mathsScheduleId))
            ->update(['is_free_card' => true]);

        $this->clockAt('17:30');

        $this->scan($this->mathsScheduleId)
            ->assertOk()
            ->assertJsonPath('data.current_month_payment.is_free_card', true)
            ->assertJsonPath('data.current_month_payment.is_paid', false);
    }

    private function classIdFor(int $scheduleId): int
    {
        return (int) DB::table('class_schedules')->where('id', $scheduleId)->value('student_class_id');
    }

    private function pay(int $scheduleId, string $month): void
    {
        $enrollment = DB::table('student_class_enrollments')
            ->where('student_class_id', $this->classIdFor($scheduleId))
            ->first();

        DB::table('payments')->insert([
            'student_id' => $enrollment->student_id,
            'student_class_enrollment_id' => $enrollment->id,
            'amount' => 2500,
            'paid_at' => $month . ' 10:00:00',
            'payment_month' => $month,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function scan(?int $classScheduleId = null)
    {
        $body = ['code' => 'ST029', 'mark_method' => 'qr_mobile'];

        if ($classScheduleId) {
            $body['class_schedule_id'] = $classScheduleId;
        }

        return $this->postJson('/api/new-attendance/scan', $body);
    }

    private function clockAt(string $time): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-21 ' . $time));
    }

    private function seedStudentWithTwoClasses(): void
    {
        $now = now();

        $gradeId = DB::table('grades')->insertGetId(['grade_name' => '2027 A/L', 'created_at' => $now, 'updated_at' => $now]);
        $teacherId = DB::table('teachers')->insertGetId([
            'custom_id' => 'TC001', 'email' => 'teacher@example.com', 'mobile' => '0700000001',
            'nic' => '900000000V',
            'full_name' => 'Teacher One', 'initials' => 'T', 'bday' => '1990-01-01',
            'gender' => 'male', 'address1' => 'Negombo', 'bank_branch_id' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('class_categories')->insertGetId(['category_name' => 'Theory', 'code' => 'THEORY', 'created_at' => $now, 'updated_at' => $now]);

        $studentId = DB::table('students')->insertGetId([
            'custom_id' => 'ST029', 'initial_name' => 'A. Student', 'gender' => 'male',
            'guardian_mobile' => '0700000000', 'grade_id' => $gradeId, 'img_url' => 'x.png',
            'is_active' => true, 'student_disable' => false, 'permanent_qr_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->historyScheduleId = $this->classFor($studentId, 'History', $gradeId, $teacherId, $categoryId, '16:30:00', '18:00:00');
        $this->mathsScheduleId = $this->classFor($studentId, 'Maths', $gradeId, $teacherId, $categoryId, '18:00:00', '20:00:00');
    }

    private function classFor(int $studentId, string $subject, int $gradeId, int $teacherId, int $categoryId, string $start, string $end): int
    {
        $now = now();

        $subjectId = DB::table('subjects')->insertGetId(['subject_name' => $subject, 'created_at' => $now, 'updated_at' => $now]);

        $classId = DB::table('student_classes')->insertGetId([
            'class_name' => $subject . ' Class', 'class_type' => 'offline',
            'teacher_id' => $teacherId, 'subject_id' => $subjectId, 'grade_id' => $gradeId,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $feeId = DB::table('class_category_fees')->insertGetId([
            'student_class_id' => $classId, 'class_category_id' => $categoryId, 'fee' => 2500,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        DB::table('student_class_enrollments')->insert([
            'student_id' => $studentId, 'student_class_id' => $classId,
            'class_category_fee_id' => $feeId, 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return DB::table('class_schedules')->insertGetId([
            'student_class_id' => $classId, 'class_category_fee_id' => $feeId,
            'class_date' => '2026-10-21', 'start_time' => $start, 'end_time' => $end,
            'day_of_week' => 'wednesday', 'status' => 'scheduled', 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}
