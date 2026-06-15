<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\PaymentSplitSnapshot;
use App\Models\StudentClass;
use App\Models\TeacherPayment;
use App\Models\TeacherSalary;
use Carbon\Carbon;

class TeacherSalaryService
{
    public function teachersMonthlyIncome(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        $teachers = Teacher::query()->get();

        $salaryRows = [];

        $summary = [
            'gross_earnings' => 0,
            'total_advance' => 0,
            'total_deduction' => 0,
            'total_other' => 0,
            'calculated_net_total' => 0,
            'db_net_total' => 0,
            'net_difference_total' => 0,
            'net_payable' => 0,
            'total_teachers' => 0,
        ];

        foreach ($teachers as $teacher) {

            $gross = PaymentSplitSnapshot::query()
                ->where('teacher_id', $teacher->id)
                ->whereBetween('payment_date', [$start, $end])
                ->sum('teacher_amount');

            $payments = TeacherPayment::query()
                ->where('teacher_id', $teacher->id)
                ->whereBetween('payment_date', [$start, $end])
                ->get();

            $advance = $payments->where('payment_type', 'advance')->sum('amount');
            $deduction = $payments->where('payment_type', 'deduction')->sum('amount');
            $other = $payments->where('payment_type', 'other')->sum('amount');

            $calculatedNet = $gross - ($advance + $deduction + $other);

            $salaryRecord = TeacherSalary::query()
                ->where('teacher_id', $teacher->id)
                ->where('salary_year', $year)
                ->where('salary_month', $month)
                ->first();

            $dbNet = $salaryRecord?->net_amount;
            $status = $salaryRecord?->status ?? 'pending';

            $finalNet = $dbNet ?? $calculatedNet;
            $netDifference = $salaryRecord ? ($calculatedNet - $dbNet) : 0;

            $salaryRows[] = [
                'teacher_id' => $teacher->id,
                'teacher_custom_id' => $teacher->custom_id,
                'teacher_name' => $teacher->full_name,

                'gross_earnings' => $gross,
                'advance' => $advance,
                'deduction' => $deduction,
                'other' => $other,

                'calculated_net' => $calculatedNet,
                'net_payable' => $finalNet,

                'status' => $status,
                'net_difference' => $netDifference,
            ];

            $summary['gross_earnings'] += $gross;
            $summary['total_advance'] += $advance;
            $summary['total_deduction'] += $deduction;
            $summary['total_other'] += $other;
            $summary['calculated_net_total'] += $calculatedNet;
            $summary['db_net_total'] += $dbNet ?? 0;
            $summary['net_difference_total'] += $netDifference;
            $summary['net_payable'] += $finalNet;
            $summary['total_teachers']++;
        }

        return [
            'data' => $salaryRows,
            'summary' => $summary,
            'month_name' => Carbon::create()->month($month)->format('F'),
        ];
    }


