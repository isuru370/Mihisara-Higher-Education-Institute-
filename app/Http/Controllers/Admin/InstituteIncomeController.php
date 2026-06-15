<?php

namespace App\Http\Controllers\Admin;

use App\Services\InstituteIncomeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class InstituteIncomeController extends Controller
{
    protected InstituteIncomeService $instituteIncomeService;

    public function __construct(
        InstituteIncomeService $instituteIncomeService
    ) {
        $this->instituteIncomeService = $instituteIncomeService;
    }


    public function monthlyIncomeReport(Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $report = $this->instituteIncomeService->monthlyInstituteIncome($year, $month);

        return view('admin.institute-income.monthly-report', [
            'success' => true,
            'year' => $year,
            'month' => $month,
            'summary' => $report['summary'],
            'teacher_summaries' => $report['teacher_summaries'] ?? [],
            'organizer_summaries' => $report['organizer_summaries'] ?? [],
            'class_summaries' => $report['class_summaries'] ?? [],
            'admission_payment_list' => $report['admission_payment_list'] ?? [],
            'extra_income_list' => $report['extra_income_list'] ?? [],
            'expense_list' => $report['expense_list'] ?? [],
        ]);
    }
}
