@extends('layouts.app')

@section('title', 'Teacher Payment Details')
@section('page-title', 'Teacher Payment Details')

@section('content')

    @php
        $monthName = $month_name ?? \Carbon\Carbon::createFromDate((int) $year, (int) $month, 1)->format('F');
        $periodLabel = $monthName . ' ' . $year;

        $isPaid = isset($salary_status) && $salary_status === 'paid';
        $totalPayments = $payments_count ?? 0;
        $totalAll = $totals['all'] ?? 0;
        $totalAdvance = $totals['advance'] ?? 0;
        $totalDeduction = $totals['deduction'] ?? 0;
        $totalOther = $totals['other'] ?? 0;
    @endphp

    <div class="teacher-payment-details-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">

                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($teacher->full_name, 0, 1)) }}
                    </div>

                    <div>
                        <h3 class="mb-1 fw-bold">{{ $teacher->full_name }}</h3>
                        <p class="mb-0 text-muted">
                            Payment Details • {{ $periodLabel }}
                        </p>
                    </div>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.teacher-salaries.show', [$teacher->id, $year, $month]) }}"
                        class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Salary Details
                    </a>
                </div>

            </div>
        </div>

        <!-- STATUS / SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-receipt-cutoff summary-icon"></i>
                        <h6>Total Payments</h6>
                        <h3>LKR {{ number_format($totalAll, 2) }}</h3>
                        <small class="summary-sub">
                            {{ $totalPayments }} transactions
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-orange">
                    <div class="card-body">
                        <i class="bi bi-arrow-up-circle summary-icon"></i>
                        <h6>Advances</h6>
                        <h3>LKR {{ number_format($totalAdvance, 2) }}</h3>
                        <small class="summary-sub">
                            Advance transactions
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-red">
                    <div class="card-body">
                        <i class="bi bi-arrow-down-circle summary-icon"></i>
                        <h6>Deductions</h6>
                        <h3>LKR {{ number_format($totalDeduction, 2) }}</h3>
                        <small class="summary-sub">
                            Deduction transactions
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-three-dots summary-icon"></i>
                        <h6>Other</h6>
                        <h3>LKR {{ number_format($totalOther, 2) }}</h3>
                        <small class="summary-sub">
                            Other adjustments
                        </small>
                    </div>
                </div>
            </div>

        </div>

        <!-- TEACHER INFO -->
        <div class="info-card mb-4">
            <div class="section-header">
                <h6 class="mb-0 fw-bold">Teacher Information</h6>
                <span class="section-pill">{{ $periodLabel }}</span>
            </div>

            <div class="row g-3">

                <div class="col-lg-4 col-md-6">
                    <div class="info-box">
                        <small class="text-muted d-block">Teacher Name</small>
                        <div class="fw-bold">{{ $teacher->full_name }}</div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="info-box">
                        <small class="text-muted d-block">Teacher ID</small>
                        <div class="fw-bold">{{ $teacher->custom_id }}</div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="info-box">
                        <small class="text-muted d-block">Salary Status</small>
                        <div>
                            @if($isPaid)
                                <span class="badge bg-success custom-badge">Paid</span>
                            @else
                                <span class="badge bg-warning text-dark custom-badge">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- INCOME BREAKDOWN -->
        <div class="main-card mb-4">
            <div class="main-card-header">
                <div>
                    <h4>Income Breakdown</h4>
                    <p>Detailed split of class earnings and shares</p>
                </div>
            </div>

            <div class="row g-4">

                <div class="col-xl-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Total Class Income</small>
                        <h5 class="text-primary mb-0">LKR {{ number_format($data['total_class_income'] ?? 0, 2) }}</h5>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Teacher Earnings</small>
                        <h5 class="text-success mb-0">LKR {{ number_format($data['teacher_earnings'] ?? 0, 2) }}</h5>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Institute Earnings</small>
                        <h5 class="text-dark mb-0">LKR {{ number_format($data['institute_earnings'] ?? 0, 2) }}</h5>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Organizer Earnings</small>
                        <h5 class="text-dark mb-0">LKR {{ number_format($data['organizer_earnings'] ?? 0, 2) }}</h5>
                    </div>
                </div>

            </div>
        </div>

        <!-- PAYMENT HISTORY -->
        <div class="main-card mb-4">

            <div class="main-card-header">
                <div>
                    <h4>Transaction History</h4>
                    <p>Advance, deduction, and other payment records</p>
                </div>

                <div class="header-badge">
                    {{ $totalPayments }} Records
                </div>
            </div>

            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount (LKR)</th>
                            <th>Reason</th>
                            <th>Note</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse(($payments ?? []) as $index => $payment)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                </td>

                                <td>
                                    @if($payment->payment_type == 'advance')
                                        <span class="badge bg-warning text-dark custom-badge">
                                            <i class="bi bi-arrow-up-circle me-1"></i>
                                            Advance
                                        </span>
                                    @elseif($payment->payment_type == 'deduction')
                                        <span class="badge bg-danger custom-badge">
                                            <i class="bi bi-arrow-down-circle me-1"></i>
                                            Deduction
                                        </span>
                                    @else
                                        <span class="badge bg-info custom-badge">
                                            <i class="bi bi-three-dots me-1"></i>
                                            Other
                                        </span>
                                    @endif
                                </td>

                                <td class="fw-semibold">
                                    @if($payment->payment_type == 'advance')
                                        <span class="text-warning">+ LKR {{ number_format($payment->amount, 2) }}</span>
                                    @elseif($payment->payment_type == 'deduction')
                                        <span class="text-danger">- LKR {{ number_format($payment->amount, 2) }}</span>
                                    @else
                                        <span class="text-info">LKR {{ number_format($payment->amount, 2) }}</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $payment->reason ?? '—' }}
                                </td>

                                <td>
                                    @if($payment->note)
                                        <span class="note-icon" data-bs-toggle="tooltip" title="{{ $payment->note }}">
                                            <i class="bi bi-info-circle-fill text-muted"></i>
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($isPaid)
                                        <button class="btn btn-sm btn-secondary action-btn" disabled
                                            title="Record is locked because salary is paid">
                                            <i class="bi bi-lock-fill me-1"></i>
                                            Delete
                                        </button>
                                    @else
                                        <form method="POST"
                                            action="{{ route('admin.teacher-salaries.payment-delete', $payment->id) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this payment record? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger action-btn"
                                                title="Delete payment record">
                                                <i class="bi bi-trash-fill me-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h5>No payment records found for this month</h5>
                                        <p class="mb-0">There are no teacher payment adjustments in this period.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($totalPayments > 0)
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="fw-bold">LKR {{ number_format($totalAll, 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- FINAL SUMMARY -->
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="final-card">
                    <small class="text-muted d-block">Gross Earnings</small>
                    <h4 class="text-success mb-0">LKR {{ number_format($data['teacher_earnings'] ?? 0, 2) }}</h4>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="final-card">
                    <small class="text-muted d-block">Total Deductions</small>
                    <h4 class="text-danger mb-0">LKR {{ number_format($totalAdvance + $totalDeduction + $totalOther, 2) }}
                    </h4>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="final-card highlight">
                    <small class="text-muted d-block">Net Payable</small>
                    <h3 class="text-primary mb-0">LKR {{ number_format($data['net_payable'] ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- STATUS ALERT -->
        <div class="mt-4">
            @if($isPaid)
                <div class="alert alert-secondary custom-alert mb-0">
                    <i class="bi bi-lock-fill me-2"></i>
                    <strong>Records Locked:</strong> This salary has been paid. Payment records cannot be modified or deleted.
                </div>
            @else
                <div class="alert alert-info custom-alert mb-0">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Delete Available:</strong> You can delete payment records while salary is unpaid.
                </div>
            @endif
        </div>

    </div>

@endsection

@push('styles')
    <style>
        .teacher-payment-details-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .info-card,
        .main-card,
        .final-card {
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

        .profile-avatar {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.6rem;
            box-shadow: 0 10px 20px rgba(79, 70, 229, .20);
            flex-shrink: 0;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
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
            min-height: 145px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            border: none;
        }

        .summary-card .card-body {
            padding: 1.4rem;
            position: relative;
        }

        .summary-card h6 {
            opacity: .9;
            margin-bottom: .6rem;
        }

        .summary-card h3 {
            margin: 0;
            font-weight: 800;
            word-break: break-word;
        }

        .summary-sub {
            opacity: .85;
        }

        .summary-icon {
            position: absolute;
            right: 18px;
            top: 18px;
            font-size: 2rem;
            opacity: .18;
        }

        .summary-blue {
            background: linear-gradient(135deg, #0f172a, #334155);
        }

        .summary-orange {
            background: linear-gradient(135deg, #ea580c, #f97316);
        }

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .section-header,
        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }

        .section-header {
            padding-bottom: 1rem;
            border-bottom: 1px solid #eef2f7;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
        }

        .section-pill,
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

        .info-box,
        .mini-stat,
        .final-card {
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 18px;
            padding: 1rem 1.1rem;
        }

        .mini-stat h5,
        .final-card h4,
        .final-card h3 {
            font-weight: 800;
        }

        .final-card.highlight {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
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

        .custom-table tbody tr {
            transition: .2s ease;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .action-btn {
            border-radius: 14px;
            padding: .7rem 1rem;
            font-weight: 600;
        }

        .note-icon {
            cursor: help;
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

        .custom-alert {
            border-radius: 18px;
        }

        @media (max-width: 768px) {

            .hero-content,
            .section-header,
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
            .navbar {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .hero-card,
            .info-card,
            .main-card,
            .final-card {
                box-shadow: none !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endpush