    public function getTeacherSalaryDetails(int $teacherId, int $year, int $month): array
    {
        $teacher = Teacher::findOrFail($teacherId);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        // ======================
        // CLASS INCOME SNAPSHOTS
        // ======================
        $snapshots = PaymentSplitSnapshot::query()
            ->where('teacher_id', $teacherId)
            ->whereBetween('payment_date', [$start, $end])
            ->get();

        $totalClassIncome = $snapshots->sum('payment_amount');
        $teacherEarnings = $snapshots->sum('teacher_amount');
        $instituteEarnings = $snapshots->sum('institution_amount');
        $organizerEarnings = $snapshots->sum('organizer_amount');

        // ======================
        // PAYMENTS (DEDUCTIONS)
        // ======================
        $payments = TeacherPayment::query()
            ->where('teacher_id', $teacherId)
            ->whereBetween('payment_date', [$start, $end])
            ->get();

        $advance = $payments->where('payment_type', 'advance')->sum('amount');
        $deduction = $payments->where('payment_type', 'deduction')->sum('amount');
        $other = $payments->where('payment_type', 'other')->sum('amount');

        // ======================
        // PAYMENT DETAILS LIST
        // ======================
        $paymentDetails = $payments->map(fn($payment) => [
            'id' => $payment->id,
            'type' => $payment->payment_type,
            'amount' => $payment->amount,
            'date' => $payment->payment_date,
            'reason' => $payment->reason,
        ])->values();

        // ======================
        // SALARY RECORD
        // ======================
        $salaryRecord = TeacherSalary::query()
            ->where('teacher_id', $teacherId)
            ->where('salary_year', $year)
            ->where('salary_month', $month)
            ->first();

        $salaryStatus = $salaryRecord?->status ?? 'pending';
        $salaryPaid = ($salaryRecord && $salaryRecord->status === 'paid')
            ? $salaryRecord->net_amount
            : 0;

        // ======================
        // NET CALCULATION
        // ======================
        $netPayable = $teacherEarnings - ($advance + $deduction + $other);

        return [
            'teacher' => $teacher,
            'year' => $year,
            'month' => $month,
            'period' => "{$year}-{$month}",

            'total_class_income' => $totalClassIncome,

            'teacher_earnings' => $teacherEarnings,
            'institute_earnings' => $instituteEarnings,
            'organizer_earnings' => $organizerEarnings,

            'advance' => $advance,
            'deduction' => $deduction,
            'other' => $other,

            'net_payable' => $netPayable,

            'salary_paid' => $salaryPaid,
            'salary_status' => $salaryStatus,

            'payment_details' => $paymentDetails,
        ];
    }

