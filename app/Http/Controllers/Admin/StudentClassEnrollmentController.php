<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CategoryStudentsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentClassEnrollment\StoreStudentClassEnrollmentRequest;
use App\Http\Requests\StudentClassEnrollment\UpdateStudentClassEnrollmentRequest;
use App\Models\ClassCategory;
use App\Models\ClassCategoryFee;
use App\Models\StudentClass;
use App\Models\StudentClassEnrollment;
use App\Services\StudentClassEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;

class StudentClassEnrollmentController extends Controller
{
    protected StudentClassEnrollmentService $studentClassEnrollmentService;

    public function __construct(StudentClassEnrollmentService $studentClassEnrollmentService)
    {
        $this->studentClassEnrollmentService = $studentClassEnrollmentService;
    }
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        $search = trim($request->input('search', ''));

        $classesQuery = StudentClass::query()
            ->with(['grade', 'subject', 'teacher'])
            ->whereHas('enrollments')
            ->with(['categoryFees.category'])
            ->withCount([
                'enrollments as total_students_count',
                'enrollments as active_students_count' => fn($q) => $q->where('is_active', true),
                'enrollments as inactive_students_count' => fn($q) => $q->where('is_active', false),
            ]);

        if ($search !== '') {
            $classesQuery->where(function ($query) use ($search) {
                $query->where('class_name', 'like', "%{$search}%")
                    ->orWhere('class_type', 'like', "%{$search}%")
                    ->orWhere('medium', 'like', "%{$search}%")
                    ->orWhereHas('grade', fn($q) => $q->where('grade_name', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn($q) => $q->where('subject_name', 'like', "%{$search}%"))
                    ->orWhereHas('teacher', fn($q) => $q->where('initials', 'like', "%{$search}%"));
            });
        }

        $classes = $classesQuery->orderBy('class_name')->paginate($perPage);
        $classes->appends($request->all());

        $classIds = collect($classes->items())->pluck('id');

        $categoryStats = StudentClassEnrollment::query()
            ->join('class_category_fees', 'student_class_enrollments.class_category_fee_id', '=', 'class_category_fees.id')
            ->selectRaw('
                student_class_enrollments.student_class_id,
                student_class_enrollments.class_category_fee_id,
                COUNT(*) as total_count,
                SUM(CASE WHEN student_class_enrollments.is_active = 1 THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN student_class_enrollments.is_active = 0 THEN 1 ELSE 0 END) as inactive_count,
                SUM(CASE WHEN student_class_enrollments.is_free_card = 1 THEN 1 ELSE 0 END) as free_card_count,
                SUM(CASE WHEN student_class_enrollments.is_free_card = 0 AND student_class_enrollments.custom_fee IS NULL THEN 1 ELSE 0 END) as default_fee_count,
                SUM(CASE WHEN student_class_enrollments.is_free_card = 0 AND student_class_enrollments.custom_fee IS NOT NULL THEN 1 ELSE 0 END) as custom_fee_count
            ')
            ->whereIn('student_class_enrollments.student_class_id', $classIds)
            ->groupBy('student_class_enrollments.student_class_id', 'student_class_enrollments.class_category_fee_id')
            ->get()
            ->groupBy('student_class_id');

        return view('admin.student-class-enrollments.index', compact('classes', 'categoryStats'));
    }

    public function categoryStudents(Request $request, StudentClass $studentClass, ClassCategory $classCategory)
    {
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;

        $search = trim($request->input('search', ''));

        $feeIds = ClassCategoryFee::where('student_class_id', $studentClass->id)
            ->where('class_category_id', $classCategory->id)
            ->pluck('id');

        $query = StudentClassEnrollment::query()
            ->with([
                'student',
                'studentClass.grade',
                'studentClass.subject',
                'studentClass.teacher',
                'classCategoryFee.category',
            ])
            ->where('student_class_id', $studentClass->id)
            ->whereIn('class_category_fee_id', $feeIds);

        if ($search !== '') {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('custom_id', 'like', "%{$search}%")
                    ->orWhere('temporary_qr_code', 'like', "%{$search}%")
                    ->orWhere('initial_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderByDesc('is_active')->latest()->paginate($perPage);
        $enrollments->appends($request->all());

        return view('admin.student-class-enrollments.category-students', compact(
            'studentClass',
            'classCategory',
            'enrollments'
        ));
    }

    public function toggleActive(StudentClassEnrollment $studentClassEnrollment)
    {
        $studentClassEnrollment->update([
            'is_active' => ! $studentClassEnrollment->is_active,
            'left_at' => $studentClassEnrollment->is_active ? now()->toDateString() : null,
        ]);

        return back()->with('success', 'Student enrollment status updated successfully.');
    }

    public function create()
    {
        return view('admin.student-class-enrollments.create', [
            'enrollment' => new StudentClassEnrollment(),
        ]);
    }

    public function store(StoreStudentClassEnrollmentRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $this->validateClassCategoryFee($data['student_class_id'], $data['class_category_fee_id']);

            $existing = StudentClassEnrollment::withTrashed()
                ->where('student_id', $data['student_id'])
                ->where('student_class_id', $data['student_class_id'])
                ->where('class_category_fee_id', $data['class_category_fee_id'])
                ->first();

            if ($existing && ! $existing->trashed() && $existing->is_active) {
                throw ValidationException::withMessages([
                    'student_id' => 'Student is already enrolled in this class category.',
                ]);
            }

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->update([
                    ...$data,
                    'is_active' => true,
                    'left_at' => null,
                    'enrolled_at' => $data['enrolled_at'] ?? now()->toDateString(),
                ]);

                return;
            }

            StudentClassEnrollment::create([
                ...$data,
                'is_active' => true,
                'enrolled_at' => $data['enrolled_at'] ?? now()->toDateString(),
            ]);
        });

        return redirect()
            ->route('admin.student-class-enrollments.index')
            ->with('success', 'Student enrolled successfully.');
    }

