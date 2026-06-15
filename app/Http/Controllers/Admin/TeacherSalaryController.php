<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentSplitSnapshot;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use App\Models\TeacherSalary;
use App\Services\TeacherSalaryService;
use Carbon\Carbon;

class TeacherSalaryController extends Controller
{
    protected TeacherSalaryService $teacherSalaryService;

    public function __construct(TeacherSalaryService $teacherSalaryService)
    {
        $this->teacherSalaryService = $teacherSalaryService;
    }

    public function index(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        try {
            $data = $this->teacherSalaryService->teachersMonthlyIncome($year, $month);

            return view('admin.teacher-salaries.index', [
                'salaryRows' => $data['data'],
                'summary' => $data['summary'],
                'year' => $year,
                'month' => $month,
                'monthName' => $data['month_name'],
            ]);
        } catch (\Throwable $e) {

            logger()->error('Teacher salary report failed', [
                'error' => $e->getMessage(),
                'year' => $year,
                'month' => $month,
            ]);

            return back()->with('error', 'Failed to load salary data.');
        }
    }

    public function show(Request $request, int $teacherId, int $year, int $month)
    {
        try {
            // dropdown override support
            $year = (int) $request->get('year', $year);
            $month = (int) $request->get('month', $month);

            $data = $this->teacherSalaryService
                ->getTeacherSalaryDetails($teacherId, $year, $month);

            return view('admin.teacher-salaries.show', [
                'data' => $data,
                'year' => $year,
                'month' => $month,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return redirect()
                ->route('admin.teacher-salaries.index')
                ->with('error', 'Teacher not found');
        } catch (\Throwable $e) {

            logger()->error('Teacher salary show failed', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId,
                'year' => $year,
                'month' => $month,
            ]);

            return redirect()
                ->route('admin.teacher-salaries.index')
                ->with('error', 'Failed to load teacher details.');
        }
    }

    public function teacherSalaryPaid(Request $request, int $teacherId)
    {
        try {

            $request->validate([
                'year' => 'required|integer',
                'month' => 'required|integer|min:1|max:12',
                'amount' => 'required|numeric|min:0',
                'note' => 'nullable|string',
            ]);

            $this->teacherSalaryService->teacherSalaryPaid(
                $teacherId,
                (int) $request->year,
                (int) $request->month,
                (float) $request->amount,
                $request->note
            );

            return back()->with([
                'success' => 'Salary paid successfully',
                'slip_open' => true, // 👈 important flag
                'slip_teacher' => $teacherId,
                'slip_year' => $request->year,
                'slip_month' => $request->month,
            ]);
        } catch (\Throwable $e) {

            logger()->error('Teacher salary pay failed', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId,
                'request' => $request->all(),
            ]);

            return back()->with('error', 'Failed to pay teacher salary.');
        }
    }

    public function printSalarySlip(int $teacherId, int $year, int $month)
    {
        try {

            $teacher = Teacher::findOrFail($teacherId);

            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();

            // =========================================================
            // SNAPSHOTS (WITH PROPER RELATIONS)
            // =========================================================
            $snapshots = PaymentSplitSnapshot::with([
                'studentClass.subject',
                'studentClass.grade'
            ])
                ->where('teacher_id', $teacherId)
                ->whereBetween('payment_date', [$start, $end])
                ->get();

            // =========================================================
            // GROUP BY CLASS
            // =========================================================
            $grouped = $snapshots->groupBy('student_class_id');

            $earnings = [];

            $totalClassAmount = 0;
            $totalTeacherEarnings = 0;
            $totalOrganizeCut = 0;
            $totalInstitutionCut = 0;

            foreach ($grouped as $classId => $items) {

                $first = $items->first();

                $class = $first->studentClass;

                // =====================================================
                // SAFE SUBJECT + GRADE NAME (FIX UNKNOWN ISSUE)
                // =====================================================
                $subject = $class?->subject?->subject_name ?? 'Unknown Subject';
                $grade = $class?->grade?->grade_name ?? 'Unknown Grade';

                $className = "{$subject} - Grade {$grade}";

                $classTotal = $items->sum('payment_amount');
                $teacherAmount = $items->sum('teacher_amount');
                $organizerAmount = $items->sum('organizer_amount');
                $institutionAmount = $items->sum('institution_amount');

                $teacherPercentage = $first->teacher_percentage ?? 0;
                $organizerPercentage = $first->organizer_percentage ?? 0;
                $institutionPercentage = $first->institution_percentage ?? 0;

                // =====================================================
                // SLIP ROW
                // =====================================================
                $earnings[] = [
                    'description' => $className . ' Teacher Salary',

                    'class_total' => $classTotal,

                    'teacher_percentage' => $teacherPercentage,
                    'teacher_share' => $teacherAmount,

                    'organize_percentage' => $organizerPercentage,
                    'organize' => $organizerAmount,

                    'institution_percentage' => $institutionPercentage,
                    'institution_cut' => $institutionAmount,

                    'amount' => $teacherAmount,
                ];

                // =====================================================
                // TOTALS
                // =====================================================
                $totalClassAmount += $classTotal;
                $totalTeacherEarnings += $teacherAmount;
                $totalOrganizeCut += $organizerAmount;
                $totalInstitutionCut += $institutionAmount;
            }

            // =========================================================
            // DEDUCTIONS
            // =========================================================
            $payments = TeacherPayment::where('teacher_id', $teacherId)
                ->whereBetween('payment_date', [$start, $end])
                ->get();

            $advance = $payments->where('payment_type', 'advance')->sum('amount');
            $deduction = $payments->where('payment_type', 'deduction')->sum('amount');
            $other = $payments->where('payment_type', 'other')->sum('amount');

            $deductions = [];

            if ($advance > 0) {
                $deductions[] = ['description' => 'Advance', 'amount' => $advance];
            }

            if ($deduction > 0) {
                $deductions[] = ['description' => 'Deduction', 'amount' => $deduction];
            }

            if ($other > 0) {
                $deductions[] = ['description' => 'Other Expenses', 'amount' => $other];
            }

            $totalDeductions = $advance + $deduction + $other;

            // =========================================================
            // NET SALARY
            // =========================================================
            $netSalary = $totalTeacherEarnings - $totalDeductions;

            // =========================================================
            // SALARY STATUS
            // =========================================================
            $salary = TeacherSalary::where([
                'teacher_id' => $teacherId,
                'salary_year' => $year,
                'salary_month' => $month,
            ])->first();

            // =========================================================
            // FINAL RESPONSE
            // =========================================================
            $slipData = [
                'status' => 'success',

                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->full_name,

                'month_year_display' =>
                date('F', mktime(0, 0, 0, $month, 1)) . ' ' . $year,

                'date_generated' => now()->format('Y-m-d'),

                // totals
                'total_class_amount' => $totalClassAmount,
                'total_teacher_earnings' => $totalTeacherEarnings,
                'total_organize_cut' => $totalOrganizeCut,
                'total_institution_cut' => $totalInstitutionCut,

                'total_addition' => $totalTeacherEarnings,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,

                'is_salary_paid' => $salary && $salary->status === 'paid',

                'earnings' => $earnings,
                'deductions' => $deductions,

                'payment_method' => 'Cash / Bank Transfer',
            ];

            return view('admin.teacher-salaries.salary-slip-exact', [
                'data' => $slipData
            ]);
        } catch (\Throwable $e) {

            logger()->error('Salary slip generation failed', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId,
                'year' => $year,
                'month' => $month,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to generate salary slip.');
        }
    }

    public function teacherPaymentStore(Request $request, int $teacherId)
    {
        try {

            $validated = $request->validate([
                'payment_type' => 'required|in:advance,deduction,other',
                'amount' => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'reason' => 'nullable|string|max:150',
                'note' => 'nullable|string',
            ]);

            $this->teacherSalaryService->teacherPaymentStore(
                $teacherId,
                $validated['payment_type'],
                (float) $validated['amount'],
                $validated['payment_date'], // ✅ string only
                $validated['reason'] ?? null,
                $validated['note'] ?? null
            );

            return back()->with('success', 'Payment record added successfully.');
        } catch (\Throwable $e) {

            logger()->error('Teacher payment store failed', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId,
                'request' => $request->all(),
            ]);

            return back()->with('error', 'Failed to add payment record.');
        }
    }

