<?php

namespace App\Services;

use App\Jobs\SendStudentPortalLoginSms;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentIdCard;
use App\Models\StudentPortalLogin;
use App\Models\TemporaryIdCard;
use App\Models\AdmissionPayment;
use App\Models\Admission;
use App\Models\QuickPhoto;
use App\Services\ParentHub\ParentHubService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class StudentService
{
    private ParentHubService $parentHubService;

    public function __construct(
        ParentHubService $parentHubService
    ) {
        $this->parentHubService = $parentHubService;
    }

    /**
     * Generate custom ID for student
     */
    public function generateCustomId(int $gradeId): string
    {
        $grade = Grade::findOrFail($gradeId);

        $gradeName = trim($grade->grade_name);
        $gradeCode = $this->extractGradeCode($gradeName, $gradeId);

        $lastStudent = Student::where('grade_id', $gradeId)
            ->where('custom_id', 'like', 'SA' . $gradeCode . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;

        if ($lastStudent && preg_match('/^SA' . preg_quote($gradeCode, '/') . '(\d+)$/', $lastStudent->custom_id, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        do {
            $lastNumber++;
            $customId = 'SA' . $gradeCode . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
        } while (Student::where('custom_id', $customId)->exists());

        return $customId;
    }

    /**
     * Extract grade code from grade name
     */
    private function extractGradeCode(string $gradeName, int $gradeId): string
    {
        if (preg_match('/^Grade\s+(\d+)$/i', $gradeName, $matches)) {
            return str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        }

        if (preg_match('/^(\d{4})\s+(A\/L|O\/L)$/i', $gradeName, $matches)) {
            return substr($matches[1], -2);
        }

        if (preg_match('/^\d{4}$/', $gradeName)) {
            return substr($gradeName, -2);
        }

        if (preg_match('/(\d+)/', $gradeName, $matches)) {
            $num = $matches[1];
            return strlen($num) === 4
                ? substr($num, -2)
                : str_pad($num, 2, '0', STR_PAD_LEFT);
        }

        return str_pad($gradeId, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Create student portal login and return plain password
     */
    public function createStudentPortalLogin(Student $student): string
    {
        $plainPassword = $this->generateStudentPassword(
            $student->initial_name,
            $student->guardian_mobile
        );

        StudentPortalLogin::create([
            'student_id' => $student->id,
            'username' => $student->custom_id,
            'password' => $plainPassword,
            'is_verified' => true,
            'is_active' => true,
        ]);

        DB::afterCommit(function () use ($student, $plainPassword) {

            $this->parentHubService->registerStudent(
                $student->custom_id,
                $student->custom_id
            );

            SendStudentPortalLoginSms::dispatch(
                $student->guardian_mobile,
                $student->custom_id,
                $plainPassword
            );
        });

        return $plainPassword;
    }

    /**
     * Generate student password
     */

    private function generateStudentPassword(
        string $initialName,
        string $guardianMobile
    ): string {

        // Initials
        $letters = preg_replace('/[^A-Za-z]/', '', $initialName);
        $initials = strtoupper(substr($letters, 0, 2));

        if (strlen($initials) < 2) {
            $initials = str_pad($initials, 2, 'X');
        }

        // Last 4 digits of mobile
        $mobileDigits = preg_replace('/\D/', '', $guardianMobile);
        $lastFour = substr($mobileDigits, -4);

        // Random letters (Eg: Ed)
        $randomLetters =
            chr(random_int(65, 90)) .      // A-Z
            chr(random_int(97, 122));      // a-z

        return $initials . $lastFour . $randomLetters;
    }
    /**
     * Get default image URL based on gender
     */
    public function getDefaultImageUrl(string $gender): string
    {
        return $gender === 'female' ? 'uploads/female.png' : 'uploads/male.png';
    }

    /**
     * Create student ID card record
     */
    public function createStudentIdCard(Student $student, string $registrationStatus = 'incomplete'): void
    {
        StudentIdCard::create([
            'student_id' => $student->id,
            'status' => 'pending',
            'registration_status' => $registrationStatus,
            'student_fee' => 350,
            'print_cost' => 90,
            'profit' => 260,
            'is_reissue' => false,
        ]);
    }

    /**
     * Assign temporary card to student
     */
    public function assignTemporaryCard(Student $student, string $temporaryQrCode): void
    {
        $temporaryCard = TemporaryIdCard::where('temporary_id_number', $temporaryQrCode)
            ->lockForUpdate()
            ->first();

        if ($temporaryCard) {
            $temporaryCard->update([
                'student_id' => $student->id,
                'status' => 'active',
                'activated_at' => now(),
            ]);
        }
    }

    /**
     * Validate temporary QR code
     */
    public function validateTemporaryQrCode(string $temporaryQrCode): ?string
    {
        if ((int) substr($temporaryQrCode, 3) < 1) {
            return 'The temporary QR code must start from TMP001.';
        }

        $card = TemporaryIdCard::where('temporary_id_number', $temporaryQrCode)->first();

        if (!$card) {
            return 'Temporary QR card not found.';
        }

        if ($card->status === 'active') {
            return 'This temporary QR code is already active.';
        }

        if ($card->status === 'expired') {
            return 'This temporary QR code is expired.';
        }

        if ($card->status !== 'issued') {
            return 'This temporary QR code must be in ISSUED status before assigning to a student.';
        }

        return null; // Valid
    }

    /**
     * Create admission payment
     */
    public function createAdmissionPayment(Student $student, int $admissionId): ?AdmissionPayment
    {
        $admission = Admission::findOrFail($admissionId);

        $payment = AdmissionPayment::create([
            'student_id' => $student->id,
            'admission_id' => $admission->id,
            'amount' => $admission->amount,
            'payment_method' => 'cash',
            'status' => AdmissionPayment::STATUS_PAID,
            'paid_at' => now(),
            'user_id' => auth()->id(),
        ]);

        $student->update(['admission' => true]);

        return $payment;
    }

    /**
     * Handle quick photo assignment
     */
    public function assignQuickPhoto(string $quickImageId, Student $student): ?string
    {
        $quickPhoto = QuickPhoto::where('custom_id', $quickImageId)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if ($quickPhoto && !empty($quickPhoto->image_path)) {
            $quickPhoto->update(['is_active' => false]);
            return $quickPhoto->image_path;
        }

        return null;
    }

    /**
     * Validate quick photo
     */
    public function validateQuickPhoto(string $quickImageId): bool
    {
        return QuickPhoto::where('custom_id', $quickImageId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Sync admission payment for student update
     */
    public function syncAdmissionPayment(Student $student, bool $hasAdmission, ?int $admissionId): void
    {
        $latestPayment = AdmissionPayment::where('student_id', $student->id)
            ->latest('id')
            ->first();

        if ($hasAdmission && $admissionId) {
            $admission = Admission::active()->findOrFail($admissionId);

            $paymentData = [
                'student_id' => $student->id,
                'admission_id' => $admission->id,
                'amount' => $admission->amount,
                'payment_method' => 'cash',
                'status' => AdmissionPayment::STATUS_PAID,
                'paid_at' => now(),
                'user_id' => auth()->id(),
            ];

            if ($latestPayment) {
                $latestPayment->update($paymentData);
            } else {
                AdmissionPayment::create($paymentData);
            }
        } else {
            if ($latestPayment && $latestPayment->status !== AdmissionPayment::STATUS_CANCELLED) {
                $latestPayment->update([
                    'status' => AdmissionPayment::STATUS_CANCELLED,
                ]);
            }
        }
    }

    /**
     * Update student card registration status
     */
    public function updateStudentCardRegistrationStatus(Student $student, string $status = 'completed'): void
    {
        $studentCard = StudentIdCard::where('student_id', $student->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($studentCard) {
            $studentCard->update([
                'registration_status' => $status,
            ]);
        }
    }

    /**
     * Handle student image upload
     */
    public function handleStudentImage($file, ?string $gender = null): string
    {
        if ($file) {
            return $file->store('uploads/students/original', 'public');
        }

        return $gender ? $this->getDefaultImageUrl($gender) : 'uploads/male.png';
    }
}
