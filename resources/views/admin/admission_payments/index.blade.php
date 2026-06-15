@extends('layouts.app')

@section('title', 'Admission Payments')
@section('page-title', 'Admission Payments')

@section('content')

@php
    $totalPayments = $payments->count();
    $paidPayments = $payments->where('status', 'paid')->count();
    $pendingPayments = $payments->where('status', 'pending')->count();
    $refundedPayments = $payments->where('status', 'refunded')->count();
    $totalAmount = $payments->sum('amount');
@endphp

<div class="admission-payments-page">

    {{-- STATS --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h3>{{ $totalPayments }}</h3>
                    <p>Total Payments</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <h3>{{ $paidPayments }}</h3>
                    <p>Paid Payments</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon orange">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div>
                    <h3>{{ $pendingPayments }}</h3>
                    <p>Pending Payments</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon red">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <h3>Rs. {{ number_format($totalAmount, 2) }}</h3>
                    <p>Total Amount</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CARD --}}
    <div class="main-card">

        {{-- HEADER --}}
        <div class="main-card-header">
            <div>
                <h4>Admission Payments Management</h4>
                <p>Manage student admission payment records, status, and payment details</p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.admission-payments.create') }}" class="btn btn-primary custom-btn">
                    <i class="bi bi-plus-lg"></i>
                    Add Payment
                </a>
            </div>
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="alert alert-success custom-alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- SEARCH / FILTER --}}
        <div class="search-card">
            <form method="GET" action="{{ route('admin.admission-payments.index') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-6">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search"></i>
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control custom-input"
                                placeholder="Search receipt / student / admission">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <select name="status" class="form-select custom-input">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <button class="btn btn-dark w-100 custom-btn" type="submit">
                            <i class="bi bi-funnel-fill"></i>
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt</th>
                        <th>Student</th>
                        <th>Admission</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>

                            <td>
                                <div class="fw-semibold">{{ $payment->receipt_number }}</div>
                                <small class="text-muted">Payment Record</small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $payment->student->initial_name ?? '-' }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $payment->admission->name ?? '-' }}
                                </div>
                            </td>

                            <td>
                                <div class="amount-text">
                                    Rs. {{ number_format($payment->amount, 2) }}
                                </div>
                            </td>

                            <td>
                                @switch($payment->payment_method)
                                    @case('cash')
                                        <span class="badge custom-badge bg-light text-dark border">Cash</span>
                                    @break

                                    @case('card')
                                        <span class="badge custom-badge bg-primary-subtle text-primary border">Card</span>
                                    @break

                                    @case('bank_transfer')
                                        <span class="badge custom-badge bg-info-subtle text-info border">Bank Transfer</span>
                                    @break

                                    @case('online')
                                        <span class="badge custom-badge bg-success-subtle text-success border">Online</span>
                                    @break

                                    @case('other')
                                        <span class="badge custom-badge bg-secondary-subtle text-secondary border">Other</span>
                                    @break

                                    @default
                                        <span class="badge custom-badge bg-light text-dark border">Unknown</span>
                                @endswitch
                            </td>

                            <td>
                                @switch($payment->status)
                                    @case('pending')
                                        <span class="badge bg-warning custom-badge text-dark">Pending</span>
                                    @break

                                    @case('paid')
                                        <span class="badge bg-success custom-badge">Paid</span>
                                    @break

                                    @case('cancelled')
                                        <span class="badge bg-danger custom-badge">Cancelled</span>
                                    @break

                                    @case('refunded')
                                        <span class="badge bg-info custom-badge text-dark">Refunded</span>
                                    @break

                                    @default
                                        <span class="badge bg-secondary custom-badge">Unknown</span>
                                @endswitch
                            </td>

                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.admission-payments.show', $payment) }}" class="action-btn view-btn" title="View">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    <a href="{{ route('admin.admission-payments.edit', $payment) }}" class="action-btn edit-btn" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>

                                    <form action="{{ route('admin.admission-payments.destroy', $payment) }}" method="POST"
                                        onsubmit="return confirm('Delete payment?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="action-btn delete-btn" title="Delete">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="empty-state">
                                    <i class="bi bi-receipt"></i>
                                    <h5>No Payments Found</h5>
                                    <p>Try adjusting the search filters or add a new payment</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $payments->links() }}
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .admission-payments-page {
        animation: fadeIn 0.4s ease;
    }

    .stats-card {
        background: #fff;
        border-radius: 24px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eef2f7;
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        color: #fff;
        flex-shrink: 0;
    }

    .blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .green { background: linear-gradient(135deg, #10b981, #34d399); }
    .orange { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .red { background: linear-gradient(135deg, #ef4444, #f87171); }

    .stats-card h3 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .stats-card p {
        margin: 0;
        color: #64748b;
    }

    .main-card {
        background: #fff;
        border-radius: 28px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: 1px solid #eef2f7;
    }

    .main-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .main-card-header h4 {
        margin: 0;
        font-weight: 800;
    }

    .main-card-header p {
        margin: .25rem 0 0 0;
        color: #64748b;
    }

    .header-buttons {
        display: flex;
        gap: .7rem;
        flex-wrap: wrap;
    }

    .custom-btn {
        border-radius: 14px;
        padding: .72rem 1.2rem;
        font-weight: 700;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    .custom-alert {
        border-radius: 16px;
        border: 1px solid #bbf7d0;
    }

    .search-card {
        background: #f8fafc;
        border-radius: 20px;
        padding: 1rem;
        margin-bottom: 1.25rem;
        border: 1px solid #eef2f7;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper i {
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    .custom-input {
        min-height: 48px;
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        box-shadow: none !important;
        padding-left: 42px;
    }

    select.custom-input {
        padding-left: 16px;
    }

    .custom-table thead th {
        border: none;
        background: #f8fafc;
        color: #475569;
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: 1rem;
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

    .amount-text {
        font-weight: 800;
        color: #0f172a;
    }

    .custom-badge {
        border-radius: 10px;
        padding: .5rem .7rem;
        font-size: .75rem;
        font-weight: 700;
    }

    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: .2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
    }

    .view-btn {
        background: #eff6ff;
        color: #2563eb;
    }

    .edit-btn {
        background: #fef3c7;
        color: #d97706;
    }

    .delete-btn {
        background: #fef2f2;
        color: #ef4444;
    }

    .empty-state i {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
        display: inline-block;
    }

    .empty-state h5 {
        font-weight: 800;
        margin-bottom: .35rem;
    }

    .empty-state p {
        margin: 0;
        color: #64748b;
    }

    @media (max-width: 768px) {
        .main-card,
        .stats-card {
            border-radius: 22px;
        }

        .main-card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-buttons {
            width: 100%;
        }

        .header-buttons a,
        .header-buttons button {
            flex: 1;
            justify-content: center;
        }

        .action-buttons {
            justify-content: flex-start;
        }
    }
</style>
@endpush