<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExamResultsExport;
use App\Exports\ExamsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExamRequest;
use App\Http\Requests\Admin\CancelExamRequest;
use App\Http\Requests\Admin\UpdateExamRequest;
use App\Services\ExamService;
use App\Models\Exam;
use App\Models\StudentClass;
use App\Models\StudentResult;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExamController extends Controller
{
    protected ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    /**
     * Display a listing of exams.
     */
    public function index(Request $request): View
    {
        $filters = $request->only([
            'search',
            'status',
            'student_class_id',
            'exam_date_from',
            'exam_date_to'
        ]);

        $exams = $this->examService
            ->getAllExams($filters, 15);

        $stats = $this->examService
            ->getExamStats();

        $classes = $this->examService
            ->getClasses();

        $upcomingExams = $this->examService
            ->getUpcomingExams(5);

        return view(
            'admin.exams.index',
            compact(
                'exams',
                'stats',
                'classes',
                'upcomingExams',
                'filters'
            )
        );
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create(): View
    {
        $classes = StudentClass::with([
            'grade:id,grade_name',
            'teacher:id,full_name',
            'categoryFees.category:id,category_name'
        ])
            ->where('is_active', true)
            ->get();

        $halls = $this->examService->getHalls();

        $categories = collect();

        return view(
            'admin.exams.create',
            compact(
                'classes',
                'categories',
                'halls'
            )
        );
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(int $id): View
    {
        $exam = $this->examService
            ->getExamById($id);

        if (!$exam) {
            abort(404, 'Exam not found');
        }

        $classes = StudentClass::with([
            'grade:id,grade_name',
            'teacher:id,full_name',
            'categoryFees.category:id,category_name'
        ])
            ->where('is_active', true)
            ->get();

        $halls = $this->examService
            ->getHalls();

        return view(
            'admin.exams.edit',
            compact(
                'exam',
                'classes',
                'halls'
            )
        );
    }


    /**
     * Get categories by class (AJAX endpoint)
     */
    public function getCategoriesByClass(Request $request): JsonResponse
    {
        try {

            $request->validate([
                'class_id' => [
                    'required',
                    'exists:student_classes,id'
                ]
            ]);

            $categories = $this->examService
                ->getCategoriesByClass(
                    (int) $request->class_id
                );

            return response()->json([
                'success' => true,
                'categories' => $categories
                    ->map(function ($category) {

                        return [
                            'id' => $category->id,
                            'name' => $category->category_name,
                            'code' => $category->code,
                        ];
                    })
                    ->values()
            ]);
        } catch (\Throwable $e) {

            Log::error(
                'Error loading categories',
                [
                    'message' => $e->getMessage(),
                    'class_id' => $request->class_id
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories'
            ], 500);
        }
    }

    /**
     * Store a newly created exam.
     */
    public function store(StoreExamRequest $request): RedirectResponse
    {
        try {

            DB::beginTransaction();

            $data = $request->validated();

            $isAvailable = $this->examService
                ->checkHallAvailability(
                    $data['class_hall_id'],
                    $data['exam_date'],
                    $data['start_time'],
                    $data['end_time']
                );

            if (!$isAvailable) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'The selected hall is already booked for the specified time slot.'
                    );
            }

            $exam = $this->examService
                ->createExam($data);

            DB::commit();

            return redirect()
                ->route('admin.exams.index')
                ->with(
                    'success',
                    "Exam '{$exam->title}' has been created successfully."
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Error creating exam',
                [
                    'message' => $e->getMessage(),
                    'data' => $request->except(['_token'])
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to create exam. Please try again.'
                );
        }
    }

    /**
     * Display the specified exam.
     */
    public function show(int $id): View
    {
        $exam = $this->examService
            ->getExamById($id);

        if (!$exam) {
            abort(404, 'Exam not found');
        }

        return view(
            'admin.exams.show',
            compact('exam')
        );
    }

    /**
     * Update the specified exam.
     */
    public function update(
        UpdateExamRequest $request,
        int $id
    ): RedirectResponse {

        try {

            DB::beginTransaction();

            $exam = $this->examService
                ->getExamById($id);

            if (!$exam) {
                abort(404, 'Exam not found');
            }

            /*
    |--------------------------------------------------------------------------
    | Completed Exam Protection
    |--------------------------------------------------------------------------
    */

            if ($exam->status === 'completed') {

                return redirect()
                    ->route('admin.exams.index')
                    ->with(
                        'error',
                        'Completed exams cannot be modified.'
                    );
            }

            $data = $request->validated();

            $hallId = $data['class_hall_id']
                ?? $exam->class_hall_id;

            $date = $data['exam_date']
                ?? $exam->exam_date->format('Y-m-d');

            $startTime = $data['start_time']
                ?? $exam->start_time;

            $endTime = $data['end_time']
                ?? $exam->end_time;

            $isAvailable = $this->examService
                ->checkHallAvailability(
                    $hallId,
                    $date,
                    $startTime,
                    $endTime,
                    $exam->id
                );

            if (!$isAvailable) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'The selected hall is already booked for the specified time slot.'
                    );
            }

            $this->examService
                ->updateExam(
                    $exam,
                    $data
                );

            DB::commit();

            return redirect()
                ->route('admin.exams.index')
                ->with(
                    'success',
                    "Exam '{$exam->title}' has been updated successfully."
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Error updating exam',
                [
                    'exam_id' => $id,
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Failed to update exam. Please try again.'
                );
        }
    }


    /**
     * Cancel the specified exam.
     */
    public function cancel(
        CancelExamRequest $request,
        int $id
    ): RedirectResponse {

        try {

            DB::beginTransaction();

            $exam = $this->examService
                ->getExamById($id);

            if (!$exam) {
                abort(404, 'Exam not found');
            }

            if ($exam->status === 'cancelled') {
                return back()->with(
                    'error',
                    'This exam is already cancelled.'
                );
            }

            $this->examService->cancelExam(
                $exam,
                auth()->id(),
                $request->cancel_reason
            );

            DB::commit();

            return redirect()
                ->route('admin.exams.index')
                ->with(
                    'success',
                    "Exam '{$exam->title}' has been cancelled successfully."
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Error cancelling exam',
                [
                    'exam_id' => $id,
                    'message' => $e->getMessage()
                ]
            );

            return back()->with(
                'error',
                'Failed to cancel exam. Please try again.'
            );
        }
    }

    /**
     * Update exam status (for quick status changes).
     */
    public function updateStatus(
        Request $request,
        int $id
    ): RedirectResponse {

        try {

            $request->validate([
                'status' => [
                    'required',
                    'in:scheduled,ongoing,completed,cancelled'
                ],
            ]);

            $exam = $this->examService
                ->getExamById($id);

            if (!$exam) {
                abort(404, 'Exam not found');
            }

            if (
                $request->status === 'cancelled' &&
                $exam->status !== 'cancelled'
            ) {

                $exam->update([
                    'status' => 'cancelled',
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                ]);
            } else {

                $this->examService
                    ->updateExamStatus(
                        $exam,
                        $request->status
                    );
            }

            return redirect()
                ->route('admin.exams.index')
                ->with(
                    'success',
                    "Exam status updated to '{$request->status}'."
                );
        } catch (\Throwable $e) {

            Log::error(
                'Error updating exam status',
                [
                    'exam_id' => $id,
                    'message' => $e->getMessage()
                ]
            );

            return back()->with(
                'error',
                'Failed to update exam status.'
            );
        }
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {

            DB::beginTransaction();

            $exam = $this->examService
                ->getExamById($id);

            if (!$exam) {
                abort(404, 'Exam not found');
            }

            if ($exam->results()->exists()) {

                return back()->with(
                    'error',
                    'Cannot delete an exam that already has results.'
                );
            }

            $examTitle = $exam->title;

            $this->examService
                ->deleteExam($exam);

            DB::commit();

            return redirect()
                ->route('admin.exams.index')
                ->with(
                    'success',
                    "Exam '{$examTitle}' has been deleted successfully."
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Error deleting exam',
                [
                    'exam_id' => $id,
                    'message' => $e->getMessage()
                ]
            );

            return back()->with(
                'error',
                'Failed to delete exam.'
            );
        }
    }
    /**
     * Display upcoming exams.
     */
    public function upcoming(Request $request): View
    {
        $filters = [
            'search' => $request->search,
            'student_class_id' => $request->student_class_id,
            'status' => 'scheduled',
            'exam_date_from' => today()->toDateString(),
        ];

        $exams = $this->examService
            ->getAllExams($filters, 15);

        $stats = $this->examService
            ->getExamStats();

        $classes = $this->examService
            ->getClasses();

        $upcomingExams = $this->examService
            ->getUpcomingExams(10);

        return view(
            'admin.exams.upcoming',
            [
                'exams' => $exams,
                'stats' => $stats,
                'classes' => $classes,
                'upcomingExams' => $upcomingExams,
                'filters' => $filters,
            ]
        );
    }

    /**
     * Get exam counts for sidebar badges.
     */
    public function counts(): JsonResponse
    {
        try {

            $stats = $this->examService
                ->getExamStats();

            return response()->json([
                'success' => true,
                'counts' => [
                    'scheduled' => $stats['scheduled'],
                    'ongoing' => $stats['ongoing'],
                    'completed' => $stats['completed'],
                    'cancelled' => $stats['cancelled'],
                    'upcoming' => $stats['upcoming'],
                    'total' => $stats['total'],
                ]
            ]);
        } catch (\Throwable $e) {

            Log::error(
                'Error getting exam counts',
                [
                    'message' => $e->getMessage()
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Error getting exam counts'
            ], 500);
        }
    }

    /**
     * Check hall availability (AJAX).
     */
    public function checkHallAvailability(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'hall_id' => ['required', 'exists:class_halls,id'],
                'date' => ['required', 'date'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'exclude_exam_id' => ['nullable', 'exists:exams,id'],
            ]);

            $isAvailable = $this->examService->checkHallAvailability(
                $request->hall_id,
                $request->date,
                $request->start_time,
                $request->end_time,
                $request->exclude_exam_id
            );

            return response()->json([
                'available' => $isAvailable,
                'message' => $isAvailable ? 'Hall is available' : 'Hall is already booked for this time slot'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking hall availability: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error checking hall availability'
            ], 500);
        }
    }

    public function markEntry(int $id): View
    {
        $exam = $this->examService
            ->getExamById($id);

        if (!$exam) {
            abort(404, 'Exam not found');
        }

        $students = $this->examService
            ->getExamStudents($id);

        return view(
            'admin.exams.mark-entry',
            compact(
                'exam',
                'students'
            )
        );
    }
    public function saveMarks(
        Request $request,
        int $id
    ): RedirectResponse {

        try {

            DB::beginTransaction();

            $exam = $this->examService->getExamById($id);

            if (!$exam) {
                abort(404, 'Exam not found');
            }

            $request->validate([
                'marks' => 'required|array',
                'max_marks' => 'nullable|numeric|min:1',
            ]);

            $maxMarks = (float) ($request->max_marks ?? 100);

            foreach ($request->marks as $studentId => $markData) {

                // Check if student is absent
                $isAbsent = isset($markData['is_absent']) && $markData['is_absent'] == 1;

                // Get marks (null if absent)
                $marks = $isAbsent ? null : (float) ($markData['marks'] ?? null);

                // Calculate percentage
                $percentage = null;
                $grade = null;
                $status = 'absent';

                if (!$isAbsent && !is_null($marks) && $marks !== '') {
                    $percentage = $maxMarks > 0
                        ? round(($marks / $maxMarks) * 100, 2)
                        : 0;

                    $grade = $this->examService->calculateGrade($percentage);
                    $status = $percentage >= 35 ? 'passed' : 'failed';
                }

                StudentResult::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'exam_id' => $exam->id
                    ],
                    [
                        'user_id' => auth()->id(),
                        'marks' => $marks,
                        'max_marks' => $maxMarks,
                        'percentage' => $percentage,
                        'grade' => $grade,
                        'status' => $status,
                        'is_absent' => $isAbsent,
                        'is_updated' => true,
                    ]
                );
            }

            DB::commit();

            // Count statistics
            $total = count($request->marks);
            $absentCount = collect($request->marks)->filter(function ($data) {
                return isset($data['is_absent']) && $data['is_absent'] == 1;
            })->count();
            $filledCount = $total - $absentCount;

            $message = "Results saved successfully!";
            $message .= " Total: {$total}, Filled: {$filledCount}, Absent: {$absentCount}";

            return redirect()
                ->route('admin.exams.results', $exam->id)
                ->with('success', $message);
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Error saving marks', [
                'exam_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to save marks. Please try again.');
        }
    }
    public function results(
        int $id
    ): View {

        $exam = Exam::with([
            'studentClass',
            'category',
            'results.student',
            'results.enteredBy'
        ])->findOrFail($id);

        $results = $exam->results()
            ->orderByDesc('marks')
            ->get();

        return view(
            'admin.exams.results',
            compact(
                'exam',
                'results'
            )
        );
    }
    public function recalculateRanks(
        int $id
    ): RedirectResponse {

        try {

            DB::beginTransaction();

            $results = StudentResult::where(
                'exam_id',
                $id
            )
                ->orderByDesc('marks')
                ->get();

            $rank = 1;

            foreach ($results as $result) {

                $result->update([
                    'rank' => $rank
                ]);

                $rank++;
            }

            DB::commit();

            return back()->with(
                'success',
                'Ranks recalculated successfully.'
            );
        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error(
                'Error recalculating ranks',
                [
                    'exam_id' => $id,
                    'message' => $e->getMessage()
                ]
            );

            return back()->with(
                'error',
                'Failed to recalculate ranks.'
            );
        }
    }

    public function searchClasses(Request $request)
    {
        $search = $request->input('q', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = StudentClass::query()
            ->with(['grade', 'teacher'])
            ->select('id', 'class_name', 'grade_id', 'teacher_id');

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'LIKE', "%{$search}%")
                    ->orWhereHas('grade', function ($g) use ($search) {
                        $g->where('grade_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $total = $query->count();
        $classes = $query->orderBy('class_name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Format for Select2
        $results = $classes->map(function ($class) {
            $label = $class->class_name;
            if ($class->grade) {
                $label .= " ({$class->grade->grade_name})";
            }
            if ($class->teacher) {
                $label .= " - {$class->teacher->full_name}";
            }
            return [
                'id' => $class->id,
                'text' => $label
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    public function exportExcel()
    {
        return Excel::download(
            new ExamsExport(),
            'exam-report.xlsx'
        );
    }

    public function exportPdf()
    {
        $exams = Exam::with([
            'studentClass',
            'category',
            'hall',
            'results'
        ])->get();

        $pdf = Pdf::loadView(
            'admin.pdf.exam.index',
            compact('exams')
        );

        return $pdf->download('exam-report.pdf');
    }

    public function exportResultsExcel(Exam $exam)
    {
        return Excel::download(
            new ExamResultsExport($exam),
            'exam-results-' . $exam->id . '.xlsx'
        );
    }

    public function exportResultsPdf(Exam $exam)
    {
        $exam->load([
            'studentClass',
            'studentClass.grade',
            'category',
            'results.student',
            'results.enteredBy'
        ]);

        $results = $exam->results()
            ->orderByDesc('marks')
            ->get();

        $pdf = Pdf::loadView(
            'admin.pdf.exam.results',
            compact(
                'exam',
                'results'
            )
        );

        return $pdf->download(
            'exam-results-' . $exam->id . '.pdf'
        );
    }
}
