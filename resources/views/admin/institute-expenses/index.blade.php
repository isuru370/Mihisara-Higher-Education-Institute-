@extends('layouts.app')

@section('title', 'Institute Expenses')
@section('page-title', 'Institute Expenses')

@push('styles')
    <style>
        .expenses-page {
            animation: fadeIn .4s ease;
        }

        /* Header Card */
        .header-card {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border-radius: 28px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }

        .header-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 50%;
        }

        .header-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .header-subtitle {
            color: #94a3b8;
            margin-bottom: 0;
        }

        .stat-badge {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 0.5rem 1.25rem;
            text-align: center;
        }

        .stat-badge .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.7;
        }

        .stat-badge .value {
            font-size: 1.25rem;
            font-weight: 700;
        }

        /* Filter Card */
        .filter-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .filter-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eef2f7;
        }

        .filter-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .filter-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .filter-input {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 0.8rem;
            font-size: 0.85rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .filter-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-filter {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .btn-reset {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-reset:hover {
            background: #475569;
            transform: translateY(-2px);
        }

        /* Table Card */
        .table-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .table-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .result-count {
            font-size: 0.8rem;
            color: #64748b;
        }

        .btn-view {
            background: #0ea5e9;
            border: none;
            border-radius: 8px;
            padding: 0.35rem 0.8rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-view:hover {
            background: #0284c7;
            transform: translateY(-1px);
        }

        .btn-edit {
            background: #f59e0b;
            border: none;
            border-radius: 8px;
            padding: 0.35rem 0.8rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-edit:hover {
            background: #d97706;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #ef4444;
            border: none;
            border-radius: 8px;
            padding: 0.35rem 0.8rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        .expense-table {
            width: 100%;
            border-collapse: collapse;
        }

        .expense-table thead th {
            background: #f8fafc;
            padding: 1rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .expense-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .expense-table tbody tr:hover {
            background: #f8fafc;
        }

        .amount-expense {
            color: #dc2626;
            font-weight: 700;
            font-family: monospace;
        }

        .badge-current {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .badge-past {
            background: #f1f5f9;
            color: #64748b;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .pagination-container {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid #eef2f7;
        }

        .pagination {
            margin: 0;
            gap: 0.25rem;
        }

        .pagination .page-link {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-color: #2563eb;
            color: white;
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

        @media (max-width: 768px) {
            .header-card { padding: 1.25rem; }
            .header-title { font-size: 1.25rem; }
            .expense-table thead th { font-size: 0.65rem; padding: 0.75rem; }
            .expense-table tbody td { padding: 0.75rem; font-size: 0.75rem; }
        }
    </style>
@endpush

@section('content')
    <div class="expenses-page">
        <!-- Header Card -->
        <div class="header-card">
            <div class="row align-items-center justify-content-between">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="header-title">
                        <i class="bi bi-receipt"></i> Institute Expenses
                    </div>
                    <p class="header-subtitle">View and manage all institute expense transactions</p>
                </div>
                <div class="col-md-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="stat-badge">
                                <div class="label">Total Expenses</div>
                                <div class="value">Rs. {{ number_format($expenses->sum('amount'), 2) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-badge">
                                <div class="label">Total Records</div>
                                <div class="value">{{ $expenses->total() }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <div class="filter-title"><i class="bi bi-funnel"></i> Filter Expenses</div>
            <form method="GET" action="{{ route('admin.institute-expenses.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="filter-label">Search</div>
                        <input type="text" name="search" class="filter-input" placeholder="Search by reason, note..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="filter-label">From Date</div>
                        <input type="date" name="from_date" class="filter-input" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="filter-label">To Date</div>
                        <input type="date" name="to_date" class="filter-input" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3">
                        <div class="filter-label">User</div>
                        <select name="user_id" class="filter-input">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="filter-label">Min Amount</div>
                        <input type="number" name="min_amount" class="filter-input" placeholder="Min" value="{{ request('min_amount') }}">
                    </div>
                    <div class="col-md-2">
                        <div class="filter-label">Max Amount</div>
                        <input type="number" name="max_amount" class="filter-input" placeholder="Max" value="{{ request('max_amount') }}">
                    </div>
                    <div class="col-md-2">
                        <div class="filter-label">Month</div>
                        <select name="month" class="filter-input">
                            <option value="">All Months</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="filter-label">Year</div>
                        <select name="year" class="filter-input">
                            <option value="">All Years</option>
                            @for($y = 2020; $y <= now()->year; $y++)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="filter-label">Current Month</div>
                        <select name="current_month" class="filter-input">
                            <option value="">No</option>
                            <option value="1" {{ request('current_month') ? 'selected' : '' }}>Yes (Current Month Only)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="filter-label">&nbsp;</div>
                        <button type="submit" class="btn-filter"><i class="bi bi-search"></i> Filter</button>
                    </div>
                    <div class="col-md-2">
                        <div class="filter-label">&nbsp;</div>
                        <a href="{{ route('admin.institute-expenses.index') }}" class="btn-reset d-block text-center"><i class="bi bi-x-circle"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title"><i class="bi bi-cash-stack"></i> Expense Transactions</h5>
                <div class="result-count">Showing {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} of {{ $expenses->total() }} records</div>
            </div>

            <div class="table-responsive">
                <table class="expense-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Reason Code</th>
                            <th>Recorded By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            @php
                                $paymentDate = Carbon\Carbon::parse($expense->payment_date);
                                $isCurrentMonth = $paymentDate->isCurrentMonth();
                            @endphp
                            <tr>
                                <td>{{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}</td>
                                <td>
                                    {{ $paymentDate->format('d M Y') }}
                                    @if($isCurrentMonth)
                                        <span class="badge-current ms-1">Current</span>
                                    @else
                                        <span class="badge-past ms-1">Past</span>
                                    @endif
                                </td>
                                <td class="amount-expense">- Rs. {{ number_format($expense->amount, 2) }}</td>
                                <td>
                                    {{ $expense->reason ?? '-' }}
                                    @if($expense->note)
                                        <small class="d-block text-muted">{{ Str::limit($expense->note, 40) }}</small>
                                    @endif
                                </td>
                                <td>{{ $expense->reason_code ?? '-' }}</td>
                                <td>
                                    {{ $expense->user->name ?? 'System' }}
                                    <small class="d-block text-muted">{{ $expense->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.institute-expenses.show', $expense->id) }}" class="btn-view"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.institute-expenses.edit', $expense->id) }}" class="btn-edit"><i class="bi bi-pencil"></i></a>
                                        <button type="button" class="btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $expense->id }}"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal{{ $expense->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3rem;"></i>
                                            <h5 class="mt-3">Are you sure?</h5>
                                            <p>Delete expense of <strong>Rs. {{ number_format($expense->amount, 2) }}</strong> from <strong>{{ $paymentDate->format('d M Y') }}</strong>?</p>
                                            @if(!$isCurrentMonth)
                                                <div class="alert alert-warning">This expense is from a past month. Deleting may affect reports.</div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('admin.institute-expenses.destroy', $expense->id) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="7" class="empty-state"><i class="bi bi-inbox"></i><h5>No Expenses Found</h5></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="pagination-container">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
@endsection