    public function paymentDetailsView(int $teacherId, int $year, int $month)
    {
        // Get teacher details
        $teacher = Teacher::findOrFail($teacherId);

        // Get payment details data
        $data = $this->teacherSalaryService->teacherAllPayments($teacherId, $year, $month);

        // Return view with data
        return view('admin.teacher-salaries.payment-details', [
            'teacherId' => $teacherId,
            'teacher' => $teacher,
            'year' => $year,
            'month' => $month,
            'month_name' => date('F', mktime(0, 0, 0, $month, 1)), // ADD THIS LINE
            'payments' => $data['payments'],
            'payments_count' => $data['payments_count'],
            'payments_list' => $data['payments_list'] ?? $data['payments'],
            'totals' => $data['totals'],
            'salary_status' => $data['salary_status'],
            'salary_record' => $data['salary_record'] ?? null,
            'has_salary_record' => $data['has_salary_record'],
            'has_any_payments' => $data['has_any_payments'],
        ]);
    }

    public function paymentDelete(int $paymentId)
    {
        try {
            $this->teacherSalaryService->teacherPaymentDelete($paymentId);

            return back()->with('success', 'Payment record deleted successfully.');
        } catch (\Throwable $e) {

            logger()->error('Teacher payment delete failed', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
            ]);

            return back()->with('error', 'Failed to delete payment record. It may be linked to a paid salary or already deleted.');
        }
    }

    public function teacherPaymentAndClassSummery(
        int $teacherId,
        int $year,
        int $month
    ) {
        try {

            $data = $this->teacherSalaryService
                ->teacherPaymentAndClassSummery(
                    $teacherId,
                    $year,
                    $month
                );

            return view(
                'admin.teacher-salaries.summary',
                [
                    'data' => $data,
                    'teacherId' => $teacherId,
                    'year' => $year,
                    'month' => $month,
                ]
            );
            try { $data = $this->studentClassEnrollmentService ->classCategoryWisePaymentStudent( (int) $class, (int) $classCategoryFee, (int) $validated['year'], (int) $validated['month'] ); return view('student-class-enrollments.category-wise-payment', [ 'success' => true, 'data' => $data ]); } catch (Throwable $e) { Log::error('Class Category Wise Payment Student Error', [ 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'class' => $class, 'class_category_fee' => $classCategoryFee, 'year' => $year, 'month' => $month, ]); return redirect()->back()->with([ 'success' => false, 'error' => 'Something went wrong while fetching data.' ]); }
        } catch (\Throwable $e) {

            logger()->error(
                'Teacher payment and class summary fetch failed',
                [
                    'error' => $e->getMessage(),
                    'teacher_id' => $teacherId,
                    'year' => $year,
                    'month' => $month,
                ]
            );

            return back()->with(
                'error',
                'Failed to fetch payment and class summary.'
            );
        }
    }
}
