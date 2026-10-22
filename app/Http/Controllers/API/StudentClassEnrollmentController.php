<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassCategoryFee;
use App\Models\ClassCategoryFeeOption;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Services\StudentQRService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class StudentClassEnrollmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Read Student Class
    |--------------------------------------------------------------------------
    */

    public function readStudentClass(Request $request)
    {
        $validated = $request->validate([
            'qr_code' => ['required', 'string', 'max:150'],
        ]);

        try {
            $qrResult = StudentQRService::read($validated['qr_code']);

            if (!($qrResult['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $qrResult['message'] ?? 'Invalid QR code.',
                ], $qrResult['status_code'] ?? 422);
            }

            $studentId = $qrResult['student_id'] ?? null;

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID not found in QR result.',
                ], 422);
            }

            $student = Student::query()
                ->with('grade')
                ->find($studentId);

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found.',
                ], 404);
            }

            $classesResponse = $this->fetchStudentClasses((int) $studentId);
            $classesData = $classesResponse->getData(true);

            return response()->json([
                'success' => true,

                'student' => [
                    'id' => $student->id,

                    'custom_id' => $student->permanent_qr_active
                        ? $student->custom_id
                        : $student->temporary_qr_code,

                    'temporary_qr_code' => $student->temporary_qr_code,

                    'initial_name' => $student->initial_name,

                    'img_url' => $student->img_url,

                    'grade_id' => $student->grade?->id,

                    'grade_name' => $student->grade?->grade_name,
                ],

                'data' => $classesData['data'] ?? [],
            ]);
        } catch (Exception $e) {

            Log::error('readStudentClass failed', [
                'qr_code' => $validated['qr_code'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to read QR code.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Store Enrollment
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
            ],

            'student_class_id' => [
                'required',
                'integer',
                'exists:student_classes,id',
            ],

            'class_category_fee_id' => [
                'required',
                'integer',
                'exists:class_category_fees,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Fee Option
            |--------------------------------------------------------------------------
            */

            'class_category_fee_option_id' => [
                'nullable',
                'integer',
                'exists:class_category_fee_options,id',
            ],

            'is_free_card' => [
                'nullable',
                'boolean',
            ],

            'custom_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'custom_fee_reason' => [
                'nullable',
                'string',
                'max:150',
            ],

            'discount_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'discount_reason' => [
                'nullable',
                'string',
                'max:150',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        try {

            DB::transaction(function () use ($validated) {

                /*
                |--------------------------------------------------------------------------
                | Validate Category Fee
                |--------------------------------------------------------------------------
                */

                $feeExists = ClassCategoryFee::query()
                    ->where('id', $validated['class_category_fee_id'])
                    ->where('student_class_id', $validated['student_class_id'])
                    ->where('is_active', true)
                    ->exists();

                if (!$feeExists) {

                    throw ValidationException::withMessages([
                        'message' =>
                            'Selected category fee is not assigned to this class or inactive.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Fee Option
                |--------------------------------------------------------------------------
                */

                if (!empty($validated['class_category_fee_option_id'])) {

                    $optionExists = ClassCategoryFeeOption::query()
                        ->where(
                            'id',
                            $validated['class_category_fee_option_id']
                        )
                        ->where(
                            'class_category_fee_id',
                            $validated['class_category_fee_id']
                        )
                        ->where('is_active', true)
                        ->exists();

                    if (!$optionExists) {

                        throw ValidationException::withMessages([
                            'message' =>
                                'Selected fee option is not valid for this category.',
                        ]);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Check Existing Enrollment
                |--------------------------------------------------------------------------
                */

                $existing = StudentClassEnrollment::withTrashed()
                    ->where(
                        'student_id',
                        $validated['student_id']
                    )
                    ->where(
                        'student_class_id',
                        $validated['student_class_id']
                    )
                    ->where(
                        'class_category_fee_id',
                        $validated['class_category_fee_id']
                    )
                    ->first();

                if ($existing) {

                    throw ValidationException::withMessages([
                        'message' =>
                            'Student is already enrolled in this class category.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Create Enrollment
                |--------------------------------------------------------------------------
                */

                $data = [
                    ...$validated,

                    'is_active' => true,

                    'is_free_card' =>
                        $validated['is_free_card'] ?? false,

                    'enrolled_at' =>
                        now()->toDateString(),

                    'left_at' => null,
                ];

                StudentClassEnrollment::create($data);
            });


            return response()->json([
                'status' => 'success',
                'message' => 'Student enrolled successfully.',
            ], 201);


        } catch (ValidationException $e) {

            return response()->json([
                'message' =>
                    collect($e->errors())
                        ->flatten()
                        ->first()
                    ?? 'Validation failed.',
            ], 422);


        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'message' =>
                    'Something went wrong while enrolling student.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Enrollment
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        StudentClassEnrollment $enrollment
    ): JsonResponse {

        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | Student ID & Student Class ID cannot be changed
            |--------------------------------------------------------------------------
            */

            'class_category_fee_id' => [
                'required',
                'integer',
                'exists:class_category_fees,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Fee Option
            |--------------------------------------------------------------------------
            */

            'class_category_fee_option_id' => [
                'nullable',
                'integer',
                'exists:class_category_fee_options,id',
            ],

            'is_free_card' => [
                'nullable',
                'boolean',
            ],

            'custom_fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'custom_fee_reason' => [
                'nullable',
                'string',
                'max:150',
            ],

            'discount_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'discount_reason' => [
                'nullable',
                'string',
                'max:150',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);


        try {

            DB::transaction(function () use (
                $validated,
                $enrollment
            ) {

                /*
                |--------------------------------------------------------------------------
                | Validate Category Fee Belongs To Same Class
                |--------------------------------------------------------------------------
                */

                $feeExists = ClassCategoryFee::query()
                    ->where(
                        'id',
                        $validated['class_category_fee_id']
                    )
                    ->where(
                        'student_class_id',
                        $enrollment->student_class_id
                    )
                    ->where('is_active', true)
                    ->exists();

                if (!$feeExists) {

                    throw ValidationException::withMessages([
                        'message' =>
                            'Selected category fee is not assigned to this class or inactive.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Fee Option
                |--------------------------------------------------------------------------
                */

                if (!empty($validated['class_category_fee_option_id'])) {

                    $optionExists = ClassCategoryFeeOption::query()
                        ->where(
                            'id',
                            $validated['class_category_fee_option_id']
                        )
                        ->where(
                            'class_category_fee_id',
                            $validated['class_category_fee_id']
                        )
                        ->where('is_active', true)
                        ->exists();

                    if (!$optionExists) {

                        throw ValidationException::withMessages([
                            'message' =>
                                'Selected fee option is not valid for this category.',
                        ]);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Duplicate Check
                |--------------------------------------------------------------------------
                */

                $existing = StudentClassEnrollment::withTrashed()
                    ->where(
                        'student_id',
                        $enrollment->student_id
                    )
                    ->where(
                        'student_class_id',
                        $enrollment->student_class_id
                    )
                    ->where(
                        'class_category_fee_id',
                        $validated['class_category_fee_id']
                    )
                    ->where(
                        'id',
                        '!=',
                        $enrollment->id
                    )
                    ->first();


                if (
                    $existing &&
                    !$existing->trashed() &&
                    $existing->is_active
                ) {

                    throw ValidationException::withMessages([
                        'message' =>
                            'Student is already enrolled in this class category.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Update Enrollment
                |--------------------------------------------------------------------------
                */

                $data = [
                    ...$validated,

                    'is_free_card' =>
                        $validated['is_free_card'] ?? false,

                    'enrolled_at' =>
                        $validated['enrolled_at']
                        ?? $enrollment->enrolled_at,
                ];

                $enrollment->update($data);
            });


            return response()->json([
                'status' => 'success',
                'message' => 'Student enrollment updated successfully.',
            ]);


        } catch (ValidationException $e) {

            return response()->json([
                'message' =>
                    collect($e->errors())
                        ->flatten()
                        ->first()
                    ?? 'Validation failed.',
            ], 422);


        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'message' =>
                    'Something went wrong while updating enrollment.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Class Status
    |--------------------------------------------------------------------------
    */

    public function toggleClassStatusChange(
        int $enrollmentId
    ): JsonResponse {

        try {

            $enrollment =
                StudentClassEnrollment::findOrFail(
                    $enrollmentId
                );


            $newStatus =
                !$enrollment->is_active;


            $enrollment->update([
                'is_active' => $newStatus,

                'left_at' =>
                    $newStatus
                    ? null
                    : now(),
            ]);


            return response()->json([

                'success' => true,

                'message' =>
                    $newStatus
                    ? 'Class activated successfully'
                    : 'Class deactivated successfully',

                'data' => [

                    'enrollment_id' =>
                        $enrollment->id,

                    'is_active' =>
                        $enrollment->is_active,

                    'left_at' =>
                        $enrollment->left_at,
                ],
            ]);


        } catch (Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Fetch Student Classes
    |--------------------------------------------------------------------------
    */

    public function fetchStudentClasses(
        int $studentId
    ) {

        try {

            $enrollments =
                StudentClassEnrollment::with([

                    'studentClass.teacher',

                    'studentClass.grade',

                    'classCategoryFee.category',

                    /*
                    |--------------------------------------------------------------------------
                    | Selected Fee Option
                    |--------------------------------------------------------------------------
                    */

                    'classCategoryFeeOption',

                ])
                    ->where(
                        'student_id',
                        $studentId
                    )
                    ->get();


            $data = $enrollments->map(
                function ($enrollment) {

                    /*
                    |--------------------------------------------------------------------------
                    | Determine Fee
                    |--------------------------------------------------------------------------
                    */

                    $optionFee = null;

                    if (
                        $enrollment->classCategoryFeeOption
                    ) {

                        $optionFee =
                            (float)
                            $enrollment
                                ->classCategoryFeeOption
                                ->fee;
                    }


                    $categoryFee = null;

                    if (
                        $enrollment->classCategoryFee
                    ) {

                        $categoryFee =
                            (float)
                            $enrollment
                                ->classCategoryFee
                                ->fee;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Selected Fee
                    |--------------------------------------------------------------------------
                    */

                    $selectedFee =
                        $optionFee !== null
                        ? $optionFee
                        : ($categoryFee ?? 0);


                    return [

                        /*
                        |--------------------------------------------------------------------------
                        | Enrollment
                        |--------------------------------------------------------------------------
                        */

                        'enrollment_id' =>
                            $enrollment->id,


                        /*
                        |--------------------------------------------------------------------------
                        | Class
                        |--------------------------------------------------------------------------
                        */

                        'class_id' =>
                            $enrollment->studentClass?->id,

                        'class_name' =>
                            $enrollment->studentClass?->class_name,

                        'grade_name' =>
                            $enrollment
                                ->studentClass
                                ?->grade
                                ?->grade_name,

                        'teacher_name' =>
                            $enrollment
                                ->studentClass
                                ?->teacher
                                ?->full_name,


                        /*
                        |--------------------------------------------------------------------------
                        | Category
                        |--------------------------------------------------------------------------
                        */

                        'class_category_fee_id' =>
                            $enrollment
                                ->class_category_fee_id,

                        'category_name' =>
                            $enrollment
                                ->classCategoryFee
                                ?->category
                                ?->category_name,


                        /*
                        |--------------------------------------------------------------------------
                        | Fee Option
                        |--------------------------------------------------------------------------
                        */

                        'class_category_fee_option_id' =>
                            $enrollment
                                ->class_category_fee_option_id,

                        'fee_option_label' =>
                            $enrollment
                                ->classCategoryFeeOption
                                ?->label,

                        'fee_option_fee' =>
                            $enrollment
                                ->classCategoryFeeOption
                                ?->fee,


                        /*
                        |--------------------------------------------------------------------------
                        | Status
                        |--------------------------------------------------------------------------
                        */

                        'is_active' =>
                            (bool)
                            $enrollment->is_active,

                        'is_free_card' =>
                            (bool)
                            $enrollment->is_free_card,


                        /*
                        |--------------------------------------------------------------------------
                        | Fee
                        |--------------------------------------------------------------------------
                        */

                        'defult_fee' =>
                            $selectedFee,

                        'final_fee' =>
                            $enrollment->final_fee,

                        'paid_amount' =>
                            $enrollment->paid_amount,

                        'balance' =>
                            $enrollment->balance,

                        'payment_status' =>
                            $enrollment->payment_status,


                        /*
                        |--------------------------------------------------------------------------
                        | Registered Date
                        |--------------------------------------------------------------------------
                        */

                        'registered_date' =>
                            $enrollment->enrolled_at
                            ? $enrollment
                                ->enrolled_at
                                ->format('Y-m-d')
                            : null,
                    ];
                }
            );


            return response()->json([

                'success' => true,

                'data' => $data,

            ]);


        } catch (Exception $e) {

            Log::error(
                'fetchStudentClasses failed',
                [
                    'student_id' =>
                        $studentId,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            return response()->json([

                'success' => false,

                'message' =>
                    'Unable to fetch student classes.',

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Deactivate Enrollment
    |--------------------------------------------------------------------------
    */

    public function deactivateEnrollment(
        int $enrollmentId
    ): JsonResponse {

        try {

            $enrollment =
                StudentClassEnrollment::findOrFail(
                    $enrollmentId
                );


            if (!$enrollment->is_active) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Student enrollment is already inactive.',

                ], 422);
            }


            $enrollment->update([

                'is_active' => false,

                'left_at' =>
                    now()->toDateString(),
            ]);


            return response()->json([

                'success' => true,

                'message' =>
                    'Student removed from class successfully.',

            ]);


        } catch (Exception $e) {

            Log::error(
                'Student Enrollment Deactivation Error',
                [

                    'enrollment_id' =>
                        $enrollmentId,

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            return response()->json([

                'success' => false,

                'message' =>
                    'Unable to remove student from class.',

            ], 500);
        }
    }
}