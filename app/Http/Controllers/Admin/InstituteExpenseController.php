<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstitutePayment;
use App\Models\PaymentReason;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InstituteExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = InstitutePayment::with(['user'])
            ->where('payment_type', 'expense'); // Only fetch expenses

        // Search by reason or note
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhere('reason_code', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        // Filter by month and year
        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('payment_date', $request->month)
                ->whereYear('payment_date', $request->year);
        } elseif ($request->filled('month')) {
            $query->whereMonth('payment_date', $request->month);
        } elseif ($request->filled('year')) {
            $query->whereYear('payment_date', $request->year);
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // REMOVED: Filter by reason code

        // Filter by recorded user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by current month only
        if ($request->boolean('current_month')) {
            $query->whereMonth('payment_date', Carbon::now()->month)
                ->whereYear('payment_date', Carbon::now()->year);
        }

        // Sorting
        $sortField = $request->get('sort', 'payment_date');
        $sortOrder = $request->get('order', 'desc');

        $allowedSorts = ['payment_date', 'amount', 'created_at', 'id'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->latest('payment_date')->latest('id');
        }

        $expenses = $query->paginate(15)->appends($request->query());

        // Get users who recorded expenses
        $users = InstitutePayment::where('payment_type', 'expense')
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id')
            ->filter();

        return view('admin.institute-expenses.index', compact('expenses', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:150'],
            'reason_code' => ['nullable', 'string', 'max:50', 'exists:payment_reasons,reason_code'],
        ]);

        $note = $this->generateNote(
            amount: $validated['amount'],
            paymentDate: $validated['payment_date'],
            reason: $validated['reason'] ?? null
        );

        InstitutePayment::create([
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reason' => $validated['reason'] ?? null,
            'reason_code' => $validated['reason_code'] ?? null,
            'payment_type' => 'expense',
            'status' => 'paid',
            'user_id' => auth()->id(),
            'note' => $note,
        ]);

        return redirect()
            ->route('admin.institute-expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function edit(InstitutePayment $instituteExpense)
    {
        if (! $this->canModify($instituteExpense)) {
            return back()->with('error', 'You can edit only current month records.');
        }

        $reasons = PaymentReason::orderBy('reason')->get();

        return view('admin.institute-expenses.edit', compact('instituteExpense', 'reasons'));
    }

    public function update(Request $request, InstitutePayment $instituteExpense)
    {
        if (! $this->canModify($instituteExpense)) {
            return back()->with('error', 'You can update only current month records.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:150'],
            'reason_code' => ['nullable', 'string', 'max:50', 'exists:payment_reasons,reason_code'],
        ]);

        $note = $this->generateNote(
            amount: $validated['amount'],
            paymentDate: $validated['payment_date'],
            reason: $validated['reason'] ?? null
        );

        $instituteExpense->update([
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reason' => $validated['reason'] ?? null,
            'reason_code' => $validated['reason_code'] ?? null,
            'payment_type' => 'expense',
            'status' => 'paid',
            'user_id' => auth()->id(),
            'note' => $note,
        ]);

        return redirect()
            ->route('admin.institute-expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(InstitutePayment $instituteExpense)
    {
        if (! $this->canModify($instituteExpense)) {
            return back()->with('error', 'You can delete only current month records.');
        }

        $instituteExpense->delete();

        return redirect()
            ->route('admin.institute-expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    private function canModify(InstitutePayment $expense): bool
    {
        $paymentDate = Carbon::parse($expense->payment_date)->startOfDay();
        $now = Carbon::now();

        $currentMonthStart = $now->copy()->startOfMonth()->startOfDay();
        $currentMonthEnd = $now->copy()->endOfMonth()->endOfDay();

        return $paymentDate->betweenIncluded($currentMonthStart, $currentMonthEnd);
    }

    private function generateNote($amount, $paymentDate, ?string $reason = null): string
    {
        $date = Carbon::parse($paymentDate)->format('Y-m-d');

        $parts = [
            'Institute expense recorded',
            'Date: ' . $date,
            'Amount: Rs. ' . number_format((float) $amount, 2),
        ];

        if (!empty($reason)) {
            $parts[] = 'Reason: ' . $reason;
        }

        return implode(' | ', $parts);
    }
}
