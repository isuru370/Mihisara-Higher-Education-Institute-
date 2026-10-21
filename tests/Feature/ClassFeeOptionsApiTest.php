<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Mobile "Add Class": choose grade -> class -> category -> fee option.
 */
class ClassFeeOptionsApiTest extends TestCase
{
    use RefreshDatabase;

    private int $gradeId;
    private int $studentId;
    private int $classId;
    private int $feeId;
    private int $optionId;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->gradeId = DB::table('grades')->insertGetId(['grade_name' => '2027 A/L', 'created_at' => $now, 'updated_at' => $now]);
        $teacherId = DB::table('teachers')->insertGetId([
            'custom_id' => 'TC001', 'email' => 'teacher@example.com', 'mobile' => '0700000001',
            'nic' => '900000000V', 'full_name' => 'Teacher One', 'initials' => 'T',
            'bday' => '1990-01-01', 'gender' => 'male', 'address1' => 'Negombo', 'bank_branch_id' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = DB::table('class_categories')->insertGetId(['category_name' => 'Theory', 'code' => 'THEORY', 'created_at' => $now, 'updated_at' => $now]);
        $subjectId = DB::table('subjects')->insertGetId(['subject_name' => 'Maths', 'created_at' => $now, 'updated_at' => $now]);

        $this->studentId = DB::table('students')->insertGetId([
            'custom_id' => 'ST029', 'initial_name' => 'A. Student', 'gender' => 'male',
            'guardian_mobile' => '0700000000', 'grade_id' => $this->gradeId, 'img_url' => 'x.png',
            'is_active' => true, 'student_disable' => false, 'permanent_qr_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->classId = DB::table('student_classes')->insertGetId([
            'class_name' => 'Maths Class', 'class_type' => 'offline', 'is_active' => true,
            'teacher_id' => $teacherId, 'subject_id' => $subjectId, 'grade_id' => $this->gradeId,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->feeId = DB::table('class_category_fees')->insertGetId([
            'student_class_id' => $this->classId, 'class_category_id' => $categoryId, 'fee' => 2500,
            'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->optionId = DB::table('class_category_fee_options')->insertGetId([
            'class_category_fee_id' => $this->feeId, 'label' => 'Full fee', 'fee' => 2500,
            'is_default' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function test_classes_by_grade_include_fee_options_for_each_category(): void
    {
        $this->getJson("/api/student-classes/{$this->gradeId}")
            ->assertOk()
            ->assertJsonPath('data.0.class_id', $this->classId)
            ->assertJsonPath('data.0.category_fees.0.class_category_fee_id', $this->feeId)
            ->assertJsonPath('data.0.category_fees.0.fee_options.0.id', $this->optionId)
            ->assertJsonPath('data.0.category_fees.0.fee_options.0.label', 'Full fee')
            ->assertJsonPath('data.0.category_fees.0.fee_options.0.is_default', true)
            ->assertJsonPath('data.0.category_fees.0.fee_options.0.is_active', true);
    }

    public function test_enrollment_saves_the_chosen_fee_option(): void
    {
        $this->postJson('/api/student-class-enrollments', [
            'student_id' => $this->studentId,
            'student_class_id' => $this->classId,
            'class_category_fee_id' => $this->feeId,
            'class_category_fee_option_id' => $this->optionId,
            'is_free_card' => false,
            'note' => 'test',
        ])->assertSuccessful();

        $this->assertDatabaseHas('student_class_enrollments', [
            'student_id' => $this->studentId,
            'class_category_fee_option_id' => $this->optionId,
        ]);
    }
}
