<?php

namespace App\Services;

use App\Models\AdmissionPayment;
use App\Models\PaymentSplitSnapshot;
use App\Models\InstitutePayment;
use App\Models\ExtraIncome;

class InstituteIncomeService
{
    public function monthlyInstituteIncome($year, $month): array
    {
        // ==================== 1. MAIN PAYMENTS FROM SPLIT SNAPSHOTS ====================
        $snapshots = PaymentSplitSnapshot::with(['teacher', 'organizer', 'studentClass.grade'])
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->get();

        // ---------------- MAIN INCOME ----------------
        $mainIncome = [
            'total_income' => $snapshots->sum('payment_amount'),
            'teacher_income' => $snapshots->sum('teacher_amount'),
            'organizer_income' => $snapshots->sum('organizer_amount'),
            'institution_income' => $snapshots->sum('institution_amount'),
        ];

        // 2. ADMISSION PAYMENT INCOME
        $admissionPayments = AdmissionPayment::query()
            ->with(['student', 'admission'])
            ->paid()
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->get();

        $totalAdmissionIncome = $admissionPayments->sum('amount');

        $admissionPaymentSummaries = $admissionPayments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'student_id' => $payment->student_id,
                'student_name' => $payment->student?->initial_name,
                'admission_id' => $payment->admission_id,
                'admission_name' => $payment->admission?->name,
                'amount' => $payment->amount,
                'paid_at' => optional($payment->paid_at)->format('Y-m-d'),
                'payment_method' => $payment->payment_method,
                'receipt_number' => $payment->receipt_number,
                'note' => $payment->note,
            ];
        })->values();

        // ==================== 2. EXTRA INCOME (Additional Income) ====================
        $extraIncomes = ExtraIncome::query()
            ->whereYear('income_date', $year)
            ->whereMonth('income_date', $month)
            ->where('status', 'received')
            ->get();

        $totalExtraIncome = $extraIncomes->sum('amount');

        $extraIncomeSummaries = $extraIncomes->map(function ($income) {
            return [
                'id' => $income->id,
                'amount' => $income->amount,
                'income_date' => optional($income->income_date)->format('Y-m-d'),
                'reason' => $income->reason,
                'reason_code' => $income->reason_code,
                'income_type' => $income->income_type,
                'note' => $income->note,
                'created_by' => $income->createdBy?->name,
            ];
        })->values();

        // ==================== 3. EXPENSES (Institute Payments) ====================
        $expenses = InstitutePayment::query()
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->where('status', 'paid')
            ->get();

        $totalExpenses = $expenses->sum('amount');

        $expenseSummaries = $expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'amount' => $expense->amount,
                'payment_date' => optional($expense->payment_date)->format('Y-m-d'),
                'reason' => $expense->reason,
                'reason_code' => $expense->reason_code,
                'note' => $expense->note,
                'created_by' => $expense->createdBy?->name,
            ];
        })->values();

        // ==================== 4. CALCULATE GROSS & NET INCOME ====================
        $grossIncome = $mainIncome['institution_income'] + $totalExtraIncome + $totalAdmissionIncome;
        $netIncome = $grossIncome - $totalExpenses;

        $overall = [
            'class_income' => $mainIncome['total_income'],
            'teacher_income' => $mainIncome['teacher_income'],
            'organizer_income' => $mainIncome['organizer_income'],
            'institution_income' => $mainIncome['institution_income'],
            'admission_income' => $totalAdmissionIncome,
            'extra_income' => $totalExtraIncome,
            'total_expenses' => $totalExpenses,
            'gross_income' => $grossIncome,
            'net_income' => $netIncome,
        ];

        // ==================== 5. TEACHER SUMMARIES ====================
        $teacherSummaries = $snapshots->groupBy('teacher_id')
            ->map(function ($rows) {
                $teacher = $rows->first()?->teacher;

                return [
                    'teacher_id' => $teacher?->id,
                    'teacher_custom_id' => $teacher?->custom_id,
                    'teacher_name' => $teacher?->full_name ?? 'Unknown',
                    'teacher_initials' => $teacher?->initials,
                    'payment_count' => $rows->count(),
                    'total_income' => $rows->sum('payment_amount'),
                    'teacher_income' => $rows->sum('teacher_amount'),
                    'organizer_income' => $rows->sum('organizer_amount'),
                    'institution_income' => $rows->sum('institution_amount'),
                ];
            })->values();

        // ==================== 6. ORGANIZER SUMMARIES ====================
        // Null organizer rows will be grouped together under the "null" bucket.
        // ==================== 6. ORGANIZER SUMMARIES ====================
        $organizerSummaries = $snapshots
            ->filter(fn($row) => $row->organizer !== null)   // only real organizers
            ->groupBy('organizer_id')
            ->map(function ($rows) {
                $organizer = $rows->first()?->organizer;

                return [
                    'organizer_id' => $organizer?->id,
                    'organizer_code' => $organizer?->code,
                    'organizer_name' => $organizer?->name,
                    'payment_count' => $rows->count(),
                    'total_income' => $rows->sum('payment_amount'),
                    'teacher_income' => $rows->sum('teacher_amount'),
                    'organizer_income' => $rows->sum('organizer_amount'),
                    'institution_income' => $rows->sum('institution_amount'),
                ];
            })
            ->values();

        // ==================== 7. CLASS SUMMARIES ====================
        $classSummaries = $snapshots->groupBy('student_class_id')
            ->map(function ($rows) {
                $class = $rows->first()?->studentClass;

                return [
                    'class_id' => $class?->id,
                    'class_name' => $class?->class_name ?? 'Unknown',
                    'grade_name' => $class?->grade?->grade_name ?? 'Unknown',
                    'payment_count' => $rows->count(),
                    'total_income' => $rows->sum('payment_amount'),
                    'teacher_income' => $rows->sum('teacher_amount'),
                    'organizer_income' => $rows->sum('organizer_amount'),
                    'institution_income' => $rows->sum('institution_amount'),
                ];
            })->values();

        return [
            'year' => $year,
            'month' => $month,
            'summary' => $overall,
            'admission_payment_list' => $admissionPaymentSummaries,
            'teacher_summaries' => $teacherSummaries,
            'organizer_summaries' => $organizerSummaries,
            'class_summaries' => $classSummaries,
            'extra_income_list' => $extraIncomeSummaries,
            'expense_list' => $expenseSummaries,
        ];
    }
}