    public function show(StudentClassEnrollment $studentClassEnrollment)
    {
        $studentClassEnrollment->load([
            'student',
            'studentClass',
            'classCategoryFee.category',
            'payments',
        ]);

        return view('admin.student-class-enrollments.show', compact('studentClassEnrollment'));
    }

    public function edit(StudentClassEnrollment $studentClassEnrollment)
    {
        $studentClassEnrollment->load([
            'student',
            'studentClass.grade',
            'studentClass.subject',
            'studentClass.teacher',
            'classCategoryFee.category',
        ]);

        return view('admin.student-class-enrollments.edit', [
            'enrollment' => $studentClassEnrollment,
        ]);
    }

    public function update(UpdateStudentClassEnrollmentRequest $request, StudentClassEnrollment $studentClassEnrollment)
    {
        $data = $request->validated();

        DB::transaction(function () use ($studentClassEnrollment, $data) {
            if (isset($data['student_class_id'], $data['class_category_fee_id'])) {
                $this->validateClassCategoryFee(
                    $data['student_class_id'],
                    $data['class_category_fee_id']
                );
            }

            if (array_key_exists('is_active', $data) && ! $data['is_active'] && empty($data['left_at'])) {
                $data['left_at'] = now()->toDateString();
            }

            if (array_key_exists('is_active', $data) && $data['is_active']) {
                $data['left_at'] = null;
            }

            $studentClassEnrollment->update($data);
        });

        return redirect()
            ->route('admin.student-class-enrollments.show', $studentClassEnrollment)
            ->with('success', 'Enrollment updated successfully.');
    }



