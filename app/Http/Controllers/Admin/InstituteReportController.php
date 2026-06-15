<?php

namespace App\Http\Controllers\Admin;

use App\Exports\institute\InstituteReportExport;
use App\Http\Controllers\Controller;
use App\Models\ExtraIncome;
use App\Models\InstitutePayment;
use App\Models\PaymentSplitSnapshot;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class InstituteReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->getFilters($request);

        $snapshotQuery = $this->buildSnapshotQuery($filters);
        $extraIncomeQuery = $this->buildExtraIncomeQuery($filters);
        $expenseQuery = $this->buildExpenseQuery($filters);

        $snapshotPayments = $snapshotQuery
            ->with(['studentClass', 'teacher', 'organizer', 'createdBy'])
            ->orderBy('payment_date', 'asc')
            ->get();

        $extraIncomes = $extraIncomeQuery
            ->with('createdBy')
            ->orderBy('income_date', 'asc')
            ->get();

        $expenses = $expenseQuery
            ->with('createdBy')
            ->orderBy('payment_date', 'asc')
            ->get();

        $snapshotIncomeTotal = (float) $snapshotPayments->sum('institution_amount');
        $extraIncomeTotal = (float) $extraIncomes->sum('amount');
        $expenseTotal = (float) $expenses->sum('amount');

        $summary = [
            'snapshot_income_total' => $snapshotIncomeTotal,
            'extra_income_total'    => $extraIncomeTotal,
            'total_income'          => $snapshotIncomeTotal + $extraIncomeTotal,
            'total_expense'         => $expenseTotal,
            'net_total'             => ($snapshotIncomeTotal + $extraIncomeTotal) - $expenseTotal,
            'snapshot_count'        => $snapshotPayments->count(),
            'extra_income_count'    => $extraIncomes->count(),
            'expense_count'         => $expenses->count(),
            'total_records'         => $snapshotPayments->count() + $extraIncomes->count() + $expenses->count(),
        ];

        return view('admin.institute_report.index', compact(
            'filters',
            'summary',
            'snapshotPayments',
            'extraIncomes',
            'expenses'
        ));
    }

    public function institutePaymentReportExcel(Request $request)
    {
        try {
            $filters = $this->getFilters($request);

            $fileName = 'institute-report-'
                . ($filters['start_date'] ?? now()->format('Y-m-d'))
                . '-to-'
                . ($filters['end_date'] ?? now()->format('Y-m-d'))
                . '.xlsx';

            return Excel::download(
                new InstituteReportExport($filters),
                $fileName
            );
        } catch (\Throwable $e) {
            Log::error('Error generating Excel report: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to generate Excel report'
            ], 500);
        }
    }

    public function institutePaymentReportPdf(Request $request)
    {
        try {
            $filters = $this->getFilters($request);

            $snapshotPayments = $this->buildSnapshotQuery($filters)
                ->with(['studentClass', 'teacher', 'organizer', 'createdBy'])
                ->orderBy('payment_date', 'asc')
                ->get();

            $extraIncomes = $this->buildExtraIncomeQuery($filters)
                ->with('createdBy')
                ->orderBy('income_date', 'asc')
                ->get();

            $expenses = $this->buildExpenseQuery($filters)
                ->with('createdBy')
                ->orderBy('payment_date', 'asc')
                ->get();

            $snapshotIncomeTotal = (float) $snapshotPayments->sum('institution_amount');
            $extraIncomeTotal = (float) $extraIncomes->sum('amount');
            $expenseTotal = (float) $expenses->sum('amount');

            $summary = [
                'snapshot_income_total' => $snapshotIncomeTotal,
                'extra_income_total'    => $extraIncomeTotal,
                'total_income'          => $snapshotIncomeTotal + $extraIncomeTotal,
                'total_expense'         => $expenseTotal,
                'net_total'             => ($snapshotIncomeTotal + $extraIncomeTotal) - $expenseTotal,
                'snapshot_count'        => $snapshotPayments->count(),
                'extra_income_count'    => $extraIncomes->count(),
                'expense_count'         => $expenses->count(),
                'total_records'         => $snapshotPayments->count() + $extraIncomes->count() + $expenses->count(),
            ];

            $pdf = Pdf::loadView('admin.institute_report.pdf', [
                'filters' => $filters,
                'summary' => $summary,
                'snapshotPayments' => $snapshotPayments,
                'extraIncomes' => $extraIncomes,
                'expenses' => $expenses,
            ])->setPaper('a4', 'portrait');

            $fileName = 'institute-report-'
                . ($filters['start_date'] ?? now()->format('Y-m-d'))
                . '-to-'
                . ($filters['end_date'] ?? now()->format('Y-m-d'))
                . '.pdf';

            return $pdf->download($fileName);
        } catch (\Throwable $e) {
            Log::error('Error generating PDF report: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to generate PDF report'
            ], 500);
        }
    }

    private function getFilters(Request $request): array
    {
        $period = $request->input('period', 'custom');

        $startDate = null;
        $endDate = null;

        if ($period === 'daily') {
            $date = $request->input('date', now()->toDateString());
            $startDate = Carbon::parse($date)->startOfDay()->toDateString();
            $endDate = Carbon::parse($date)->endOfDay()->toDateString();
        } elseif ($period === 'monthly') {
            $month = $request->input('month', now()->format('Y-m'));
            $startDate = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
            $endDate = Carbon::parse($month . '-01')->endOfMonth()->toDateString();
        } else {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        return [
            'period' => $period,
            'date' => $request->input('date'),
            'month' => $request->input('month'),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function buildSnapshotQuery(array $filters)
    {
        $query = PaymentSplitSnapshot::query();

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('payment_date', [
                $filters['start_date'],
                $filters['end_date'],
            ]);
        }

        return $query;
    }

    private function buildExtraIncomeQuery(array $filters)
    {
        $query = ExtraIncome::query()
            ->where('status', 'received');

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('income_date', [
                $filters['start_date'],
                $filters['end_date'],
            ]);
        }

        return $query;
    }

    private function buildExpenseQuery(array $filters)
    {
        $query = InstitutePayment::query()
            ->where('payment_type', 'expense')
            ->where('status', 'paid');

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('payment_date', [
                $filters['start_date'],
                $filters['end_date'],
            ]);
        }

        return $query;
    }
}
