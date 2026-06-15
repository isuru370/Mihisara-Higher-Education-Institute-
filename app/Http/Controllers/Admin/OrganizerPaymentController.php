<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use App\Models\OrganizerPayment;
use App\Models\PaymentSplitSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrganizerPaymentController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $organizers = Organizer::query()
            ->select('id', 'code', 'name', 'mobile')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $snapshots = PaymentSplitSnapshot::query()
            ->whereHas('payment', function ($query) use ($year, $month) {
                $query->where('status', 'completed')
                    ->whereYear('paid_at', $year)
                    ->whereMonth('paid_at', $month);
            })
            ->get();

        $incomeByOrganizer = $snapshots
            ->groupBy('organizer_id')
            ->map(fn($items) => round($items->sum('organizer_amount'), 2));

        $payments = OrganizerPayment::query()
            ->where('status', 'paid')
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->get();

        $paymentsByOrganizer = $payments->groupBy('organizer_id');

        $organizerRows = $organizers->map(function ($organizer) use ($incomeByOrganizer, $paymentsByOrganizer) {
            $organizerPayments = $paymentsByOrganizer->get($organizer->id, collect());

            $totalIncome = (float) ($incomeByOrganizer[$organizer->id] ?? 0);
            $advanceAmount = (float) $organizerPayments->where('payment_type', 'advance')->sum('amount');
            $deductionAmount = (float) $organizerPayments->where('payment_type', 'deduction')->sum('amount');
            $otherAmount = (float) $organizerPayments->where('payment_type', 'other')->sum('amount');
            $salaryPaid = (float) $organizerPayments->where('payment_type', 'salary')->sum('amount');

            $netTotal = max(
                $totalIncome - $advanceAmount - $deductionAmount - $otherAmount - $salaryPaid,
                0
            );

            return [
                'organizer_id' => $organizer->id,
                'code' => $organizer->code,
                'name' => $organizer->name,
                'mobile' => $organizer->mobile,
                'total_income' => round($totalIncome, 2),
                'advance_amount' => round($advanceAmount, 2),
                'deduction_amount' => round($deductionAmount, 2),
                'other_amount' => round($otherAmount, 2),
                'salary_paid' => round($salaryPaid, 2),
                'net_total' => round($netTotal, 2),
                'is_salary_paid' => $salaryPaid > 0,
            ];
        });

        return view('admin.organizer-payments.index', [
            'year' => $year,
            'month' => $month,
            'organizerRows' => $organizerRows,
            'summary' => [
                'total_income' => round($organizerRows->sum('total_income'), 2),
                'advance_amount' => round($organizerRows->sum('advance_amount'), 2),
                'deduction_amount' => round($organizerRows->sum('deduction_amount'), 2),
                'other_amount' => round($organizerRows->sum('other_amount'), 2),
                'salary_paid' => round($organizerRows->sum('salary_paid'), 2),
                'net_total' => round($organizerRows->sum('net_total'), 2),
            ],
        ]);
    }

    public function pay(Request $request, Organizer $organizer)
    {
        $year = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $data = $this->calculateOrganizerSalary($organizer, $year, $month);

        return view('admin.organizer-payments.pay', array_merge($data, [
            'organizer' => $organizer,
            'year' => $year,
            'month' => $month,
        ]));
    }

    public function store(Request $request, Organizer $organizer)
    {
        $validated = $request->validate([
            'payment_type' => ['required', 'in:salary'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        $data = $this->calculateOrganizerSalary($organizer, $year, $month);

        if ($data['salaryRecord']) {
            return redirect()
                ->back()
                ->with('error', 'Organizer salary already paid.');
        }

        if ($data['netTotal'] <= 0) {
            return redirect()
                ->back()
                ->with('error', 'No payable salary balance.');
        }

        DB::transaction(function () use ($organizer, $data) {
            OrganizerPayment::create([
                'organizer_id' => $organizer->id,
                'user_id' => auth()->id(),
                'payment_type' => 'salary',
                'amount' => round($data['netTotal'], 2),
                'payment_date' => now()->toDateString(),
                'reason_code' => null,
                'reason' => 'Organizer salary payment',
                'status' => 'paid',
                'note' => null,
            ]);
        });

        return redirect()
            ->route('admin.organizer-payments.salary-slip', [
                'organizer' => $organizer->id,
                'year' => $year,
                'month' => $month,
            ])
            ->with('success', 'Organizer salary paid successfully.');
    }

    public function storeAdjustment(Request $request, Organizer $organizer)
    {
        $validated = $request->validate([
            'payment_type' => ['required', 'in:advance,deduction,other'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:150'],
            'note' => ['nullable', 'string'],
        ]);

        $paymentDate = \Carbon\Carbon::parse($validated['payment_date']);

        $salaryPaid = OrganizerPayment::query()
            ->where('organizer_id', $organizer->id)
            ->where('payment_type', 'salary')
            ->where('status', 'paid')
            ->whereYear('payment_date', $paymentDate->year)
            ->whereMonth('payment_date', $paymentDate->month)
            ->exists();

        if ($salaryPaid) {
            return redirect()
                ->back()
                ->with('error', 'Cannot add adjustment because organizer salary already paid.');
        }

        OrganizerPayment::create([
            'organizer_id' => $organizer->id,
            'user_id' => auth()->id(),
            'payment_type' => $validated['payment_type'],
            'amount' => round((float) $validated['amount'], 2),
            'payment_date' => $validated['payment_date'],
            'reason_code' => null,
            'reason' => $validated['reason'] ?? null,
            'status' => 'paid',
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()
            ->back()
            ->with('success', ucfirst($validated['payment_type']) . ' saved successfully.');
    }

    public function destroy(OrganizerPayment $organizerPayment)
    {
        try {
            $paymentDate = $organizerPayment->payment_date;

            if (
                $paymentDate->year !== now()->year ||
                $paymentDate->month !== now()->month
            ) {
                return redirect()
                    ->back()
                    ->with('error', 'Only current month payments can be deleted.');
            }

            $salaryPaid = OrganizerPayment::query()
                ->where('organizer_id', $organizerPayment->organizer_id)
                ->where('payment_type', 'salary')
                ->where('status', 'paid')
                ->whereYear('payment_date', $paymentDate->year)
                ->whereMonth('payment_date', $paymentDate->month)
                ->exists();

            if ($salaryPaid && $organizerPayment->payment_type !== 'salary') {
                return redirect()
                    ->back()
                    ->with('error', 'Cannot delete this payment because organizer salary is already paid.');
            }

            $organizerPayment->delete();

            return redirect()
                ->back()
                ->with('success', 'Organizer payment deleted successfully.');
        } catch (Throwable $e) {
            Log::error('Organizer payment delete failed', [
                'payment_id' => $organizerPayment->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to delete organizer payment.');
        }
    }

    private function calculateOrganizerSalary(Organizer $organizer, int $year, int $month): array
    {
        $totalIncome = PaymentSplitSnapshot::query()
            ->where('organizer_id', $organizer->id)
            ->whereHas('payment', function ($query) use ($year, $month) {
                $query->where('status', 'completed')
                    ->whereYear('paid_at', $year)
                    ->whereMonth('paid_at', $month);
            })
            ->sum('organizer_amount');

        $payments = OrganizerPayment::query()
            ->where('organizer_id', $organizer->id)
            ->where('status', 'paid')
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->get();

        $advanceAmount = (float) $payments->where('payment_type', 'advance')->sum('amount');
        $deductionAmount = (float) $payments->where('payment_type', 'deduction')->sum('amount');
        $otherAmount = (float) $payments->where('payment_type', 'other')->sum('amount');
        $salaryPaid = (float) $payments->where('payment_type', 'salary')->sum('amount');
        $salaryRecord = $payments->where('payment_type', 'salary')->first();

        $netTotal = max(
            $totalIncome - $advanceAmount - $deductionAmount - $otherAmount - $salaryPaid,
            0
        );

        return [
            'totalIncome' => round($totalIncome, 2),
            'advanceAmount' => round($advanceAmount, 2),
            'deductionAmount' => round($deductionAmount, 2),
            'otherAmount' => round($otherAmount, 2),
            'salaryPaid' => round($salaryPaid, 2),
            'netTotal' => round($netTotal, 2),
            'salaryRecord' => $salaryRecord,
        ];
    }

    public function salarySlip(Request $request, Organizer $organizer)
    {
        $year = (int) ($request->year ?? now()->year);
        $month = (int) ($request->month ?? now()->month);

        $data = $this->calculateOrganizerSalary($organizer, $year, $month);

        if (! $data['salaryRecord']) {
            return redirect()
                ->back()
                ->with('error', 'Salary payment record not found.');
        }

        return view('admin.organizer-payments.salary-slip', array_merge($data, [
            'organizer' => $organizer,
            'year' => $year,
            'month' => $month,
        ]));
    }
}