    public function teacherSalaryPaid(
        int $teacherId,
        int $year,
        int $month,
        float $calculatedNet,
        ?string $note = null
    ) {

        // =========================
        // Teacher Gross Earnings
        // =========================
        $grossAmount = PaymentSplitSnapshot::query()
            ->where('teacher_id', $teacherId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->sum('teacher_amount');

        // =========================
        // Advance Total
        // =========================
        $advanceDeduction = TeacherPayment::query()
            ->where('teacher_id', $teacherId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->where('payment_type', 'advance')
            ->sum('amount');

        // =========================
        // Deduction + Other Total
        // =========================
        $otherDeduction = TeacherPayment::query()
            ->where('teacher_id', $teacherId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->whereIn('payment_type', ['deduction', 'other'])
            ->sum('amount');

        // =========================
        // Final Net Salary
        // =========================
        $netAmount = $grossAmount - ($advanceDeduction + $otherDeduction);

        // =========================
        // Auto Note
        // =========================
        $autoNote = "Salary Paid for "
            . Carbon::create($year, $month, 1)->format('F Y');

        if ($note) {
            $autoNote .= " | " . $note;
        }

        // =========================
        // Save Salary Record
        // =========================
        return TeacherSalary::updateOrCreate(
            [
                'teacher_id'   => $teacherId,
                'salary_year'  => $year,
                'salary_month' => $month,
            ],
            [
                'gross_amount'      => $grossAmount,
                'advance_deduction' => $advanceDeduction,
                'other_deduction'   => $otherDeduction,
                'net_amount'        => $netAmount,
                'status'            => 'paid',
                'paid_at'           => now(),
                'user_id'           => auth()->id(),
                'note'              => $autoNote,
            ]
        );
    }

    public function teacherPaymentStore(
        int $teacherId,
        string $paymentType,
        float $amount,
        string $paymentDate,
        ?string $reason = null,
        ?string $note = null
    ) {
        $date = Carbon::parse($paymentDate);

        $year = $date->year;
        $month = $date->month;

        // 💰 teacher monthly income (FIXED)
        $teacherIncome = PaymentSplitSnapshot::query()
            ->where('teacher_id', $teacherId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->sum('teacher_amount');

        $usedAmount = TeacherPayment::query()
            ->where('teacher_id', $teacherId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->sum('amount');

        $salaryPaid = TeacherSalary::query()
            ->where('teacher_id', $teacherId)
            ->where('salary_year', $year)
            ->where('salary_month', $month)
            ->where('status', 'paid')
            ->value('net_amount') ?? 0;

        $availableLimit = $teacherIncome - $usedAmount - $salaryPaid;

        // ❌ validation (server-side safety only)
        if ($amount > $availableLimit) {
            throw new \Exception(
                "Amount exceeds allowed limit. Available: " . number_format($availableLimit, 2)
            );
        }

        return TeacherPayment::create([
            'teacher_id'   => $teacherId,
            'user_id'      => auth()->id(),
            'payment_type' => $paymentType,
            'amount'       => $amount,
            'payment_date' => $paymentDate,
            'reason'       => $reason,
            'note'         => $note,
            'status'       => 'paid',
        ]);
    }


    public function teacherAllPayments(int $teacherId, int $year, int $month)
    {
        // Get all teacher payments for the specified month
        $payments = TeacherPayment::query()
            ->where('teacher_id', $teacherId)
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->where('status', 'paid')
            ->orderBy('payment_date', 'desc')
            ->orderBy('payment_type', 'asc')
            ->get();

        // Get the teacher salary record for this month
        $teacherSalary = TeacherSalary::query()
            ->where('teacher_id', $teacherId)
            ->where('salary_year', $year)
            ->where('salary_month', $month)
            ->first();

        // Determine salary status
        $isSalaryPaid = false;
        $salaryStatus = 'unpaid';
        $salaryPaidAmount = 0;

        if ($teacherSalary) {
            $isSalaryPaid = $teacherSalary->status === 'paid';
            $salaryStatus = $teacherSalary->status ?? 'unpaid';
            $salaryPaidAmount = $teacherSalary->paid_amount ?? 0;
        }

        // Calculate totals from payments
        $totalAdvances = $payments->where('payment_type', 'advance')->sum('amount');
        $totalDeductions = $payments->where('payment_type', 'deduction')->sum('amount');
        $totalOther = $payments->where('payment_type', 'other')->sum('amount');
        $totalPayments = $payments->sum('amount');

        return [
            'teacher_id' => $teacherId,
            'year' => $year,
            'month' => $month,
            'month_name' => date('F', mktime(0, 0, 0, $month, 1)), // ADD THIS LINE

            // Payment records
            'payments' => $payments,
            'payments_count' => $payments->count(),

            // Payment totals
            'totals' => [
                'advance' => $totalAdvances,
                'deduction' => $totalDeductions,
                'other' => $totalOther,
                'all' => $totalPayments
            ],

            // Salary status (boolean)
            'is_salary_paid' => $isSalaryPaid,
            'salary_status' => $salaryStatus,  // 'paid' or 'unpaid'
            'salary_paid_amount' => $salaryPaidAmount,

            // Teacher salary record (if exists)
            'teacher_salary' => $teacherSalary,

            // Additional info
            'has_salary_record' => !is_null($teacherSalary),
            'has_any_payments' => $payments->isNotEmpty(),
        ];
    }

    public function teacherPaymentDelete(int $paymentId): bool
    {
        try {
            $payment = TeacherPayment::findOrFail($paymentId);

            // Check if salary is already paid for this month
            $salaryPaid = TeacherSalary::query()
                ->where('teacher_id', $payment->teacher_id)
                ->where('salary_year', $payment->payment_date->year)
                ->where('salary_month', $payment->payment_date->month)
                ->where('status', 'paid')
                ->exists();

            if ($salaryPaid) {
                // Fix: Convert to Carbon if needed, then format
                $date = $payment->payment_date instanceof \DateTime
                    ? $payment->payment_date
                    : Carbon::parse($payment->payment_date);

                throw new \Exception("Cannot delete payment. Salary for " .
                    $date->format('F Y') .
                    " has already been marked as paid.");
            }

            // Perform soft delete (since you have SoftDeletes trait)
            return $payment->delete();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new \Exception("Payment record not found.");
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function teacherPaymentAndClassSummery(
        int $teacherId,
        int $year,
        int $month
    ): array {

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        /*
    |--------------------------------------------------------------------------
    | Teacher Total
    |--------------------------------------------------------------------------
    */

        $teacherTotal = PaymentSplitSnapshot::query()
            ->where('teacher_id', $teacherId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('teacher_amount');

        /*
    |--------------------------------------------------------------------------
    | Classes With Optimized Eager Loading
    |--------------------------------------------------------------------------
    */

        $classes = StudentClass::query()
            ->where('teacher_id', $teacherId)
            ->where('is_active', true)

            ->with([

                /*
        |--------------------------------------------------------------------------
        | Category Fees
        |--------------------------------------------------------------------------
        */
                'categoryFees.category:id,category_name',

                /*
        |--------------------------------------------------------------------------
        | Grade
        |--------------------------------------------------------------------------
        */
                'grade:id,grade_name',

                /*
        |--------------------------------------------------------------------------
        | Enrollments
        |--------------------------------------------------------------------------
        */
                'enrollments' => function ($query) use ($year, $month) {

                    $query
                        ->where('is_active', true)

                        ->with([
                            'classCategoryFee:id,class_category_id,fee',
                            'classCategoryFee.category:id,category_name',
                        ])

                        /*
                |--------------------------------------------------------------------------
                | Monthly Payments Sum
                |--------------------------------------------------------------------------
                */
                        ->withSum([
                            'payments as monthly_paid_amount' => function ($paymentQuery) use ($year, $month) {

                                $paymentQuery
                                    ->where('status', 'completed')
                                    ->whereYear('paid_at', $year)
                                    ->whereMonth('paid_at', $month);
                            }
                        ], 'amount');
                }

            ])
            ->get();


        /*
    |--------------------------------------------------------------------------
    | Final Result Build
    |--------------------------------------------------------------------------
    */

        $classSummaries = [];

        $grandStudentCount = 0;
        $grandClassCount = $classes->count();

        foreach ($classes as $class) {

            $classEnrollments = $class->enrollments;

            $classStudentCount = $classEnrollments->count();

            $grandStudentCount += $classStudentCount;

            $classTotal = 0;

            $categorySummaries = [];

            foreach ($class->categoryFees as $categoryFee) {

                /*
            |--------------------------------------------------------------------------
            | Category Enrollments
            |--------------------------------------------------------------------------
            */

                $enrollments = $classEnrollments->where(
                    'class_category_fee_id',
                    $categoryFee->id
                );

                $studentCount = $enrollments->count();

                $paidStudents = 0;
                $partialStudents = 0;
                $unpaidStudents = 0;
                $freeCardStudents = 0;

                $categoryTotal = 0;

                foreach ($enrollments as $enrollment) {

                    /*
                |--------------------------------------------------------------------------
                | FREE CARD
                |--------------------------------------------------------------------------
                */

                    if ($enrollment->is_free_card) {

                        $freeCardStudents++;

                        continue;
                    }

                    $paidAmount = (float) ($enrollment->monthly_paid_amount ?? 0);

                    $finalFee = (float) $enrollment->final_fee;

                    /*
                |--------------------------------------------------------------------------
                | Payment Status
                |--------------------------------------------------------------------------
                */

                    if ($paidAmount <= 0) {

                        $unpaidStudents++;
                    } elseif ($paidAmount < $finalFee) {

                        $partialStudents++;
                    } else {

                        $paidStudents++;
                    }

                    $categoryTotal += $paidAmount;
                }

                $classTotal += $categoryTotal;

                $categorySummaries[] = [
                    'category_fee_id' => $categoryFee?->id,

                    'category_id' => $categoryFee->category?->id,

                    'category_name' => $categoryFee->category?->category_name,

                    'fee' => (float) $categoryFee->fee,

                    'student_count' => $studentCount,

                    'paid_students' => $paidStudents,

                    'partial_students' => $partialStudents,

                    'unpaid_students' => $unpaidStudents,

                    'free_card_students' => $freeCardStudents,

                    'category_total' => round($categoryTotal, 2),
                ];
            }

            $classSummaries[] = [

                'class_id' => $class->id,

                'class_name' => $class->class_name,

                'grade_name' => $class->grade?->grade_name,

                'total_students' => $classStudentCount,

                'class_total' => round($classTotal, 2),

                'categories' => $categorySummaries,
            ];
        }

        /*
    |--------------------------------------------------------------------------
    | Final Response
    |--------------------------------------------------------------------------
    */

        return [

            'teacher_id' => $teacherId,

            'year' => $year,

            'month' => $month,

            'teacher_total' => round($teacherTotal, 2),

            'total_student_count' => $grandStudentCount,

            'total_class_count' => $grandClassCount,

            'classes' => $classSummaries,
        ];
    }
}
