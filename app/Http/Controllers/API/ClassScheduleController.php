<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClassCategoryFee;
use App\Models\ClassSchedule;
use App\Models\ClassSchedulePattern;
use App\Models\StudentClass;
use App\Services\ClassScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClassScheduleController extends Controller
{
    protected ClassScheduleService $scheduleService;

    public function __construct(
        ClassScheduleService $scheduleService
    ) {
        $this->scheduleService = $scheduleService;
    }
    public function todayClasses(Request $request)
    {
        $today = Carbon::today();
        $search = trim($request->input('search', ''));

        $classes = StudentClass::query()
            ->select([
                'id',
                'class_name',
                'class_type',
                'medium',
                'grade_id',
                'subject_id',
                'teacher_id',
                'is_active',
                'is_ongoing',
            ])
            ->with([
                'grade:id,grade_name',
                'subject:id,subject_name',
                'teacher:id,full_name,mobile',

                'categoryFees' => function ($query) {
                    $query->select([
                        'id',
                        'student_class_id',
                        'class_category_id',
                        'fee',
                        'is_active',
                    ])
                        ->where('is_active', true)
                        ->with([
                            'category:id,category_name',
                        ]);
                },

                'schedules' => function ($query) use ($today) {
                    $query->select([
                        'id',
                        'student_class_id',
                        'class_category_fee_id',
                        'class_schedule_pattern_id',
                        'class_date',
                        'start_time',
                        'end_time',
                        'status',
                        'class_hall_id',
                    ])
                        ->whereDate('class_date', $today)
                        ->whereNotIn('status', ['cancelled', 'completed'])
                        ->with([
                            'hall:id,hall_name,code',
                        ])
                        ->orderBy('start_time');
                },
            ])
            ->where('is_active', true)
            ->where('is_ongoing', true)
            ->whereHas('schedules', function ($query) use ($today) {
                $query->whereDate('class_date', $today)
                    ->whereNotIn('status', ['cancelled', 'completed']);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('class_name', 'like', "%{$search}%")
                        ->orWhere('class_type', 'like', "%{$search}%")
                        ->orWhere('medium', 'like', "%{$search}%")
                        ->orWhereHas('grade', function ($grade) use ($search) {
                            $grade->where('grade_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('subject', function ($subject) use ($search) {
                            $subject->where('subject_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('teacher', function ($teacher) use ($search) {
                            $teacher->where('full_name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%");
                        })
                        ->orWhereHas('categoryFees.category', function ($category) use ($search) {
                            $category->where('category_name', 'like', "%{$search}%");
                        });
                });
            })
            ->get();

        $data = $classes->flatMap(function ($class) {
            return $class->categoryFees->flatMap(function ($fee) use ($class) {
                $matchedSchedules = $class->schedules->where('class_category_fee_id', $fee->id);

                return $matchedSchedules->map(function ($schedule) use ($class, $fee) {
                    return [
                        'student_class' => [
                            'id' => $class->id,
                            'class_name' => $class->class_name,
                            'class_type' => $class->class_type,
                            'medium' => $class->medium,
                            'grade' => $class->grade ? [
                                'id' => $class->grade->id,
                                'grade_name' => $class->grade->grade_name,
                            ] : null,
                            'subject' => $class->subject ? [
                                'id' => $class->subject->id,
                                'subject_name' => $class->subject->subject_name,
                            ] : null,
                            'teacher' => $class->teacher ? [
                                'id' => $class->teacher->id,
                                'full_name' => $class->teacher->full_name,
                                'mobile' => $class->teacher->mobile,
                            ] : null,
                        ],
                        'category_fee' => [
                            'id' => $fee->id,
                            'class_category_id' => $fee->class_category_id,
                            'fee' => $fee->fee,
                            'category' => $fee->category ? [
                                'id' => $fee->category->id,
                                'category_name' => $fee->category->category_name,
                            ] : null,
                        ],
                        'schedule' => [
                            'id' => $schedule->id,
                            'class_category_fee_id' => $schedule->class_category_fee_id,
                            'class_schedule_pattern_id' => $schedule->class_schedule_pattern_id,
                            'class_date' => $schedule->class_date,
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time,
                            'status' => $schedule->status,
                            'hall' => $schedule->hall ? [
                                'id' => $schedule->hall->id,
                                'hall_name' => $schedule->hall->hall_name,
                                'code' => $schedule->hall->code,
                            ] : null,
                        ],
                    ];
                });
            });
        })->values();

        $data = $data->sortBy(function ($row) {
            return $row['schedule']['start_time'] ?? '';
        })->values();

        return response()->json([
            'success' => true,
            'date' => $today->toDateString(),
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

    public function fetchOngoingClass()
    {
        try {

            $classes = StudentClass::with([
                'teacher:id,custom_id,initials',
                'subject:id,subject_name',
                'grade:id,grade_name'
            ])
                ->where('is_active', true)
                ->where('is_ongoing', true)
                ->get();

            return $this->successResponse(
                'Classes fetched successfully.',
                $classes
            );
        } catch (Throwable $e) {

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function fetchClassCategory(Request $request)
    {
        try {

            $request->validate([
                'class_id' => 'required|exists:student_classes,id',
            ]);

            $categories = ClassCategoryFee::with([
                'category:id,category_name'
            ])
                ->where('student_class_id', $request->class_id)
                ->where('is_active', true)
                ->get([
                    'id',
                    'student_class_id',
                    'class_category_id',
                    'fee',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Categories fetched successfully.',
                'data' => $categories,
            ]);
        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchClassSchedule(Request $request)
    {
        try {
            $request->validate([
                'class_category_fee_id' => 'required|exists:class_category_fees,id'
            ]);

            // Get date ranges for previous, current, and next month
            $now = now();
            $currentMonthStart = $now->copy()->startOfMonth();
            $currentMonthEnd = $now->copy()->endOfMonth();

            $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

            $nextMonthStart = $now->copy()->addMonth()->startOfMonth();
            $nextMonthEnd = $now->copy()->addMonth()->endOfMonth();

            $schedules = ClassSchedule::with([
                'hall:id,hall_name,code',
                'classCategoryFee.category:id,category_name',
                'studentClass:id,class_name'
            ])
                ->where('class_category_fee_id', $request->class_category_fee_id)
                ->where('is_active', true)
                ->where(function ($query) use (
                    $previousMonthStart,
                    $previousMonthEnd,
                    $currentMonthStart,
                    $currentMonthEnd,
                    $nextMonthStart,
                    $nextMonthEnd
                ) {
                    $query->whereBetween('class_date', [$previousMonthStart, $previousMonthEnd])
                        ->orWhereBetween('class_date', [$currentMonthStart, $currentMonthEnd])
                        ->orWhereBetween('class_date', [$nextMonthStart, $nextMonthEnd]);
                })
                ->orderBy('class_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            return $this->successResponse(
                'Schedules fetched successfully.',
                $schedules
            );
        } catch (Throwable $e) {
            Log::error('Schedule fetch error: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to fetch schedules. Please try again later.',
                500
            );
        }
    }

    public function storeAddNewDay(Request $request)
    {
        try {

            $validated = $request->validate([
                'student_class_id'      => 'required|exists:student_classes,id',
                'class_category_fee_id' => 'required|exists:class_category_fees,id',
                'class_hall_id'         => 'required|exists:class_halls,id',
                'class_date'            => 'required|date',
                'start_time'            => 'required|date_format:H:i',
                'end_time'              => 'required|date_format:H:i|after:start_time',
                'day_of_week'           => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'note'                  => 'nullable|string',
            ]);

            $pattern = $this->scheduleService
                ->getActivePattern(
                    $validated['student_class_id']
                );

            if (!$pattern) {
                return $this->errorResponse(
                    'No active schedule pattern found.'
                );
            }

            if (
                !$this->scheduleService
                    ->validatePatternDateRange(
                        $pattern,
                        $validated['class_date']
                    )
            ) {
                return $this->errorResponse(
                    "Class date should be between {$pattern->start_date->format('Y-m-d')} and {$pattern->end_date->format('Y-m-d')}"
                );
            }

            if (
                $this->scheduleService
                ->hasDuplicateSchedule(
                    $validated['student_class_id'],
                    $validated['class_date']
                )
            ) {
                return $this->errorResponse(
                    'A schedule already exists for the selected date.'
                );
            }

            DB::beginTransaction();

            $schedule = ClassSchedule::create([
                'class_schedule_pattern_id' => $pattern->id,
                'student_class_id'          => $validated['student_class_id'],
                'class_category_fee_id'     => $validated['class_category_fee_id'],
                'class_hall_id'             => $validated['class_hall_id'],
                'class_date'                => $validated['class_date'],
                'start_time'                => $validated['start_time'],
                'end_time'                  => $validated['end_time'],
                'day_of_week'               => $validated['day_of_week'],
                'status'                    => 'scheduled',
                'is_active'                 => true,
                'note'                      => $validated['note'] ?? null,
            ]);

            DB::commit();

            // try {

            //     $this->scheduleService->sendClassScheduleSms(
            //         $validated,
            //         'created'
            //     );
            // } catch (Throwable $e) {

            //     Log::error('Class SMS dispatch failed', [
            //         'error' => $e->getMessage(),
            //         'class_category_fee_id' => $validated['class_category_fee_id'],
            //     ]);
            // }


            return $this->successResponse(
                'New class day added successfully.',
                $schedule,
                201
            );
        } catch (Throwable $e) {

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function updateClassSchedule(Request $request)
    {
        try {

            $validated = $request->validate([
                'schedule_id'           => 'required|exists:class_schedules,id',
                'class_category_fee_id' => 'required|exists:class_category_fees,id',
                'class_hall_id'         => 'required|exists:class_halls,id',
                'class_date'            => 'required|date',
                'start_time'            => 'required|date_format:H:i',
                'end_time'              => 'required|date_format:H:i|after:start_time',
                'day_of_week'           => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'note'                  => 'nullable|string',
            ]);

            $schedule = ClassSchedule::findOrFail(
                $validated['schedule_id']
            );

            if (
                $this->scheduleService
                ->isLockedStatus($schedule->status)
            ) {
                return $this->errorResponse(
                    "This class is {$schedule->status} and cannot be updated."
                );
            }

            if (
                $this->scheduleService
                ->isPastSchedule($schedule)
            ) {
                return $this->errorResponse(
                    'Past class schedules cannot be updated.'
                );
            }

            $pattern = $this->scheduleService
                ->getActivePattern(
                    $schedule->student_class_id
                );

            if (!$pattern) {
                return $this->errorResponse(
                    'No active schedule pattern found.'
                );
            }

            if (
                !$this->scheduleService
                    ->validatePatternDateRange(
                        $pattern,
                        $validated['class_date']
                    )
            ) {
                return $this->errorResponse(
                    "Class date should be between {$pattern->start_date->format('Y-m-d')} and {$pattern->end_date->format('Y-m-d')}"
                );
            }

            if (
                $this->scheduleService
                ->hasDuplicateSchedule(
                    $schedule->student_class_id,
                    $validated['class_date'],
                    $schedule->id
                )
            ) {
                return $this->errorResponse(
                    'Another schedule already exists for this date.'
                );
            }

            DB::beginTransaction();

            $schedule->update([
                'class_category_fee_id' => $validated['class_category_fee_id'],
                'class_hall_id'         => $validated['class_hall_id'],
                'class_date'            => $validated['class_date'],
                'start_time'            => $validated['start_time'],
                'end_time'              => $validated['end_time'],
                'day_of_week'           => $validated['day_of_week'],
                'note'                  => $validated['note'] ?? null,
            ]);

            DB::commit();

            // try {

            //     $this->scheduleService->sendClassScheduleSms(
            //         $validated,
            //         'updated'
            //     );
            // } catch (Throwable $e) {

            //     Log::error('Class SMS dispatch failed', [
            //         'error' => $e->getMessage(),
            //         'class_category_fee_id' => $validated['class_category_fee_id'],
            //     ]);
            // }

            return $this->successResponse(
                'Class schedule updated successfully.',
                $schedule->fresh()
            );
        } catch (Throwable $e) {

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    public function classCancel(Request $request)
    {
        try {

            $validated = $request->validate([
                'schedule_id'   => 'required|exists:class_schedules,id',
                'cancel_reason' => 'nullable|string|max:500',
            ]);

            $schedule = ClassSchedule::findOrFail(
                $validated['schedule_id']
            );

            if (
                $this->scheduleService
                ->isLockedStatus($schedule->status)
            ) {
                return $this->errorResponse(
                    "This class is {$schedule->status} and cannot be cancelled."
                );
            }

            if (
                $this->scheduleService
                ->isPastSchedule($schedule)
            ) {
                return $this->errorResponse(
                    'Past class schedules cannot be cancelled.'
                );
            }

            DB::beginTransaction();

            $schedule->cancel(
                auth()->id(),
                $validated['cancel_reason'] ?? null
            );

            DB::commit();

            // try {

            //     $this->scheduleService->sendClassScheduleSms(
            //         [
            //             'class_category_fee_id' => $schedule->class_category_fee_id,
            //             'class_hall_id' => $schedule->class_hall_id,
            //             'class_date' => $schedule->class_date,
            //             'start_time' => $schedule->start_time,
            //             'end_time' => $schedule->end_time,
            //             'cancel_reason' => $validated['cancel_reason'] ?? null,
            //         ],
            //         'cancelled'
            //     );
            // } catch (Throwable $e) {

            //     Log::error('Class cancel SMS dispatch failed', [
            //         'schedule_id' => $schedule->id,
            //         'error' => $e->getMessage(),
            //     ]);
            // }

            return $this->successResponse(
                'Class cancelled successfully.',
                $schedule->fresh()
            );
        } catch (Throwable $e) {

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return $this->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    private function successResponse(
        string $message,
        $data = null,
        int $code = 200
    ) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    private function errorResponse(
        string $message,
        int $code = 422
    ) {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}
