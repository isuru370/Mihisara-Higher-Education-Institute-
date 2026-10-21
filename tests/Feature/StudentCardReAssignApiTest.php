<?php

namespace Tests\Feature;

use App\Models\StudentCard;
use App\Models\User;
use App\Services\StudentCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * POST /api/re-assign, as the mobile Re-Assign Card page calls it.
 */
class StudentCardReAssignApiTest extends TestCase
{
    use RefreshDatabase;

    private int $studentId;

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

        $gradeId = DB::table('grades')->insertGetId(['grade_name' => '2027 A/L', 'created_at' => $now, 'updated_at' => $now]);

        $this->studentId = DB::table('students')->insertGetId([
            'custom_id' => 'MHE0001', 'initial_name' => 'A. Student', 'gender' => 'male',
            'guardian_mobile' => '0700000000', 'grade_id' => $gradeId, 'img_url' => 'x.png',
            'is_active' => true, 'student_disable' => false, 'permanent_qr_active' => true,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $this->card(1, 'MHE0001', 'assigned', $this->studentId, true);
        $this->card(2, 'MHE0002', 'available');
    }

    public function test_reassign_moves_student_to_new_card(): void
    {
        $this->postJson('/api/re-assign', [
            'old_qr_code' => 'MHE0001',
            'new_qr_code' => 'MHE0002',
            'reason' => 'damaged',
            'remarks' => null,
        ])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.qr_code', 'MHE0002');

        $this->assertDatabaseHas('students', ['id' => $this->studentId, 'custom_id' => 'MHE0002']);
        $this->assertDatabaseHas('student_cards', ['qr_code' => 'MHE0001', 'status' => 'inactive', 'is_current' => false]);
        $this->assertDatabaseHas('student_cards', ['qr_code' => 'MHE0002', 'status' => 'assigned', 'student_id' => $this->studentId]);
        $this->assertDatabaseHas('student_card_histories', ['action' => 'replaced', 'reason' => 'damaged']);
    }

    public function test_reassign_with_lowercase_new_code(): void
    {
        $this->postJson('/api/re-assign', [
            'old_qr_code' => 'MHE0001',
            'new_qr_code' => 'mhe0002',
            'reason' => 'lost',
        ])->assertOk();
    }

    public function test_mark_lost_then_reassign(): void
    {
        // Used to fail: markLost set students.custom_id (NOT NULL) to null.
        app(StudentCardService::class)->markLost(StudentCard::where('qr_code', 'MHE0001')->firstOrFail());

        $this->assertDatabaseHas('student_cards', ['qr_code' => 'MHE0001', 'status' => 'lost']);
        $this->assertDatabaseHas('students', ['id' => $this->studentId, 'student_disable' => true]);

        $this->postJson('/api/re-assign', [
            'old_qr_code' => 'MHE0001',
            'new_qr_code' => 'MHE0002',
            'reason' => 'lost',
        ])->assertOk();

        $this->assertDatabaseHas('students', ['id' => $this->studentId, 'custom_id' => 'MHE0002', 'student_disable' => false]);
    }

    public function test_damaged_card_can_be_replaced(): void
    {
        app(StudentCardService::class)->markDamaged(StudentCard::where('qr_code', 'MHE0001')->firstOrFail());

        $this->postJson('/api/re-assign', [
            'old_qr_code' => 'MHE0001',
            'new_qr_code' => 'MHE0002',
            'reason' => 'damaged',
        ])->assertOk();

        $this->assertDatabaseHas('students', ['id' => $this->studentId, 'custom_id' => 'MHE0002']);
    }

    public function test_old_card_is_no_longer_active_after_reassign(): void
    {
        $this->postJson('/api/re-assign', [
            'old_qr_code' => 'MHE0001',
            'new_qr_code' => 'MHE0002',
            'reason' => 'damaged',
        ])->assertOk();

        // Scanning the old card now shows the new card as current.
        $this->postJson('/api/qr-code', ['card_qr_code' => 'MHE0001'])
            ->assertOk()
            ->assertJsonPath('message', 'This card is no longer active.')
            ->assertJsonPath('current_card.qr_code', 'MHE0002');
    }

    public function test_unknown_new_card_returns_404_message(): void
    {
        $this->postJson('/api/re-assign', [
            'old_qr_code' => 'MHE0001',
            'new_qr_code' => 'NOPE',
            'reason' => 'damaged',
        ])->assertStatus(404)->assertJsonPath('status', false);
    }

    private function card(int $seq, string $qr, string $status, ?int $studentId = null, bool $current = false): void
    {
        DB::table('student_cards')->insert([
            'student_id' => $studentId, 'card_sequence' => $seq,
            'card_number' => sprintf('C%05d', $seq), 'qr_code' => $qr,
            'status' => $status, 'is_current' => $current,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
