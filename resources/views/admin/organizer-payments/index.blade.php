@extends('layouts.app')

@section('title', 'Organizer Payments')
@section('page-title', 'Organizer Payments')

@section('content')

    @php
        $selectedYear = request('year', $year);
        $selectedMonth = request('month', $month);

        $totalIncome = $summary['total_income'] ?? 0;
        $advanceAmount = $summary['advance_amount'] ?? 0;
        $deductionAmount = $summary['deduction_amount'] ?? 0;
        $otherAmount = $summary['other_amount'] ?? 0;
        $salaryPaid = $summary['salary_paid'] ?? 0;
        $netTotal = $summary['net_total'] ?? 0;
    @endphp

    <div class="organizer-payments-page">

        <!-- TOOLBAR -->
        <div class="page-toolbar mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1 fw-bold">Organizer Payments</h4>
                    <div class="text-muted">
                        Manage organizer income, advances, deductions and salary payments
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.organizer-payments.index') }}"
                    class="d-flex flex-wrap gap-2 align-items-center">

                    <input type="number" name="year" value="{{ $selectedYear }}"
                        class="form-control custom-input filter-input" placeholder="Year" min="2000" max="2100">

                    <input type="number" name="month" value="{{ $selectedMonth }}"
                        class="form-control custom-input filter-input" placeholder="Month" min="1" max="12">

                    <button type="submit" class="btn btn-primary custom-btn">
                        <i class="bi bi-funnel me-1"></i>
                        Filter
                    </button>

                    <a href="{{ route('admin.organizer-payments.index') }}" class="btn btn-outline-secondary custom-btn">
                        Reset
                    </a>

                    <button type="button" onclick="window.print()" class="btn btn-light border custom-btn">
                        <i class="bi bi-printer me-1"></i>
                        Print
                    </button>

                </form>
            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-2 col-md-4 col-6">
                <div class="summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-cash-stack summary-icon"></i>
                        <h6>Total Income</h6>
                        <h3>{{ number_format($totalIncome, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <div class="summary-card summary-red">
                    <div class="card-body">
                        <i class="bi bi-arrow-down-circle summary-icon"></i>
                        <h6>Advance</h6>
                        <h3>{{ number_format($advanceAmount, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <div class="summary-card summary-orange">
                    <div class="card-body">
                        <i class="bi bi-dash-circle summary-icon"></i>
                        <h6>Deduction</h6>
                        <h3>{{ number_format($deductionAmount, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <div class="summary-card summary-gold">
                    <div class="card-body">
                        <i class="bi bi-three-dots summary-icon"></i>
                        <h6>Other</h6>
                        <h3>{{ number_format($otherAmount, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <div class="summary-card summary-purple">
                    <div class="card-body">
                        <i class="bi bi-wallet2 summary-icon"></i>
                        <h6>Salary Paid</h6>
                        <h3>{{ number_format($salaryPaid, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-6">
                <div class="summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-check2-circle summary-icon"></i>
                        <h6>Net Total</h6>
                        <h3>{{ number_format($netTotal, 2) }}</h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <div class="main-card-header">
                <div>
                    <h4>Monthly Organizer Payment Summary</h4>
                    <p>
                        Showing records for
                        {{ $selectedMonth }} / {{ $selectedYear }}
                    </p>
                </div>

                <div class="header-badge">
                    {{ count($organizerRows) }} Organizers
                </div>
            </div>

            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th class="text-end">Total Income</th>
                            <th class="text-end">Advance</th>
                            <th class="text-end">Deduction</th>
                            <th class="text-end">Other</th>
                            <th class="text-end">Salary Paid</th>
                            <th class="text-end">Net Total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($organizerRows as $row)
                            <tr>
                                <td>
                                    <span class="fw-semibold">
                                        {{ $row['organizer_id'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border custom-badge">
                                        {{ $row['code'] }}
                                    </span>
                                </td>

                                <td>
                                    <div class="fw-bold">
                                        {{ $row['name'] }}
                                    </div>

                                    @if(!empty($row['mobile']))
                                        <small class="text-muted">
                                            {{ $row['mobile'] }}
                                        </small>
                                    @endif
                                </td>

                                <td class="text-end">
                                    {{ number_format($row['total_income'], 2) }}
                                </td>

                                <td class="text-end text-danger">
                                    {{ number_format($row['advance_amount'], 2) }}
                                </td>

                                <td class="text-end text-danger">
                                    {{ number_format($row['deduction_amount'], 2) }}
                                </td>

                                <td class="text-end text-warning">
                                    {{ number_format($row['other_amount'], 2) }}
                                </td>

                                <td class="text-end text-primary">
                                    {{ number_format($row['salary_paid'], 2) }}
                                </td>

                                <td class="text-end fw-bold text-success">
                                    {{ number_format($row['net_total'], 2) }}
                                </td>

                                <td class="text-center">
                                    <div class="action-buttons">
                                        @if(!$row['is_salary_paid'])
                                                                    <button type="button" class="action-btn add-btn" data-bs-toggle="modal"
                                                                        data-bs-target="#adjustmentModal{{ $row['organizer_id'] }}" title="Add Payment">
                                                                        <i class="bi bi-plus-circle-fill"></i>
                                                                    </button>

                                                                    <a href="{{ route('admin.organizer-payments.pay', [
                                                'organizer' => $row['organizer_id'],
                                                'year' => $selectedYear,
                                                'month' => $selectedMonth,
                                            ]) }}" class="action-btn pay-btn" title="Pay Salary">
                                                                        <i class="bi bi-cash-coin"></i>
                                                                    </a>
                                        @else
                                                                    <a href="{{ route('admin.organizer-payments.salary-slip', [
                                                'organizer' => $row['organizer_id'],
                                                'year' => $selectedYear,
                                                'month' => $selectedMonth,
                                            ]) }}" target="_blank" class="action-btn print-btn"
                                                                        title="Print Salary Slip">
                                                                        <i class="bi bi-printer-fill"></i>
                                                                    </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-receipt"></i>
                                        <h5>No organizer payment records found</h5>
                                        <p>Try changing the year or month filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        @foreach($organizerRows as $row)
            @if(!$row['is_salary_paid'])
                <div class="modal fade" id="adjustmentModal{{ $row['organizer_id'] }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <form method="POST"
                                action="{{ route('admin.organizer-payments.adjustment-store', $row['organizer_id']) }}">
                                @csrf

                                <div class="modal-header border-0">
                                    <div>
                                        <h5 class="modal-title fw-bold">
                                            Add Organizer Payment
                                        </h5>
                                        <small class="text-muted">
                                            {{ $row['name'] }} · {{ $row['code'] }}
                                        </small>
                                    </div>

                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body pt-0">

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Payment Type</label>
                                        <select name="payment_type" class="form-select custom-input" required>
                                            <option value="advance">Advance</option>
                                            <option value="deduction">Deduction</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Amount</label>
                                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control custom-input"
                                            required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Payment Date</label>
                                        <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}"
                                            class="form-control custom-input" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Reason</label>
                                        <input type="text" name="reason" class="form-control custom-input" maxlength="150">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Note</label>
                                        <textarea name="note" class="form-control custom-input" rows="3"></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-outline-secondary custom-btn" data-bs-dismiss="modal">
                                        Cancel
                                    </button>

                                    <button type="submit" class="btn btn-success custom-btn">
                                        Save
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

    </div>

@endsection

@push('styles')
    <style>
        .organizer-payments-page {
            animation: fadeIn .4s ease;
        }

        .page-toolbar,
        .main-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
        }

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
            box-shadow: none;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .filter-input {
            width: 120px;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            border: none;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .summary-card {
            border-radius: 24px;
            overflow: hidden;
            color: #fff;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            border: none;
            min-height: 140px;
        }

        .summary-card .card-body {
            padding: 1.25rem;
            position: relative;
        }

        .summary-card h6 {
            opacity: .9;
            margin-bottom: .5rem;
            font-size: .9rem;
        }

        .summary-card h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            word-break: break-word;
        }

        .summary-icon {
            position: absolute;
            right: 16px;
            top: 16px;
            font-size: 2rem;
            opacity: .18;
        }

        .summary-blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .summary-orange {
            background: linear-gradient(135deg, #ea580c, #f97316);
        }

        .summary-gold {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .summary-purple {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
        }

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
        }

        .header-badge {
            display: inline-flex;
            align-items: center;
            padding: .5rem .85rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: .85rem;
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
            vertical-align: middle;
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: .2s ease;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .05);
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .add-btn {
            background: #fef3c7;
            color: #d97706;
        }

        .pay-btn {
            background: #dcfce7;
            color: #16a34a;
        }

        .print-btn {
            background: #e2e8f0;
            color: #0f172a;
        }

        .empty-state {
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
        }

        @media (max-width: 768px) {

            .page-toolbar,
            .main-card {
                padding: 1rem;
            }

            .page-toolbar form {
                width: 100%;
            }

            .filter-input {
                width: 100%;
            }

            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media print {

            .btn,
            .badge,
            .sidebar,
            .navbar,
            .modal {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .page-toolbar,
            .main-card,
            .summary-card {
                box-shadow: none !important;
            }
        }
    </style>
@endpush