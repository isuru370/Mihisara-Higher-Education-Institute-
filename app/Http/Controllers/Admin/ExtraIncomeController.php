<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtraIncome;
use App\Services\ReceiptNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExtraIncomeController extends Controller
{
    /**
     * Display a listing of extra incomes.
     */
    public function index(Request $request)
    {
        // 📅 filters
        $selectedYear = $request->year ?? now()->year;
        $selectedMonth = $request->month ?? now()->month;

        $query = ExtraIncome::with('createdBy');

        // 🔥 year filter
        $query->whereYear('income_date', $selectedYear);

        // 🔥 month filter
        if ($selectedMonth !== 'all') {
            $query->whereMonth('income_date', $selectedMonth);
        }

        // 🔍 status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🔍 income type filter
        if ($request->filled('income_type')) {
            $query->where('income_type', $request->income_type);
        }

        // 🔍 search
        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('reason', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        // 📊 statistics
        $totalIncome = (clone $query)->sum('amount');

        $receivedIncome = (clone $query)
            ->where('status', 'received')
            ->sum('amount');

        $pendingIncome = (clone $query)
            ->where('status', 'pending')
            ->sum('amount');

        // 📃 records
        $extraIncomes = $query
            ->latest('income_date')
            ->latest('id')
            ->paginate(20)
            ->appends($request->query());

        // 📅 years dropdown
        $years = ExtraIncome::selectRaw('YEAR(income_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('admin.extra-incomes.index', compact(
            'extraIncomes',
            'years',
            'selectedYear',
            'selectedMonth',
            'totalIncome',
            'receivedIncome',
            'pendingIncome'
        ));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.extra-incomes.create');
    }

    /**
     * Store new extra income.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'income_date' => [
                'required',
                'date'
            ],

            'reason' => [
                'nullable',
                'string',
                'max:150'
            ],

            'income_type' => [
                'required',
                Rule::in([
                    'hall_rent',
                    'extra',
                    'refund',
                    'other'
                ])
            ],

            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'approved',
                    'received',
                    'cancelled'
                ])
            ],

            'note' => [
                'nullable',
                'string'
            ],
        ]);

        DB::beginTransaction();

        try {

            $validated['user_id'] = auth()->id();

            $validated['receipt_number'] =
                ReceiptNumberService::generate();

            $extraIncome = ExtraIncome::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.extra-incomes.show', $extraIncome)
                ->with('success', 'Extra income created successfully.');
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to create extra income.');
        }
    }

    /**
     * Display single record.
     */
    public function show(ExtraIncome $extraIncome)
    {
        $extraIncome->load('createdBy');

        return view('admin.extra-incomes.show', compact(
            'extraIncome'
        ));
    }

    /**
     * Show edit form.
     */
    public function edit(ExtraIncome $extraIncome)
    {
        return view('admin.extra-incomes.edit', compact(
            'extraIncome'
        ));
    }

    /**
     * Update existing record.
     */
    public function update(Request $request, ExtraIncome $extraIncome)
    {
        $validated = $request->validate([

            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],

            'income_date' => [
                'required',
                'date'
            ],

            'reason' => [
                'nullable',
                'string',
                'max:150'
            ],

            'income_type' => [
                'required',
                Rule::in([
                    'hall_rent',
                    'extra',
                    'refund',
                    'other'
                ])
            ],

            'status' => [
                'required',
                Rule::in([
                    'pending',
                    'approved',
                    'received',
                    'cancelled'
                ])
            ],

            'note' => [
                'nullable',
                'string'
            ],
        ]);

        DB::beginTransaction();

        try {

            $extraIncome->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.extra-incomes.index')
                ->with('success', 'Extra income updated successfully.');
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to update extra income.');
        }
    }

    /**
     * Soft delete record.
     */
    public function destroy(ExtraIncome $extraIncome)
    {
        DB::beginTransaction();

        try {

            $extraIncome->delete();

            DB::commit();

            return redirect()
                ->route('admin.extra-incomes.index')
                ->with('success', 'Extra income deleted successfully.');
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->with('error', 'Failed to delete extra income.');
        }
    }
}
