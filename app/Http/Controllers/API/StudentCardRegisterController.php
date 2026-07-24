<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentService;
use App\Services\StudentCardService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentCardRegisterController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private StudentCardService $studentCardService,
    ) {}

    /**
     * Register Student using Physical Student Card.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'card_qr_code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {

                    try {

                        $this->studentCardService
                            ->findAvailableCardByQr($value);
                    } catch (\Exception $e) {

                        $fail($e->getMessage());
                    }
                },
            ],

            'quick_image_id' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {

                    if ($value && !$this->studentService->validateQuickPhoto($value)) {
                        $fail('Quick image is invalid or already used.');
                    }
                },
            ],

            'initial_name' => 'required|string|max:100',
            'guardian_mobile' => 'required|string|max:20',
            'grade_id' => 'required|exists:grades,id',
            'gender' => 'required|in:male,female,other',
            'admission_id' => 'nullable|exists:admissions,id',

        ]);

        DB::beginTransaction();

        try {

            // Get Student Card
            $card = $this->studentCardService
                ->findAvailableCardByQr($validated['card_qr_code']);

            // Default Image
            $imgUrl = $this->studentService
                ->getDefaultImageUrl($validated['gender']);

            // Assign Quick Photo
            $imgUrl = $this->studentService->handleStudentImage(
                $request->file('image'),
                $validated['gender']
            );

            // If quick photo is provided, use it instead
            if (!empty($validated['quick_image_id'])) {

                $quickPhotoPath = $this->studentService
                    ->assignQuickPhoto($validated['quick_image_id']);

                if ($quickPhotoPath) {
                    $imgUrl = $quickPhotoPath;
                }
            }

            // Create Student
            $student = Student::create([
                'custom_id'        => $card->qr_code,
                'initial_name'     => $validated['initial_name'],
                'guardian_mobile'  => $validated['guardian_mobile'],
                'grade_id'         => $validated['grade_id'],
                'gender'           => $validated['gender'],
                'img_url'          => $imgUrl,
            ]);

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
            $admissionPayment = null;

            if (!empty($validated['admission_id'])) {

                $admissionPayment = $this->studentService
                    ->createAdmissionPayment(
                        $student,
                        $validated['admission_id']
                    );
            }

            // Create Student Portal Login
            $plainPassword = $this->studentService
                ->createStudentPortalLogin($student);

            $student->load('grade');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully.',
                'student' => $student,

                'portal' => [
                    'username' => $student->custom_id,
                    'password' => $plainPassword,
                ],

                'admission_payment' => $admissionPayment
                    ? [
                        'id' => $admissionPayment->id,
                        'receipt_number' => $admissionPayment->receipt_number,
                        'amount' => $admissionPayment->amount,
                        'status' => $admissionPayment->status,
                        'paid_at' => $admissionPayment->paid_at,
                        'payment_method' => $admissionPayment->payment_method,
                        'admission_name' => $admissionPayment->admission?->name,
                    ]
                    : null,

            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Student Card API Registration Failed', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'request' => $request->except(['quick_image_id']),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchQrcode(Request $request)
    {
        $validated = $request->validate([
            'card_qr_code' => ['required', 'string'],
        ]);

        return response()->json(
            $this->studentCardService->searchCard($validated['card_qr_code'])
        );
    }

    public function reAssign(Request $request)
    {
        $request->validate([
            'old_qr_code' => ['required', 'string'],
            'new_qr_code' => [
                'required',
                'string',
                'different:old_qr_code',
            ],
            'reason' => ['required', 'in:lost,damaged,worn_out,other'],
            'remarks' => ['nullable', 'string'],
        ]);

        try {

            $card = $this->studentCardService->replaceCard(
                $request->old_qr_code,
                $request->new_qr_code,
                $request->reason,
                $request->remarks,
            );

            return response()->json([
                'status' => true,
                'message' => 'Student card reassigned successfully.',
                'data' => [
                    'card_id' => $card->id,
                    'card_number' => $card->card_number,
                    'qr_code' => $card->qr_code,
                    'student_id' => $card->student_id,
                    'status' => $card->status,
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Card not found.',
            ], 404);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
