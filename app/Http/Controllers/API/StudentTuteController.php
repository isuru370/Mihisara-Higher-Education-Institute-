<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Illuminate\Http\Request;
use App\Models\StudentTute;
use App\Services\StudentQRService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class StudentTuteController extends Controller
{
    public function readStudentTute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string', 'max:150'],
        ]);

        try {
            $qrResult = StudentQRService::read($validated['qr_code']);

            if (! $qrResult['success']) {
                return $this->errorResponse(
                    $qrResult['message'],
                    $qrResult['status_code']
                );
            }

            $student = $this->getActiveStudent((int) $qrResult['student_id']);

            if (! $student) {
                return $this->errorResponse(
                    'Student not found or inactive',
                    404
                );
            }

            $now = Carbon::now();

            $enrollments = StudentClassEnrollment::with([
                'studentClass.teacher',
                'studentClass.grade',
                'classCategoryFee.category',
            ])
                ->where('student_id', $student->id)
                ->where('is_active', true)
                ->get()
                ->map(function ($enrollment) use ($student, $now) {
                    $studentClass = $enrollment->studentClass;

                    $tute = StudentTute::where('student_id', $student->id)
                        ->where('student_class_enrollment_id', $enrollment->id)
                        ->whereYear('issued_at', $now->year)
                        ->whereMonth('issued_at', $now->month)
                        ->latest('issued_at')
                        ->first();

                    return [
                        'enrollment_id' => $enrollment->id,
                        'student_class_id' => $studentClass?->id,
                        'class_name' => $studentClass?->class_name,
                        'grade_name' => $studentClass?->grade?->grade_name
                            ?? $studentClass?->grade?->name
                            ?? null,
                        'category_name' => $enrollment->classCategoryFee?->category?->category_name,
                        'teacher_name' => $studentClass?->teacher->initials,
                        'payment_status' => $enrollment->payment_status,
                        'final_fee' => $enrollment->final_fee,
                        'tute_issued' => (bool) $tute,
                        'tute_status' => $tute ? 'issued' : 'not_issued',
                        'tute_issued_at' => $tute?->issued_at?->toDateTimeString(),
                    ];
                });

            return $this->successResponse([
                'student' => [
                    'id' => $student->id,
                    'custom_id' => $student->permanent_qr_active
                        ? $student->custom_id
                        : $student->temporary_qr_code,
                    'initial_name' => $student->initial_name,
                    'guardian_mobile' => $student->guardian_mobile,
                    'img_url' => $student->img_url,
                ],
                'year' => $now->year,
                'month' => $now->month,
                'enrollments' => $enrollments,
            ]);
        } catch (\Throwable $e) {
            Log::error('Read Student Tute Error', [
                'qr_code' => $validated['qr_code'],
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Something went wrong', 500);
        }
    }
    public function store(Request $request)
    {
        try {

            // validation
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'student_class_enrollment_id' => 'required|exists:student_class_enrollments,id',
                'issued_month' => 'required|date',
                'is_issued' => 'nullable|boolean',
                'issued_at' => 'nullable|date',
                'note' => 'nullable|string',
            ]);

            // duplicate check
            $alreadyExists = StudentTute::where('student_id', $validated['student_id'])
                ->where('student_class_enrollment_id', $validated['student_class_enrollment_id'])
                ->whereDate('issued_month', $validated['issued_month'])
                ->exists();

            if ($alreadyExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tute already issued for this month',
                ], 409);
            }

            // create record
            $studentTute = StudentTute::create([
                'student_id' => $validated['student_id'],
                'student_class_enrollment_id' => $validated['student_class_enrollment_id'],
                'issued_month' => $validated['issued_month'],
                'is_issued' => $validated['is_issued'] ?? false,
                'issued_at' => $validated['issued_at'] ?? null,
                'issued_by' => auth()->id(),
                'note' => $validated['note'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student tute created successfully',
                'data' => $studentTute
            ], 201);
        } catch (\Exception $e) {

            Log::error('Student Tute Store Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function studentTuteHistory(int $studentId, int $enrolledId)
    {
        try {

            $tuteHistory = StudentTute::with([
                'issuedBy:id,name'
            ])
                ->where('student_id', $studentId)
                ->where('student_class_enrollment_id', $enrolledId)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Student tute history fetched successfully',
                'data' => $tuteHistory
            ], 200);
        } catch (\Exception $e) {

            Log::error('Student Tute History Error', [
                'student_id' => $studentId,
                'enrollment_id' => $enrolledId,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getActiveStudent(int $studentId): ?Student
    {
        return Student::where('id', $studentId)
            ->where('is_active', true)
            ->first();
    }
    private function successResponse(
        $data = null,
        string $message = 'Success',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(
        string $message = 'Error',
        int $status = 400,
        $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
