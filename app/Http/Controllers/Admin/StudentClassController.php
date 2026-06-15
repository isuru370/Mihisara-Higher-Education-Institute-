<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentClass;
use App\Models\ClassPaymentConfig;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Exports\StudentClassesExport;
use App\Models\ClassCategoryFee;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentClassController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentClass::with([
            'teacher',
            'subject',
            'grade',
            'paymentConfig.organizer',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'like', "%{$search}%")
                    ->orWhere('medium', 'like', "%{$search}%")
                    ->orWhereHas('teacher', function ($t) use ($search) {
                        $t->where('full_name', 'like', "%{$search}%")
                            ->orWhere('custom_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('subject', function ($s) use ($search) {
                        $s->where('subject_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('grade', function ($g) use ($search) {
                        $g->where('grade_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('grade_id')) {
            $query->where('grade_id', $request->grade_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_type')) {
            $query->where('class_type', $request->class_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        if ($request->filled('is_ongoing')) {
            $query->where('is_ongoing', $request->is_ongoing === 'true');
        }

        $classes = $query->latest()->paginate(10)->appends($request->query());

        return view('admin.student_classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.student_classes.create', [
            'teachers' => Teacher::where('is_active', true)->orderBy('full_name')->get(),
            'grades' => Grade::where('is_active', true)->orderBy('grade_name')->get(),
            'subjects' => Subject::where('is_active', true)->orderBy('subject_name')->get(),
            'organizers' => Organizer::where('is_active', true)->orderBy('name')->get(),
            'studentClass' => null,
            'paymentConfig' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateClassRequest($request);

        DB::transaction(function () use ($request, $validated) {
            $gradeId = $this->resolveGradeId($request);
            $subjectId = $this->resolveSubjectId($request);

            $studentClass = StudentClass::create([
                'class_name' => $validated['class_name'],
                'class_type' => $validated['class_type'],
                'medium' => $validated['medium'],
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $subjectId,
                'grade_id' => $gradeId,
                'is_active' => $request->boolean('is_active'),
                'is_ongoing' => $request->boolean('is_ongoing'),
            ]);

            ClassPaymentConfig::create([
                'student_class_id' => $studentClass->id,
                'teacher_id' => $validated['teacher_id'],
                'organizer_id' => $request->organizer_id,
                'teacher_percentage' => $validated['teacher_percentage'],
                'organizer_percentage' => $validated['organizer_percentage'] ?? 0,
                'institution_percentage' => $validated['institution_percentage'],
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('admin.student-classes.index')
            ->with('success', 'Class created successfully.');
    }

    public function show(StudentClass $studentClass)
    {
        $studentClass->load([
            'teacher',
            'subject',
            'grade',
            'paymentConfig.organizer',
            'paymentConfig.createdBy',
            'paymentConfig.updatedBy',
        ]);

        $categoryFees = ClassCategoryFee::with('category')
            ->where('student_class_id', $studentClass->id)
            ->whereNull('deleted_at')
            ->orderByDesc('is_active')
            ->latest()
            ->get();

        return view(
            'admin.student_classes.show',
            compact(
                'studentClass',
                'categoryFees'
            )
        );
    }

    public function edit(StudentClass $studentClass)
    {
        $studentClass->load(['paymentConfig']);

        return view('admin.student_classes.edit', [
            'studentClass' => $studentClass,
            'paymentConfig' => $studentClass->paymentConfig,
            'teachers' => Teacher::where('is_active', true)->orderBy('full_name')->get(),
            'grades' => Grade::where('is_active', true)->orderBy('grade_name')->get(),
            'subjects' => Subject::where('is_active', true)->orderBy('subject_name')->get(),
            'organizers' => Organizer::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StudentClass $studentClass)
    {
        $validated = $this->validateClassRequest($request);

        DB::transaction(function () use ($request, $validated, $studentClass) {
            $gradeId = $this->resolveGradeId($request);
            $subjectId = $this->resolveSubjectId($request);

            $studentClass->update([
                'class_name' => $validated['class_name'],
                'class_type' => $validated['class_type'],
                'medium' => $validated['medium'],
                'teacher_id' => $validated['teacher_id'],
                'subject_id' => $subjectId,
                'grade_id' => $gradeId,
                'is_active' => $request->boolean('is_active'),
                'is_ongoing' => $request->boolean('is_ongoing'),
            ]);

            ClassPaymentConfig::updateOrCreate(
                [
                    'student_class_id' => $studentClass->id,
                ],
                [
                    'teacher_id' => $validated['teacher_id'],
                    'organizer_id' => $request->organizer_id,
                    'teacher_percentage' => $validated['teacher_percentage'],
                    'organizer_percentage' => $validated['organizer_percentage'] ?? 0,
                    'institution_percentage' => $validated['institution_percentage'],
                    'effective_from' => $request->effective_from,
                    'effective_to' => $request->effective_to,
                    'is_active' => $request->boolean('payment_is_active', true),
                    'updated_by' => Auth::id(),
                ]
            );
        });

        return redirect()
            ->route('admin.student-classes.index')
            ->with('success', 'Class updated successfully.');
    }

    public function destroy(StudentClass $studentClass)
    {
        // ❌ Prevent delete if class is ongoing
        if ($studentClass->is_ongoing) {

            return redirect()
                ->route('admin.student-classes.index')
                ->with('error', 'Ongoing classes cannot be deleted.');
        }

        // ❌ Prevent delete if class is active
        if ($studentClass->is_active) {

            return redirect()
                ->route('admin.student-classes.index')
                ->with('error', 'Active classes cannot be deleted.');
        }

        DB::transaction(function () use ($studentClass) {

            // Soft delete payment config
            $studentClass->paymentConfig()?->delete();

            // Delete class
            $studentClass->delete();
        });

        return redirect()
            ->route('admin.student-classes.index')
            ->with('success', 'Class deleted successfully.');
    }



    public function toggleActive(StudentClass $studentClass)
    {
        // Prevent inactive if class is ongoing
        if ($studentClass->is_active && $studentClass->is_ongoing) {

            return back()->with(
                'error',
                'You cannot deactivate an ongoing class.'
            );
        }

        DB::transaction(function () use ($studentClass) {

            $newStatus = ! $studentClass->is_active;

            // Update class
            $studentClass->update([
                'is_active' => $newStatus,
            ]);

            // Update payment config
            if ($studentClass->paymentConfig) {

                $studentClass->paymentConfig->update([
                    'is_active' => $newStatus,
                ]);
            }
        });

        return back()->with(
            'success',
            'Class status updated.'
        );
    }

    public function toggleOngoing(StudentClass $studentClass)
    {
        $studentClass->update([
            'is_ongoing' => !$studentClass->is_ongoing,
        ]);

        return back()->with('success', 'Ongoing status updated.');
    }

    private function validateClassRequest(Request $request): array
    {
        $validated = $request->validate([
            'class_name' => ['required', 'string', 'max:150'],
            'class_type' => ['required', Rule::in(['online', 'offline', 'hybrid'])],
            'medium' => ['required', 'string', 'max:50'],

            'teacher_id' => ['required', 'exists:teachers,id'],

            'grade_id' => ['nullable', 'exists:grades,id'],
            'new_grade' => ['nullable', 'string', 'max:100'],

            'subject_id' => ['nullable', 'exists:subjects,id'],
            'new_subject' => ['nullable', 'string', 'max:100'],

            'organizer_id' => ['nullable', 'exists:organizers,id'],

            'teacher_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'organizer_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'institution_percentage' => ['required', 'numeric', 'min:0', 'max:100'],

            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        if (!$request->grade_id && !$request->filled('new_grade')) {
            throw ValidationException::withMessages([
                'grade_id' => 'Please select a grade or add a new grade.',
            ]);
        }

        if (!$request->subject_id && !$request->filled('new_subject')) {
            throw ValidationException::withMessages([
                'subject_id' => 'Please select a subject or add a new subject.',
            ]);
        }

        if (
            (float) ($request->organizer_percentage ?? 0) > 0 &&
            !$request->filled('organizer_id')
        ) {
            throw ValidationException::withMessages([
                'organizer_id' => 'Organizer is required when organizer percentage is greater than 0.',
            ]);
        }

        $total =
            (float) $request->teacher_percentage +
            (float) ($request->organizer_percentage ?? 0) +
            (float) $request->institution_percentage;

        if (round($total, 2) !== 100.00) {
            throw ValidationException::withMessages([
                'percentages' => 'Total must be 100%',
            ]);
        }

        return $validated;
    }

    public function exportExcel()
    {
        return Excel::download(new StudentClassesExport, 'student-classes.xlsx');
    }

    public function exportPdf()
    {
        $classes = StudentClass::with([
            'teacher',
            'subject',
            'grade',
            'paymentConfig.organizer',
        ])->latest()->get();

        $pdf = Pdf::loadView('admin.student_classes.pdf', compact('classes'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('student-classes.pdf');
    }




    private function resolveGradeId(Request $request): int
    {
        if ($request->filled('new_grade')) {
            $grade = Grade::firstOrCreate(
                ['grade_name' => trim($request->new_grade)],
                ['is_active' => true]
            );

            return $grade->id;
        }

        return (int) $request->grade_id;
    }

    private function resolveSubjectId(Request $request): int
    {
        if ($request->filled('new_subject')) {
            $subject = Subject::firstOrCreate(
                ['subject_name' => trim($request->new_subject)],
                ['is_active' => true]
            );

            return $subject->id;
        }

        return (int) $request->subject_id;
    }

    // StudentClassController
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        return StudentClass::query()
            ->with(['grade', 'subject', 'teacher'])
            ->where('is_active', true)
            ->where('is_ongoing', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('class_name', 'like', "%{$q}%")
                        ->orWhere('class_type', 'like', "%{$q}%")
                        ->orWhere('medium', 'like', "%{$q}%")
                        ->orWhereHas('grade', fn($x) => $x->where('grade_name', 'like', "%{$q}%"))
                        ->orWhereHas('subject', fn($x) => $x->where('subject_name', 'like', "%{$q}%"))
                        ->orWhereHas('teacher', fn($x) => $x->where('initials', 'like', "%{$q}%"));
                });
            })
            ->orderBy('class_name')
            ->limit(20)
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'text' => $class->class_name
                        . ' | Grade: ' . ($class->grade?->grade_name ?? '-')
                        . ' | Subject: ' . ($class->subject?->subject_name ?? '-')
                        . ' | Teacher: ' . ($class->teacher?->initials ?? '-'),
                ];
            });
    }
}
