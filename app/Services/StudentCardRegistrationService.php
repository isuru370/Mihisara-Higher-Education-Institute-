<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentCardRegistrationService
{
    public function __construct(
        private StudentService $studentService,
        private StudentCardService $studentCardService
    ) {}

    /**
     * Show Registration Form
     */
    public function createForm()
    {
        $grades = Grade::orderBy('grade_name')->get();

        $admissions = Admission::active()
            ->orderBy('name')
            ->get();

        return view(
            'admin.student-card-registration.create',
            compact(
                'grades',
                'admissions'
            )
        );
    }

    /**
     * Register Student
     */
    public function register(Request $request): array
    {
        $qrCode = trim($request->card_qr_code);

        if (empty($qrCode)) {
            throw new \Exception('Student Card QR Code is required.');
        }

        $card = $this->studentCardService
            ->findAvailableCardByQr($qrCode);

        // ==========================
        // Prepare Student Data
        // ==========================

        $data = $request->except([
            'card_qr_code',
        ]);

        // QR Code becomes Student Custom ID
        $data['custom_id'] = $card->qr_code;



        // Handle Student Image (reuse existing method)
        $data = $request->except([
            'card_qr_code',
        ]);

        $data['custom_id'] = $card->qr_code;

        // Always set image
        $data['img_url'] = $this->studentService->handleStudentImage(
            $request->file('image'),
            $request->gender
        );

        $data['last_image_update_at'] = now();

        // Create Student
        // Create Student
        $student = Student::create($data);

        if (!$student) {
            throw new \Exception('Failed to create student.');
        }

        // Assign Physical Student Card
        $this->studentCardService->assignCard(
            $student->id,
            $card->id
        );

        // Create Student ID Card
        $this->studentService->createStudentIdCard(
            $student,
            'completed'
        );

        // Create Admission Payment
        if ($request->boolean('admission') && $request->filled('admission_id')) {

            $this->studentService->createAdmissionPayment(
                $student,
                (int) $request->admission_id
            );
        }

        // Create Student Portal Login
        $this->studentService->createStudentPortalLogin($student);

        return [
            'success' => 'Student registered successfully.'
        ];
    }


    
}
