<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassCategoryFee;
use App\Models\ClassHall;
use App\Models\ClassSchedule;
use App\Models\ClassSchedulePattern;
use App\Models\StudentClass;
use App\Services\ClassScheduleService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class ClassScheduleController extends Controller
{
    protected ClassScheduleService $classScheduleService;

    public function __construct(
        ClassScheduleService $scheduleService
    ) {
        $this->classScheduleService = $scheduleService;
    }
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 1) {
            $perPage = 10;
        }

        $search = trim($request->input('search', ''));

        $classes = StudentClass::query()
            ->with([
                'grade',
                'subject',
                'teacher',
                'categoryFees' => function ($query) {
                    $query->with('category')
                        ->where('is_active', true);
                },
            ])
            ->where('is_active', true)
            ->where('is_ongoing', true)
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
                            $teacher->where('initials', 'like', "%{$search}%");
                        })
                        ->orWhereHas('categoryFees.category', function ($category) use ($search) {
                            $category->where('category_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('class_name')
            ->paginate($perPage)
            ->appends($request->query());

        return view('admin.class_schedules.index', compact('classes'));
    }

    public function create(Request $request)
    {
        $selectedClassId = $request->input('student_class_id');
        $selectedCategoryFeeId = $request->input('class_category_fee_id');
        $scheduleType = $request->input('type', 'recurring');

        if (!$selectedClassId || !$selectedCategoryFeeId) {
            return redirect()
                ->route('admin.class-schedules.index')
                ->with('error', 'Please select a class and category fee first.');
        }

        $selectedClass = StudentClass::with([
            'grade',
            'subject',
            'teacher',
        ])->findOrFail($selectedClassId);

        $categoryFee = ClassCategoryFee::with('category')
            ->findOrFail($selectedCategoryFeeId);

        $selectedCategory = $categoryFee->category;

        $halls = ClassHall::where('is_active', true)
            ->orderBy('hall_name')
            ->get();

        return view('admin.class_schedules.create', compact(
            'selectedClass',
            'selectedCategory',
            'categoryFee',
            'halls',
            'selectedClassId',
            'selectedCategoryFeeId',
            'scheduleType'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_type' => ['required', Rule::in(['single', 'recurring'])],

            'student_class_id' => ['required', 'exists:student_classes,id'],
            'class_category_fee_id' => ['required', 'exists:class_category_fees,id'],
            'class_hall_id' => ['nullable', 'exists:class_halls,id'],

            'class_date' => ['required_if:schedule_type,single', 'nullable', 'date'],

            'start_date' => ['required_if:schedule_type,recurring', 'nullable', 'date'],
            'end_date' => ['required_if:schedule_type,recurring', 'nullable', 'date', 'after_or_equal:start_date'],

            'class_day' => [
                'required_if:schedule_type,recurring',
                'nullable',
                Rule::in([
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday',
                    'sunday',
                ]),
            ],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],

            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ]);

        $studentClass = StudentClass::findOrFail($validated['student_class_id']);
        if (!$studentClass->is_active) {
            return back()
                ->withInput()
                ->with('error', 'This class is inactive and cannot be scheduled.');
        }

        $categoryFee = ClassCategoryFee::with('category')
            ->findOrFail($validated['class_category_fee_id']);

        if (isset($categoryFee->is_active) && !$categoryFee->is_active) {
            return back()
                ->withInput()
                ->with('error', 'This category fee is inactive and cannot be scheduled.');
        }

        if (!$categoryFee->category || !$categoryFee->category->is_active || !$categoryFee->category->is_schedulable) {
            return back()
                ->withInput()
                ->with('error', 'This category is not available for scheduling.');
        }

        try {
            DB::transaction(function () use ($validated, $request, $categoryFee) {
                if ($validated['schedule_type'] === 'single') {

                    $date = Carbon::parse($validated['class_date']);
                    $dayOfWeek = strtolower($date->format('l'));

                    $exists = ClassSchedule::where('student_class_id', $validated['student_class_id'])
                        ->where('class_category_fee_id', $validated['class_category_fee_id'])
                        ->whereDate('class_date', $date->toDateString())
                        ->where('start_time', $validated['start_time'])
                        ->where('end_time', $validated['end_time'])
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($exists) {
                        throw new Exception('This single schedule already exists.');
                    }

                    $schedule = ClassSchedule::create([
                        'class_schedule_pattern_id' => null,
                        'student_class_id' => $validated['student_class_id'],
                        'class_category_fee_id' => $validated['class_category_fee_id'],
                        'class_hall_id' => $validated['class_hall_id'] ?? null,
                        'class_date' => $date->toDateString(),
                        'start_time' => $validated['start_time'],
                        'end_time' => $validated['end_time'],
                        'day_of_week' => $dayOfWeek,
                        'status' => 'scheduled',
                        'is_active' => $request->boolean('is_active', true),
                        'note' => $validated['note'] ?? null,
                    ]);

                    return;
                }

                $pattern = ClassSchedulePattern::create([
                    'student_class_id' => $validated['student_class_id'],
                    'class_category_fee_id' => $validated['class_category_fee_id'],
                    'class_hall_id' => $validated['class_hall_id'] ?? null,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'class_day' => $validated['class_day'],
                    'is_active' => $request->boolean('is_active', true),
                    'note' => $validated['note'] ?? null,
                ]);

                $start = Carbon::parse($validated['start_date']);
                $end = Carbon::parse($validated['end_date']);
                $date = $start->copy();

                while ($date->lte($end)) {
                    $dayOfWeek = strtolower($date->format('l'));

                    if ($dayOfWeek === $validated['class_day']) {
                        $exists = ClassSchedule::where('student_class_id', $validated['student_class_id'])
                            ->where('class_category_fee_id', $validated['class_category_fee_id'])
                            ->whereDate('class_date', $date->toDateString())
                            ->where('start_time', $validated['start_time'])
                            ->where('end_time', $validated['end_time'])
                            ->whereNull('deleted_at')
                            ->exists();

                        if (!$exists) {
                            ClassSchedule::create([
                                'class_schedule_pattern_id' => $pattern->id,
                                'student_class_id' => $validated['student_class_id'],
                                'class_category_fee_id' => $validated['class_category_fee_id'],
                                'class_hall_id' => $validated['class_hall_id'] ?? null,
                                'class_date' => $date->toDateString(),
                                'start_time' => $validated['start_time'],
                                'end_time' => $validated['end_time'],
                                'day_of_week' => $dayOfWeek,
                                'status' => 'scheduled',
                                'is_active' => $request->boolean('is_active', true),
                                'note' => $validated['note'] ?? null,
                            ]);
                        }
                    }

                    $date->addDay();
                }
            });

            // if ($validated['schedule_type'] === 'single') {

            //     try {

            //         $this->classScheduleService->sendClassScheduleSms(
            //             [
            //                 'class_category_fee_id' => $validated['class_category_fee_id'],
            //                 'class_hall_id' => $validated['class_hall_id'] ?? null,
            //                 'class_date' => $validated['class_date'],
            //                 'start_time' => $validated['start_time'],
            //                 'end_time' => $validated['end_time'],
            //             ],
            //             'created'
            //         );
            //     } catch (Throwable $e) {

            //         Log::error('Class schedule SMS dispatch failed', [
            //             'student_class_id' => $validated['student_class_id'],
            //             'error' => $e->getMessage(),
            //         ]);
            //     }
            // }

            return redirect()
                ->route('admin.class-schedules.index', [
                    'class_id' => $validated['student_class_id'],
                    'class_category_fee_id' => $validated['class_category_fee_id'],
                ])
                ->with('success', 'Class schedule created successfully.');
        } catch (Exception $e) {
            Log::error('Class schedule creation failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(ClassSchedule $classSchedule)
    {
        $classSchedule->load([
            'pattern',
            'studentClass.grade',
            'studentClass.subject',
            'studentClass.teacher',
            'classCategoryFee.category',
            'hall',
            'cancelledBy',
        ]);

        $categoryFee = $classSchedule->classCategoryFee ?? null;
        $category = optional($categoryFee)->category;

        // Compatibility for blades that still use $classSchedule->category
        $classSchedule->setRelation('category', $category);

        return view('admin.class_schedules.show', compact(
            'classSchedule',
            'categoryFee',
            'category'
        ));
    }

    public function categorySchedules(Request $request)
    {
        $validated = $request->validate([
            'student_class_id' => ['required', 'exists:student_classes,id'],
            'class_category_fee_id' => ['required', 'exists:class_category_fees,id'],
        ]);

        $search = trim((string) $request->get('search'));

        $studentClass = StudentClass::with([
            'grade',
            'subject',
            'teacher',
        ])->findOrFail($validated['student_class_id']);

        $categoryFee = ClassCategoryFee::with('category')
            ->findOrFail($validated['class_category_fee_id']);

        $category = $categoryFee->category;

        $schedulesQuery = ClassSchedule::with([
            'pattern',
            'hall',
            'classCategoryFee.category',
        ])
            ->where('student_class_id', $validated['student_class_id'])
            ->where('class_category_fee_id', $validated['class_category_fee_id']);

        if (!empty($search)) {
            $schedulesQuery->where(function ($query) use ($search) {
                $query->where('status', 'like', '%' . $search . '%')
                    ->orWhere('day_of_week', 'like', '%' . $search . '%')
                    ->orWhereDate('class_date', $search)
                    ->orWhereHas('hall', function ($hallQuery) use ($search) {
                        $hallQuery->where('hall_name', 'like', '%' . $search . '%');
                    });
            });
        }

        $schedules = $schedulesQuery
            ->latest('class_date')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.class_schedules.category_show', compact(
            'studentClass',
            'categoryFee',
            'category',
            'schedules',
            'search'
        ));
    }

    public function edit(ClassSchedule $classSchedule)
    {
        if ($this->isLocked($classSchedule)) {
            return back()->with('error', 'This schedule is locked and cannot be edited.');
        }

        $classSchedule->load([
            'studentClass.grade',
            'studentClass.subject',
            'studentClass.teacher',
            'classCategoryFee.category',
            'hall',
        ]);

        $categoryFee = $classSchedule->classCategoryFee ?? null;
        $category = optional($categoryFee)->category;

        // Compatibility for blades that still use $classSchedule->category
        $classSchedule->setRelation('category', $category);

        $classes = StudentClass::with(['grade', 'subject', 'teacher'])
            ->where('is_active', true)
            ->orderBy('class_name')
            ->get();

        $halls = ClassHall::where('is_active', true)
            ->orderBy('hall_name')
            ->get();

        return view('admin.class_schedules.edit', compact(
            'classSchedule',
            'classes',
            'halls',
            'categoryFee',
            'category'
        ));
    }

    public function update(Request $request, ClassSchedule $classSchedule)
    {
        if ($this->isLocked($classSchedule)) {
            return back()->withInput()->with('error', 'This schedule is locked and cannot be updated.');
        }

        if (!$classSchedule->is_active) {
            return back()->withInput()->with('error', 'Inactive schedule cannot be edited.');
        }

        if ($classSchedule->status === 'cancelled') {
            return back()->withInput()->with('error', 'Cancelled schedule cannot be edited.');
        }

        $validated = $request->validate([
            'student_class_id' => ['required', 'exists:student_classes,id'],
            'class_category_fee_id' => ['required', 'exists:class_category_fees,id'],
            'class_hall_id' => ['nullable', 'exists:class_halls,id'],
            'class_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string'],
        ]);

        $studentClass = StudentClass::findOrFail($validated['student_class_id']);
        if (!$studentClass->is_active) {
            return back()
                ->withInput()
                ->with('error', 'This class is inactive and cannot be scheduled.');
        }

        $categoryFee = ClassCategoryFee::with('category')
            ->findOrFail($validated['class_category_fee_id']);

        if (isset($categoryFee->is_active) && !$categoryFee->is_active) {
            return back()
                ->withInput()
                ->with('error', 'This category fee is inactive and cannot be scheduled.');
        }

        if (!$categoryFee->category || !$categoryFee->category->is_active || !$categoryFee->category->is_schedulable) {
            return back()
                ->withInput()
                ->with('error', 'This category is not available for scheduling.');
        }

        $dayOfWeek = strtolower(Carbon::parse($validated['class_date'])->format('l'));

        $exists = ClassSchedule::where('student_class_id', $validated['student_class_id'])
            ->where('class_category_fee_id', $validated['class_category_fee_id'])
            ->whereDate('class_date', $validated['class_date'])
            ->where('start_time', $validated['start_time'])
            ->where('end_time', $validated['end_time'])
            ->where('id', '!=', $classSchedule->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'This schedule already exists for the selected class, category fee, date and time.');
        }

        $classSchedule->update([
            'student_class_id' => $validated['student_class_id'],
            'class_category_fee_id' => $validated['class_category_fee_id'],
            'class_hall_id' => $validated['class_hall_id'] ?? null,
            'class_date' => $validated['class_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'day_of_week' => $dayOfWeek,
            'is_active' => $request->boolean('is_active'),
            'note' => $validated['note'] ?? null,
        ]);

        $smsData = [
            'class_category_fee_id' => $validated['class_category_fee_id'],
            'class_hall_id' => $validated['class_hall_id'] ?? null,
            'class_date' => $validated['class_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ];

        // try {

        //     $this->classScheduleService->sendClassScheduleSms(
        //         $smsData,
        //         'updated'
        //     );
        // } catch (Throwable $e) {

        //     Log::error('Class schedule update SMS dispatch failed', [
        //         'schedule_id' => $classSchedule->id,
        //         'student_class_id' => $validated['student_class_id'],
        //         'error' => $e->getMessage(),
        //     ]);
        // }

        return redirect()
            ->route('admin.class-schedules.categorySchedules', [
                'student_class_id' => $validated['student_class_id'],
                'class_category_fee_id' => $validated['class_category_fee_id'],
            ])
            ->with('success', 'Class schedule updated successfully.');
    }

    public function destroy(ClassSchedule $classSchedule)
    {
        if ($this->isLocked($classSchedule)) {
            return back()->with('error', 'This schedule is locked and cannot be deleted.');
        }

        try {
            if ($classSchedule->studentAttendances()->exists()) {
                return back()->with('error', 'Cannot delete schedule. Attendance records already exist.');
            }

            $classSchedule->delete();

            return redirect()
                ->route('admin.class-schedules.index')
                ->with('success', 'Class schedule deleted successfully.');
        } catch (Exception $e) {
            Log::error('Class schedule delete failed', [
                'schedule_id' => $classSchedule->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Class schedule delete failed.');
        }
    }

    public function toggleActive(ClassSchedule $classSchedule)
    {
        if ($this->isLocked($classSchedule)) {
            return back()->with('error', 'This schedule is locked and cannot be changed.');
        }

        if ($classSchedule->status === 'cancelled') {
            return back()->with('error', 'Cancelled schedule cannot be activated.');
        }

        $classSchedule->update([
            'is_active' => !$classSchedule->is_active,
        ]);

        return back()->with('success', 'Schedule status updated.');
    }

    public function cancel(Request $request, ClassSchedule $classSchedule)
    {
        if ($this->isLocked($classSchedule)) {
            return back()->with('error', 'This schedule is locked and cannot be cancelled.');
        }

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'min:3'],
        ]);

        if ($classSchedule->status === 'completed') {
            return back()->with('error', 'Completed schedule cannot be cancelled.');
        }

        if ($classSchedule->status === 'cancelled') {
            return back()->with('error', 'This schedule is already cancelled.');
        }

        $classSchedule->cancel(
            Auth::id(),
            $validated['cancel_reason']
        );

        // try {

        //     $this->classScheduleService->sendClassScheduleSms(
        //         [
        //             'class_category_fee_id' => $classSchedule->class_category_fee_id,
        //             'class_hall_id' => $classSchedule->class_hall_id,
        //             'class_date' => $classSchedule->class_date,
        //             'start_time' => $classSchedule->start_time,
        //             'end_time' => $classSchedule->end_time,
        //             'cancel_reason' => $validated['cancel_reason'],
        //         ],
        //         'cancelled'
        //     );
        // } catch (Throwable $e) {

        //     Log::error('Class schedule cancel SMS dispatch failed', [
        //         'schedule_id' => $classSchedule->id,
        //         'error' => $e->getMessage(),
        //     ]);
        // }

        return back()->with(
            'success',
            'Schedule cancelled successfully.'
        );
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

        // Mobile API එකේ structure එකට සමාන data structure එකක් හදනවා
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
        })->sortBy(function ($row) {
            return $row['schedule']['start_time'] ?? '';
        })->values();

        // Web view එකට data යවනවා (same structure as mobile API)
        return view('admin.today-classes.index', [
            'classes' => $data,
            'today' => $today,
        ]);
    }

    public function statusUpdate(ClassSchedule $classSchedule)
    {
        if ($this->isLocked($classSchedule)) {
            return back()->with('error', 'This schedule is locked and cannot be updated.');
        }

        if ($classSchedule->status === 'cancelled') {
            return back()->with('error', 'Cancelled schedule cannot be updated. Please activate it first.');
        }

        $classSchedule->update([
            'status' => 'completed',
        ]);

        return back()->with('success', 'Schedule marked as completed successfully.');
    }

    private function isLocked(ClassSchedule $classSchedule): bool
    {
        if (!$classSchedule->class_date) {
            return false;
        }

        return Carbon::parse($classSchedule->class_date)->lte(today());
    }
}
