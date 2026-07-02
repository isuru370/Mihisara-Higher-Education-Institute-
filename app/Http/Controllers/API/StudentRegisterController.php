<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\AdmissionPayment;
use App\Models\Student;
use App\Services\StudentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StudentRegisterController extends Controller
{
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function QuickStudentStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'temporary_qr_code' => [
                'required',
                'string',
                'regex:/^TMP\d{3,}$/',
                'unique:students,temporary_qr_code',
                'exists:temporary_id_cards,temporary_id_number',
                function ($attribute, $value, $fail) {
                    $error = $this->studentService->validateTemporaryQrCode($value);
                    if ($error) {
                        $fail($error);
                    }
                },
            ],
            'quick_image_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!$this->studentService->validateQuickPhoto($value)) {
                        $fail('Quick image is invalid or already used.');
                    }
                }
            ],
            'initial_name' => 'required|string|max:100',
            'guardian_mobile' => 'required|string|max:20',
            'grade_id' => 'required|exists:grades,id',
            'gender' => 'required|in:male,female,other',
            'admission_id' => 'nullable|exists:admissions,id',
        ]);

        $studentData = DB::transaction(function () use ($validated) {
            // Generate custom ID
            $customId = $this->studentService->generateCustomId((int) $validated['grade_id']);

            // Get image URL
            $imgUrl = $this->studentService->getDefaultImageUrl($validated['gender']);

            // Assign quick photo if available
            $quickPhotoPath = $this->studentService->assignQuickPhoto(
                $validated['quick_image_id'],
                new Student() // Temporary - will be updated after student creation
            );

            if ($quickPhotoPath) {
                $imgUrl = $quickPhotoPath;
            }

            // Create student
            $student = Student::create([
                'custom_id' => $customId,
                'temporary_qr_code' => $validated['temporary_qr_code'],
                'temporary_qr_code_expire_date' => Carbon::now('Asia/Colombo')->addMonths(2),
                'initial_name' => $validated['initial_name'],
                'guardian_mobile' => $validated['guardian_mobile'],
                'grade_id' => $validated['grade_id'],
                'gender' => $validated['gender'],
                'img_url' => $imgUrl,
            ]);

            // Create portal login
            $this->studentService->createStudentPortalLogin($student);

            // Assign temporary card
            $this->studentService->assignTemporaryCard($student, $validated['temporary_qr_code']);

            // Create student ID card
            $this->studentService->createStudentIdCard($student, 'incomplete');

            // Handle admission payment
            $admissionPayment = null;
            if (!empty($validated['admission_id'])) {
                $admissionPayment = $this->studentService->createAdmissionPayment(
                    $student,
                    $validated['admission_id']
                );
            }

            $student->load('grade');

            return [
                'student' => $student,
                'admissionPayment' => $admissionPayment,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Student registered successfully',
            'student' => $studentData['student'],
            'admission_payment' => $studentData['admissionPayment']
                ? [
                    'id' => $studentData['admissionPayment']->id,
                    'receipt_number' => $studentData['admissionPayment']->receipt_number,
                    'amount' => $studentData['admissionPayment']->amount,
                    'status' => $studentData['admissionPayment']->status,
                    'paid_at' => $studentData['admissionPayment']->paid_at,
                    'payment_method' => $studentData['admissionPayment']->payment_method,
                    'admission_name' => $studentData['admissionPayment']->admission?->name,
                ]
                : null,
        ], 201);
    }
}
