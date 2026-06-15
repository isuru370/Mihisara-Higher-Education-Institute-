@extends('layouts.app')

@section('title', 'Institute Payment Report')
@section('page-title', 'Institute Payment Report')

@push('styles')
    <style>
        .institute-report-page {
            animation: fadeIn .4s ease;
        }

        /* Hero Card */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            padding: 2rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8, #1e40af);
            box-shadow: 0 20px 45px rgba(37, 99, 235, .25);
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            top: -100px;
            right: -80px;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            bottom: -60px;
            left: -40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(10px);
            font-size: .85rem;
            font-weight: 600;
        }

        .text-light-soft {
            color: rgba(255, 255, 255, .78);
        }

        /* Filter Card */
        .filter-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            margin-bottom: 1.5rem;
        }

        .filter-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
        }

        .filter-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-title i {
            color: #2563eb;
        }

        .filter-body {
            padding: 1.5rem;
        }

        .filter-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: block;
        }

        .filter-input, .form-select-custom {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .filter-input:focus, .form-select-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .btn-pdf {
            background: #dc2626;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-pdf:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .btn-excel {
            background: #059669;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-excel:hover {
            background: #047857;
            transform: translateY(-2px);
        }

        /* Stats Cards */
        .stats-row {
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid #eef2f7;
            transition: all 0.2s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .05);
            right: -20px;
            bottom: -20px;
        }

        .stat-card:hover {
            border-color: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
        }

        .stat-icon.income { background: linear-gradient(135deg, #10b981, #34d399); color: white; }
        .stat-icon.expense { background: linear-gradient(135deg, #ef4444, #f87171); color: white; }
        .stat-icon.net { background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; }
        .stat-icon.records { background: linear-gradient(135deg, #8b5cf6, #a78bfa); color: white; }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0;
        }

        .stat-value.text-success { color: #10b981; }
        .stat-value.text-danger { color: #ef4444; }
        .stat-value.text-primary { color: #2563eb; }

        /* Table Card */
        .table-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
            margin-top: 1.5rem;
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
        }

        .table-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-title i {
            color: #2563eb;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            background: #f8fafc;
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }

        .data-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .amount-text {
            font-weight: 700;
            font-family: monospace;
            text-align: right;
        }

        .amount-income {
            color: #10b981;
        }

        .amount-expense {
            color: #ef4444;
        }

        .badge-income {
            background: #dcfce7;
            color: #166534;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-expense {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-paid {
            background: #dcfce7;
            color: #166534;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-received {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }
            .stat-value {
                font-size: 1.1rem;
            }
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            .data-table thead th {
                font-size: 0.65rem;
                padding: 0.5rem;
            }
            .data-table tbody td {
                padding: 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="institute-report-page">

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="hero-badge mb-3">
                        <i class="bi bi-graph-up"></i>
                        Financial Reports
                    </div>
                    <h2 class="fw-bold mb-2">Institute Payment Report</h2>
                    <p class="mb-0 text-light-soft">
                        Track income, expenses, and payment splits across the institution
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-calendar-event-fill"></i>
                        {{ now()->format('d M Y') }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER CARD --}}
        <div class="filter-card">
            <div class="filter-header">
                <h5 class="filter-title">
                    <i class="bi bi-funnel-fill"></i> Report Filters
                </h5>
            </div>
            <div class="filter-body">
                <form method="GET" action="{{ route('admin.institute-reports.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="filter-label">Report Type</label>
                            <select name="period" id="period" class="form-select-custom">
                                <option value="daily" {{ request('period') === 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="monthly" {{ request('period') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="custom" {{ request('period', 'custom') === 'custom' ? 'selected' : '' }}>Date Range</option>
                            </select>
                        </div>

                        <div class="col-md-2 filter-group daily-group d-none">
                            <label class="filter-label">Date</label>
                            <input type="date" name="date" id="date" class="filter-input"
                                value="{{ request('date', now()->toDateString()) }}">
                        </div>

                        <div class="col-md-2 filter-group monthly-group d-none">
                            <label class="filter-label">Month</label>
                            <input type="month" name="month" id="month" class="filter-input"
                                value="{{ request('month', now()->format('Y-m')) }}">
                        </div>

                        <div class="col-md-2 filter-group custom-group d-none">
                            <label class="filter-label">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="filter-input"
                                value="{{ request('start_date') }}">
                        </div>

                        <div class="col-md-2 filter-group custom-group d-none">
                            <label class="filter-label">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="filter-input"
                                value="{{ request('end_date') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="filter-label">Payment Type</label>
                            <select name="payment_type" class="form-select-custom">
                                <option value="">All</option>
                                <option value="income" {{ request('payment_type') === 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ request('payment_type') === 'expense' ? 'selected' : '' }}>Expense</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="filter-label">Status</label>
                            <select name="status" class="form-select-custom">
                                <option value="">All</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn-primary-custom">
                                    <i class="bi bi-search"></i> Generate Report
                                </button>
                                <a href="{{ route('admin.institute-reports.pdf', request()->all()) }}" class="btn-pdf">
                                    <i class="bi bi-filetype-pdf"></i> Download PDF
                                </a>
                                <a href="{{ route('admin.institute-reports.excel', request()->all()) }}" class="btn-excel">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Download Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="stats-row">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon income">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="stat-label">Total Income</div>
                        <div class="stat-value text-success">
                            Rs. {{ number_format($summary['total_income'] ?? 0, 2) }}
                        </div>
                        <small class="text-muted">All revenue sources</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon expense">
                            <i class="bi bi-graph-down-arrow"></i>
                        </div>
                        <div class="stat-label">Total Expense</div>
                        <div class="stat-value text-danger">
                            Rs. {{ number_format($summary['total_expense'] ?? 0, 2) }}
                        </div>
                        <small class="text-muted">All expenditures</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon net">
                            <i class="bi bi-calculator-fill"></i>
                        </div>
                        <div class="stat-label">Net Total</div>
                        <div class="stat-value text-primary">
                            Rs. {{ number_format($summary['net_total'] ?? 0, 2) }}
                        </div>
                        <small class="text-muted">Income - Expenses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon records">
                            <i class="bi bi-list-ul"></i>
                        </div>
                        <div class="stat-label">Total Records</div>
                        <div class="stat-value">
                            {{ $summary['total_records'] ?? 0 }}
                        </div>
                        <small class="text-muted">Transactions processed</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- PAYMENT SPLIT SNAPSHOTS TABLE --}}
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">
                    <i class="bi bi-pie-chart-fill"></i> Payment Split Snapshots
                </h5>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Organizer</th>
                            <th>Payment Amount</th>
                            <th>Institution Amount</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($snapshotPayments as $key => $row)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row->payment_date ? \Carbon\Carbon::parse($row->payment_date)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $row->studentClass->name ?? $row->studentClass->class_name ?? '-' }}</td>
                                <td>{{ $row->teacher->name ?? $row->teacher->full_name ?? '-' }}</td>
                                <td>{{ $row->organizer->name ?? '-' }}</td>
                                <td class="amount-text amount-income">Rs. {{ number_format($row->payment_amount ?? 0, 2) }}</td>
                                <td class="amount-text">Rs. {{ number_format($row->institution_amount ?? 0, 2) }}</td>
                                <td>{{ $row->createdBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <td><td colspan="8" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Records Found</h5>
                                <p class="text-muted">No payment split snapshots available for the selected period.</p>
                             </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- EXTRA INCOMES TABLE --}}
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">
                    <i class="bi bi-cash-coin"></i> Extra Incomes
                </h5>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Created By</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($extraIncomes as $key => $income)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $income->income_date ? \Carbon\Carbon::parse($income->income_date)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $income->reason ?? '-' }}</td>
                                <td>
                                    <span class="badge-income">{{ ucfirst($income->income_type ?? '-') }}</span>
                                </td>
                                <td>
                                    <span class="badge-paid">{{ ucfirst($income->status ?? '-') }}</span>
                                </td>
                                <td class="amount-text amount-income">Rs. {{ number_format($income->amount ?? 0, 2) }}</td>
                                <td>{{ $income->createdBy->name ?? '-' }}</td>
                                <td>{{ Str::limit($income->note ?? '-', 50) }}</td>
                            </tr>
                        @empty
                            <td><td colspan="8" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Records Found</h5>
                                <p class="text-muted">No extra income records available for the selected period.</p>
                             </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- EXPENSES TABLE --}}
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">
                    <i class="bi bi-receipt"></i> Expenses
                </h5>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Created By</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $key => $expense)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $expense->payment_date ? \Carbon\Carbon::parse($expense->payment_date)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $expense->reason ?? '-' }}</td>
                                <td>
                                    @if($expense->payment_type == 'income')
                                        <span class="badge-income">Income</span>
                                    @else
                                        <span class="badge-expense">Expense</span>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->status == 'paid')
                                        <span class="badge-paid">Paid</span>
                                    @else
                                        <span class="badge-received">Received</span>
                                    @endif
                                </td>
                                <td class="amount-text @if($expense->payment_type != 'income') amount-expense @else amount-income @endif">
                                    Rs. {{ number_format($expense->amount ?? 0, 2) }}
                                </td>
                                <td>{{ $expense->createdBy->name ?? '-' }}</td>
                                <td>{{ Str::limit($expense->note ?? '-', 50) }}</td>
                            </tr>
                        @empty
                            <td><td colspan="8" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>No Records Found</h5>
                                <p class="text-muted">No expense records available for the selected period.</p>
                             </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i> 
                Report generated on {{ now()->format('d M Y, h:i A') }}
            </small>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const period = document.getElementById('period');
            const dailyGroup = document.querySelector('.daily-group');
            const monthlyGroup = document.querySelector('.monthly-group');
            const customGroups = document.querySelectorAll('.custom-group');

            const dateInput = document.getElementById('date');
            const monthInput = document.getElementById('month');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function hideAll() {
                dailyGroup?.classList.add('d-none');
                monthlyGroup?.classList.add('d-none');
                customGroups.forEach(el => el.classList.add('d-none'));

                if (dateInput) dateInput.disabled = true;
                if (monthInput) monthInput.disabled = true;
                if (startDateInput) startDateInput.disabled = true;
                if (endDateInput) endDateInput.disabled = true;
            }

            function toggleFields() {
                hideAll();

                if (period.value === 'daily') {
                    dailyGroup?.classList.remove('d-none');
                    if (dateInput) dateInput.disabled = false;
                } else if (period.value === 'monthly') {
                    monthlyGroup?.classList.remove('d-none');
                    if (monthInput) monthInput.disabled = false;
                } else {
                    customGroups.forEach(el => el.classList.remove('d-none'));
                    if (startDateInput) startDateInput.disabled = false;
                    if (endDateInput) endDateInput.disabled = false;
                }
            }

            if (period) {
                period.addEventListener('change', toggleFields);
                toggleFields();
            }
        });
    </script>
@endpush