    public function destroy(StudentClassEnrollment $studentClassEnrollment)
    {
        if ($studentClassEnrollment->payments()->exists()) {
            throw ValidationException::withMessages([
                'enrollment' => 'Cannot delete enrollment because payments already exist.',
            ]);
        }

        $studentClassEnrollment->delete();

        return redirect()
            ->route('admin.student-class-enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }

    public function leave(Request $request, StudentClassEnrollment $studentClassEnrollment)
    {
        $request->validate([
            'left_at' => ['nullable', 'date'],
        ]);

        $studentClassEnrollment->update([
            'is_active' => false,
            'left_at' => $request->input('left_at', now()->toDateString()),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Student left from class successfully.');
    }

    public function restore($id)
    {
        $enrollment = StudentClassEnrollment::withTrashed()->findOrFail($id);

        $enrollment->restore();

        $enrollment->update([
            'is_active' => true,
            'left_at' => null,
        ]);

        return redirect()
            ->route('admin.student-class-enrollments.show', $enrollment)
            ->with('success', 'Enrollment restored successfully.');
    }

    private function validateClassCategoryFee($studentClassId, $classCategoryFeeId): void
    {
        $exists = ClassCategoryFee::where('id', $classCategoryFeeId)
            ->where('student_class_id', $studentClassId)
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'class_category_fee_id' => 'Selected category fee is not assigned to this class or fee is inactive.',
            ]);
        }
    }

    public function categoryStudentsPdf(Request $request, StudentClass $studentClass, ClassCategory $classCategory)
    {
        $studentClass->load(['grade', 'subject', 'teacher']);

        $feeIds = ClassCategoryFee::where('student_class_id', $studentClass->id)
            ->where('class_category_id', $classCategory->id)
            ->pluck('id');

        $query = StudentClassEnrollment::query()
            ->with(['student'])
            ->where('student_class_id', $studentClass->id)
            ->whereIn('class_category_fee_id', $feeIds);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->whereHas('student', function ($q) use ($search) {
                $q->where('custom_id', 'like', "%{$search}%")
                    ->orWhere('temporary_qr_code', 'like', "%{$search}%")
                    ->orWhere('initial_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderByDesc('is_active')->latest()->get();

        $pdf = Pdf::loadView(
            'admin.student-class-enrollments.exports.pdf',
            compact('studentClass', 'classCategory', 'enrollments')
        )->setPaper('a4', 'landscape');

        $fileName = str_replace(' ', '-', strtolower($studentClass->class_name))
            . '-' . str_replace(' ', '-', strtolower($classCategory->category_name))
            . '-students.pdf';

        return $pdf->download($fileName);
    }

    public function categoryStudentsExcel(Request $request, StudentClass $studentClass, ClassCategory $classCategory)
    {
        $studentClass->load(['grade', 'subject', 'teacher']);

        $feeIds = ClassCategoryFee::where('student_class_id', $studentClass->id)
            ->where('class_category_id', $classCategory->id)
            ->pluck('id');

        $query = StudentClassEnrollment::query()
            ->with(['student'])
            ->where('student_class_id', $studentClass->id)
            ->whereIn('class_category_fee_id', $feeIds);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

            $query->whereHas('student', function ($q) use ($search) {
                $q->where('custom_id', 'like', "%{$search}%")
                    ->orWhere('temporary_qr_code', 'like', "%{$search}%")
                    ->orWhere('initial_name', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderByDesc('is_active')->latest()->get();

        $fileName = str_replace(' ', '-', strtolower($studentClass->class_name))
            . '-' . str_replace(' ', '-', strtolower($classCategory->category_name))
            . '-students.xlsx';

        return Excel::download(
            new CategoryStudentsExport($enrollments, $studentClass, $classCategory),
            $fileName
        );
    }




    public function classCategoryWisePaymentStudent(
        int $class,
        int $classCategoryFee,
        int $year,
        int $month
    ) {
        try {
            // Get per page from request, default 50
            $perPage = request()->get('per_page', 50);



            $data = $this->studentClassEnrollmentService
                ->classCategoryWisePaymentStudent(
                    $class,
                    $classCategoryFee,
                    $year,
                    $month,
                    (int) $perPage
                );

            return view('admin.student-class-enrollments.category-wise-payment', [
                'students' => $data['students'],
                'pagination' => $data['pagination'],
                'class' => $class,
                'classCategoryFee' => $classCategoryFee,
                'year' => $year,
                'month' => $month,
                'perPage' => $perPage,
            ]);
        } catch (\Throwable $e) {

            logger()->error('Class Category Wise Payment Student Error', [
                'message' => $e->getMessage(),
                'class' => $class,
                'class_category_fee' => $classCategoryFee,
                'year' => $year,
                'month' => $month,
            ]);

            return back()->with('error', 'Something went wrong while fetching data.');
        }
    }
}
