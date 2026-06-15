<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassCategoryFee;
use App\Models\Grade;
use App\Models\StudentClass as StudentClassModel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StudentClassController extends Controller
{
    public function fetchStudentClass(int $gradeId): JsonResponse
    {
        try {
            $grade = Grade::query()
                ->select('id', 'grade_name')
                ->findOrFail($gradeId);

            $classes = StudentClassModel::query()
                ->with([
                    'teacher:id,full_name',
                    'grade:id,grade_name',
                    'categoryFees' => function ($query) {
                        $query->select(
                            'id',
                            'student_class_id',
                            'class_category_id',
                            'fee',
                            'is_active'
                        )
                        ->with([
                            'category:id,category_name',
                        ])
                        ->whereNull('deleted_at')
                        ->where('is_active', true);
                    },
                ])
                ->where('grade_id', $gradeId)
                ->where('is_active', true)
                ->orderBy('class_name')
                ->get()
                ->map(function ($class) {
                    return [
                        'class_id' => $class->id,
                        'class_name' => $class->class_name,
                        'class_type' => $class->class_type,
                        'medium' => $class->medium,
                        'grade_id' => $class->grade_id,
                        'grade_name' => $class->grade?->grade_name,
                        'teacher_id' => $class->teacher?->id,
                        'teacher_name' => $class->teacher?->full_name,
                        'is_active' => (bool) $class->is_active,
                        'is_ongoing' => (bool) $class->is_ongoing,
                        'category_fees' => $class->categoryFees->map(function ($feeRow) {
                            return [
                                'class_category_fee_id' => $feeRow->id,
                                'class_category_id' => $feeRow->class_category_id,
                                'category_name' => $feeRow->category?->category_name,
                                'fee' => (float) $feeRow->fee,
                                'is_active' => (bool) $feeRow->is_active,
                            ];
                        })->values(),
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'message' => 'Student classes fetched successfully',
                'grade' => [
                    'id' => $grade->id,
                    'grade_name' => $grade->grade_name,
                ],
                'data' => $classes,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}