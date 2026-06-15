@extends('layouts.app')

@section('title', 'Extra Incomes')
@section('page-title', 'Extra Incomes')

@section('content')

    @php
        $reportYear = $selectedYear ?? now()->year;
        $reportMonth = $selectedMonth ?? 'all';
        $monthLabel = $reportMonth === 'all'
            ? 'All Months'
            : \Carbon\Carbon::create()->month((int) $reportMonth)->format('F');

        $statusLabel = request('status') ? ucfirst(request('status')) : 'All Status';
        $searchLabel = request('search') ? request('search') : 'All Records';
    @endphp

    <div class="extra-income-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <h3 class="fw-bold mb-1">Extra Incomes</h3>
                    <p class="text-muted mb-0">
                        Manage extra income records for {{ $reportYear }} · {{ $monthLabel }}
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.extra-incomes.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg me-1"></i>
                        Add Income
                    </a>

                    <button type="button" onclick="window.print()" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-printer me-1"></i>
                        Print
                    </button>

                    <button type="button" class="btn btn-outline-success custom-btn" disabled>
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        Export Excel
                    </button>

                    <button type="button" class="btn btn-outline-danger custom-btn" disabled>
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        Export PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <div class="filter-card mb-4">
            <form method="GET" action="{{ route('admin.extra-incomes.index') }}" class="row g-3 align-items-end">

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Year</label>
                    <select name="year" class="form-select custom-input">
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $reportYear == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Month</label>
                    <select name="month" class="form-select custom-input">
                        <option value="all" {{ $reportMonth == 'all' ? 'selected' : '' }}>All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (string) $reportMonth === (string) $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select custom-input">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control custom-input"
                        placeholder="Reason / Note">
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button class="btn btn-dark custom-btn" type="submit">
                        <i class="bi bi-funnel me-1"></i>
                        Filter
                    </button>

                    <a href="{{ route('admin.extra-incomes.index') }}" class="btn btn-outline-secondary custom-btn">
                        Reset
                    </a>
                </div>

            </form>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-4 col-md-6">
                <div class="summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-cash-stack summary-icon"></i>
                        <h6>Total Income</h6>
                        <h3>Rs. {{ number_format($totalIncome, 2) }}</h3>
                        <small class="summary-sub">{{ $searchLabel }}</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-check-circle-fill summary-icon"></i>
                        <h6>Received</h6>
                        <h3 class="text-white">Rs. {{ number_format($receivedIncome, 2) }}</h3>
                        <small class="summary-sub">Approved and received income</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-12">
                <div class="summary-card summary-warning">
                    <div class="card-body">
                        <i class="bi bi-clock-history summary-icon"></i>
                        <h6>Pending</h6>
                        <h3>Rs. {{ number_format($pendingIncome, 2) }}</h3>
                        <small class="summary-sub">Not yet received</small>
                    </div>
                </div>
            </div>

        </div>

        <!-- TABLE -->
        <div class="main-card">

            <div class="main-card-header">
                <div>
                    <h4 class="mb-1 fw-bold">Income Records</h4>
                    <p class="mb-0 text-muted">
                        Showing filtered extra income entries
                    </p>
                </div>

                <div class="header-badge">
                    {{ $extraIncomes->total() }} Records
                </div>
            </div>

            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center" width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($extraIncomes as $income)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $income->income_date->format('Y-m-d') }}
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $income->reason ?? '-' }}
                                    </div>
                                    @if(!empty($income->note))
                                        <small class="text-muted">
                                            {{ \Illuminate\Support\Str::limit($income->note, 60) }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-info custom-badge">
                                        {{ ucfirst($income->income_type) }}
                                    </span>
                                </td>

                                <td>
                                    @if($income->status == 'received')
                                        <span class="badge bg-success custom-badge">Received</span>
                                    @elseif($income->status == 'pending')
                                        <span class="badge bg-warning text-dark custom-badge">Pending</span>
                                    @elseif($income->status == 'approved')
                                        <span class="badge bg-primary custom-badge">Approved</span>
                                    @else
                                        <span class="badge bg-danger custom-badge">Cancelled</span>
                                    @endif
                                </td>

                                <td class="text-end fw-bold">
                                    Rs. {{ number_format($income->amount, 2) }}
                                </td>

                                <td class="text-center">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.extra-incomes.show', $income) }}"
                                            class="btn btn-sm btn-outline-info action-btn" title="View">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        <a href="{{ route('admin.extra-incomes.edit', $income) }}"
                                            class="btn btn-sm btn-outline-warning action-btn" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-secondary action-btn" disabled
                                            title="Future Feature">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h5>No records found</h5>
                                        <p class="mb-0">Try changing the filters or add a new extra income record.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $extraIncomes->withQueryString()->links() }}
            </div>

        </div>

    </div>
@endsection

@push('styles')
    <style>
        .extra-income-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .filter-card,
        .main-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            padding: 1.35rem 1.5rem;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-actions {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover:not(:disabled) {
            transform: translateY(-2px);
        }

        .custom-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
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

        .summary-card {
            border-radius: 24px;
            overflow: hidden;
            color: #fff;
            position: relative;
            min-height: 145px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            border: none;
        }

        .summary-card .card-body {
            padding: 1.35rem;
            position: relative;
        }

        .summary-card h6 {
            opacity: .9;
            margin-bottom: .6rem;
        }

        .summary-card h3 {
            margin: 0;
            font-weight: 800;
        }

        .summary-sub {
            opacity: .85;
            font-size: .8rem;
        }

        .summary-icon {
            position: absolute;
            right: 18px;
            top: 18px;
            font-size: 2rem;
            opacity: .18;
        }

        .summary-blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .summary-warning {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #111827;
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
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
            padding: .55rem .9rem;
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
            white-space: nowrap;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .45rem .65rem;
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
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .05);
        }

        .action-btn:hover:not(:disabled) {
            transform: translateY(-2px);
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

            .hero-content,
            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions {
                width: 100%;
            }

            .hero-actions .btn {
                flex: 1;
            }
        }

        @media print {

            .btn,
            .alert,
            .sidebar,
            .navbar,
            .pagination {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .hero-card,
            .filter-card,
            .main-card,
            .summary-card {
                box-shadow: none !important;
            }
        }
    </style>
@endpush