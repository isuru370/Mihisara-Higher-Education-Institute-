<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The mobile app's Payment Scan and Collect Payment screens.
 */
class MobilePaymentApiTest extends TestCase
{
    use RefreshDatabase;

    private int $enrollmentId;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Http::fake();

        $now = now();

        $typeId = DB::table('user_types')->insertGetId([
            'name' => 'Admin', 'code' => 'ADMIN', 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $user = new User();
        $user->forceFill([
            'name' => 'Staff', 'email' => 'staff@example.com', 'password' => 'secret-pass',
            'is_active' => true, 'user_type_id' => $typeId,
        ])->save();

        Sanctum::actingAs($user);

        $gradeId = DB::table('grades')->insertGetId(['grade_name' => '2027 A/L', 'created_at' => $now, 'updated_at' => $now]);
        $teacherId = DB::table('teachers')->insertGetId([
            'custom_id' => 'TC001', 'email' => 'teacher@example.com', 'mobile' => '0700000001',
            'nic' => '900000000V', 'full_name' => 'Teacher One', 'initials' => 'T',
            'bday' => '1990-01-01', 'gender' => 'male', 'address1' => 'Negombo', 'bank_branch_id' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('class_categories')->insertGetId(['category_name' => 'Theory', 'code' => 'THEORY', 'created_at' => $now, 'updated_at' => $now]);
        $subjectId = DB::table('subjects')->insertGetId(['subject_name' => 'Maths', 'created_at' => $now, 'updated_at' => $now]);

        $studentId = DB::table('students')->insertGetId([
            'custom_id' => 'ST029', 'initial_name' => 'A. Student', 'gender' => 'male',
            'guardian_mobile' => '0700000000', 'grade_id' => $gradeId, 'img_url' => 'x.png',
            'is_active' => true, 'student_disable' => false, 'permanent_qr_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $classId = DB::table('student_classes')->insertGetId([
            'class_name' => 'Maths Class', 'class_type' => 'offline',
            'teacher_id' => $teacherId, 'subject_id' => $subjectId, 'grade_id' => $gradeId,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $feeId = DB::table('class_category_fees')->insertGetId([
            'student_class_id' => $classId, 'class_category_id' => $categoryId, 'fee' => 2500,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->enrollmentId = DB::table('student_class_enrollments')->insertGetId([
            'student_id' => $studentId, 'student_class_id' => $classId,
            'class_category_fee_id' => $feeId, 'is_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function test_read_returns_student_and_classes(): void
    {
        $this->postJson('/api/new-payment/read', ['code' => 'ST029'])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.student.custom_id', 'ST029')
            ->assertJsonPath('data.classes.0.enrollment_id', $this->enrollmentId);
    }

    public function test_read_marks_free_card_and_fee_option(): void
    {
        $optionId = DB::table('class_category_fee_options')->insertGetId([
            'class_category_fee_id' => DB::table('student_class_enrollments')->where('id', $this->enrollmentId)->value('class_category_fee_id'),
            'label' => 'Full fee', 'fee' => 2500, 'is_default' => true, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('student_class_enrollments')->where('id', $this->enrollmentId)->update([
            'is_free_card' => true, 'class_category_fee_option_id' => $optionId,
        ]);

        $this->postJson('/api/new-payment/read', ['code' => 'ST029'])
            ->assertOk()
            ->assertJsonPath('data.classes.0.is_free_card', true)
            ->assertJsonPath('data.classes.0.final_fee', 0)
            ->assertJsonPath('data.classes.0.fee_option.label', 'Full fee');
    }

    public function test_read_unknown_code_returns_message(): void
    {
        $this->postJson('/api/new-payment/read', ['code' => 'NOPE'])
            ->assertStatus(404)
            ->assertJsonPath('status', 'error');
    }

    public function test_bulk_pay_saves_payment(): void
    {
        $this->postJson('/api/new-payment/bulk-pay', [
            'payments' => [[
                'code' => 'ST029',
                'enrollment_id' => $this->enrollmentId,
                'amount' => 2500,
                'payment_month' => '2026-10-01',
                'payment_method' => 'cash',
                'mark_method' => 'qr_mobile',
                'note' => 'test',
            ]],
        ])->assertStatus(201)->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('payments', [
            'student_class_enrollment_id' => $this->enrollmentId,
            'status' => 'completed',
        ]);
    }

    public function test_single_pay_is_not_exposed_to_the_app(): void
    {
        $this->postJson('/api/new-payment/pay', [])->assertNotFound();
    }

    public function test_requires_login(): void
    {
        $this->app['auth']->forgetGuards();

        $this->withHeaders(['Authorization' => ''])
            ->postJson('/api/new-payment/read', ['code' => 'ST029'])
            ->assertUnauthorized();
    }
}
