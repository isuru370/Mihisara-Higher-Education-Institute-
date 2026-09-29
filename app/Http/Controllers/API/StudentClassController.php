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
                ->select(
                    'id',
                    'grade_name'
                )
                ->findOrFail($gradeId);


            $classes = StudentClassModel::query()
                ->with([

                    /*
                |--------------------------------------------------------------------------
                | Teacher
                |--------------------------------------------------------------------------
                */

                    'teacher:id,full_name',


                    /*
                |--------------------------------------------------------------------------
                | Grade
                |--------------------------------------------------------------------------
                */

                    'grade:id,grade_name',


                    /*
                |--------------------------------------------------------------------------
                | Category Fees
                |--------------------------------------------------------------------------
                */

                    'categoryFees' => function ($query) {

                        $query->select(
                            'id',
                            'student_class_id',
                            'class_category_id',
                            'fee',
                            'is_active'
                        )

                            ->with([

                                /*
                        |--------------------------------------------------------------------------
                        | Category
                        |--------------------------------------------------------------------------
                        */

                                'category:id,category_name',


                                /*
                        |--------------------------------------------------------------------------
                        | Fee Options
                        |--------------------------------------------------------------------------
                        */

                                'feeOptions' => function ($optionQuery) {

                                    $optionQuery
                                        ->select(
                                            'id',
                                            'class_category_fee_id',
                                            'label',
                                            'fee',
                                            'is_default',
                                            'is_active',
                                            'note'
                                        )
                                        ->whereNull('deleted_at')
                                        ->where(
                                            'is_active',
                                            true
                                        )
                                        ->orderByDesc(
                                            'is_default'
                                        )
                                        ->orderBy(
                                            'id'
                                        );
                                },

                            ])

                            ->whereNull('deleted_at')
                            ->where(
                                'is_active',
                                true
                            );
                    },

                ])

                ->where(
                    'grade_id',
                    $gradeId
                )

                ->where(
                    'is_active',
                    true
                )

                ->orderBy(
                    'class_name'
                )

                ->get()


                /*
            |--------------------------------------------------------------------------
            | Format Response
            |--------------------------------------------------------------------------
            */

                ->map(function ($class) {

                    return [

                        /*
                    |--------------------------------------------------------------------------
                    | Class
                    |--------------------------------------------------------------------------
                    */

                        'class_id' =>
                        $class->id,

                        'class_name' =>
                        $class->class_name,

                        'class_type' =>
                        $class->class_type,

                        'medium' =>
                        $class->medium,


                        /*
                    |--------------------------------------------------------------------------
                    | Grade
                    |--------------------------------------------------------------------------
                    */

                        'grade_id' =>
                        $class->grade_id,

                        'grade_name' =>
                        $class->grade?->grade_name,


                        /*
                    |--------------------------------------------------------------------------
                    | Teacher
                    |--------------------------------------------------------------------------
                    */

                        'teacher_id' =>
                        $class->teacher?->id,

                        'teacher_name' =>
                        $class->teacher?->full_name,


                        /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                        'is_active' =>
                        (bool) $class->is_active,

                        'is_ongoing' =>
                        (bool) $class->is_ongoing,


                        /*
                    |--------------------------------------------------------------------------
                    | Category Fees
                    |--------------------------------------------------------------------------
                    */

                        'category_fees' =>
                        $class->categoryFees
                            ->map(function ($feeRow) {

                                return [

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Category Fee
                                    |--------------------------------------------------------------------------
                                    */

                                    'class_category_fee_id' =>
                                    $feeRow->id,

                                    'class_category_id' =>
                                    $feeRow->class_category_id,

                                    'category_name' =>
                                    $feeRow
                                        ->category
                                        ?->category_name,

                                    'fee' =>
                                    (float) $feeRow->fee,

                                    'is_active' =>
                                    (bool) $feeRow->is_active,


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Fee Options
                                    |--------------------------------------------------------------------------
                                    */

                                    'fee_options' =>
                                    $feeRow->feeOptions
                                        ->map(function ($option) {

                                            return [

                                                'id' =>
                                                $option->id,

                                                'class_category_fee_id' =>
                                                $option
                                                    ->class_category_fee_id,

                                                'label' =>
                                                $option->label,

                                                'fee' =>
                                                (float) $option->fee,

                                                'is_default' =>
                                                (bool) $option->is_default,

                                                'is_active' =>
                                                (bool) $option->is_active,

                                                'note' =>
                                                $option->note,

                                            ];
                                        })
                                        ->values(),

                                ];
                            })
                            ->values(),

                    ];
                })
                ->values();


            /*
        |--------------------------------------------------------------------------
        | Success Response
        |--------------------------------------------------------------------------
        */

            return response()->json([

                'success' => true,

                'message' =>
                'Student classes fetched successfully',

                'grade' => [

                    'id' =>
                    $grade->id,

                    'grade_name' =>
                    $grade->grade_name,

                ],

                'data' =>
                $classes,

            ]);
        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage(),

            ], 500);
        }
    }
}
