@extends('layouts.app')

@section('title', 'Teacher Expenses')
@section('page-title', 'Teacher Expenses')

@section('content')

    @php
        $periodYear = explode('-', $data['period'])[0] ?? now()->year;
        $periodMonth = explode('-', $data['period'])[1] ?? now()->month;
        $periodName = \Carbon\Carbon::createFromDate((int) $periodYear, (int) $periodMonth, 1)->format('F Y');

        $grossEarnings = $data['teacher_earnings'] ?? 0;
        $totalDeductions = ($data['advance'] ?? 0) + ($data['deduction'] ?? 0) + ($data['other'] ?? 0);
        $netPayable = $data['net_payable'] ?? 0;
        $totalClassIncome = $data['total_class_income'] ?? 0;

        $teacherSharePercent = $totalClassIncome > 0
            ? round(($grossEarnings / $totalClassIncome) * 100, 1)
            : 0;
    @endphp

    <div class="teacher-expenses-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">

                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($data['teacher']->full_name, 0, 1)) }}
                    </div>

                    <div>
                        <h3 class="mb-1 fw-bold">
                            Teacher Expenses
                        </h3>
                        <p class="mb-0 text-muted">
                            {{ $data['teacher']->full_name }} · {{ $data['teacher']->custom_id }} · {{ $periodName }}
                        </p>
                    </div>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.teacher-salaries.show', [
        'teacher' => $data['teacher']->id,
        'year' => $periodYear,
        'month' => $periodMonth
    ]) }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Salary Details
                    </a>
                </div>

            </div>
        </div>

        <!-- SUMMARY CARDS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-building summary-icon"></i>
                        <h6>Total Class Income</h6>
                        <h3>LKR {{ number_format($totalClassIncome, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-person-badge-fill summary-icon"></i>
                        <h6>Teacher Share ({{ $teacherSharePercent }}%)</h6>
                        <h3>LKR {{ number_format($grossEarnings, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-red">
                    <div class="card-body">
                        <i class="bi bi-dash-circle summary-icon"></i>
                        <h6>Total Deductions</h6>
                        <h3>LKR {{ number_format($totalDeductions, 2) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-primary">
                    <div class="card-body">
                        <i class="bi bi-wallet2 summary-icon"></i>
                        <h6>Net Payable</h6>
                        <h3>LKR {{ number_format($netPayable, 2) }}</h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- TEACHER INFO -->
        <div class="info-card mb-4">
            <div class="section-header">
                <h6 class="mb-0 fw-bold">Teacher Information</h6>
                <span class="section-pill">
                    {{ $periodName }}
                </span>
            </div>

            <div class="row g-3">

                <div class="col-lg-4 col-md-6">
                    <div class="info-box">
                        <small class="text-muted">Teacher Name</small>
                        <div class="fw-bold">{{ $data['teacher']->full_name }}</div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="info-box">
                        <small class="text-muted">Teacher ID</small>
                        <div class="fw-bold">{{ $data['teacher']->custom_id }}</div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="info-box">
                        <small class="text-muted">Salary Status</small>
                        <div>
                            @if($data['salary_status'] === 'paid')
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

                <div class="col-lg-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Total Class Income</small>
                        <h5 class="text-primary mb-0">LKR {{ number_format($totalClassIncome, 2) }}</h5>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Teacher Earnings</small>
                        <h5 class="text-success mb-0">LKR {{ number_format($grossEarnings, 2) }}</h5>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Institute Share</small>
                        <h5 class="text-dark mb-0">LKR {{ number_format($data['institute_earnings'] ?? 0, 2) }}</h5>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="mini-stat">
                        <small class="text-muted">Organizer Share</small>
                        <h5 class="text-dark mb-0">LKR {{ number_format($data['organizer_earnings'] ?? 0, 2) }}</h5>
                    </div>
                </div>

            </div>

        </div>

        <!-- PAYMENT DETAILS -->
        <div class="main-card mb-4">

            <div class="main-card-header">
                <div>
                    <h4>Payment / Adjustment Details</h4>
                    <p>Advance, deduction, and other records for this period</p>
                </div>

                <div class="header-badge">
                    {{ count($data['payment_details'] ?? []) }} Records
                </div>
            </div>

            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount (LKR)</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data['payment_details'] ?? [] as $payment)
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($payment['date'])->format('Y-m-d') }}
                                </td>

                                <td>
                                    @if($payment['type'] == 'advance')
                                        <span class="badge bg-warning text-dark custom-badge">Advance</span>
                                    @elseif($payment['type'] == 'deduction')
                                        <span class="badge bg-danger custom-badge">Deduction</span>
                                    @else
                                        <span class="badge bg-secondary custom-badge">Other</span>
                                    @endif
                                </td>

                                <td class="text-danger fw-semibold">
                                    LKR {{ number_format($payment['amount'], 2) }}
                                </td>

                                <td>
                                    {{ $payment['reason'] ?? '-' }}
                                </td>

                                <td>
                                    @if($payment['status'] == 'paid')
                                        <span class="badge bg-success custom-badge">Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark custom-badge">Pending</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $payment['created_by'] ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-receipt"></i>
                                        <h5>No payment records for this period</h5>
                                        <p class="mb-0">There are no advances, deductions, or adjustments recorded.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Total Deductions:</td>
                            <td class="text-danger">LKR {{ number_format($totalDeductions, 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="final-card">
                    <small class="text-muted">Gross Earnings</small>
                    <h4 class="text-success mb-0">LKR {{ number_format($grossEarnings, 2) }}</h4>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="final-card">
                    <small class="text-muted">Total Deductions</small>
                    <h4 class="text-danger mb-0">LKR {{ number_format($totalDeductions, 2) }}</h4>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="final-card highlight">
                    <small class="text-muted">Net Payable</small>
                    <h3 class="text-primary mb-0">LKR {{ number_format($netPayable, 2) }}</h3>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('styles')
    <style>
        .teacher-expenses-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card,
        .info-card,
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

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .summary-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .main-card {
            margin-bottom: 1.5rem;
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
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eef2f7;
        }

        .section-pill {
            display: inline-flex;
            align-items: center;
            padding: .35rem .75rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 600;
            font-size: .82rem;
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
            margin-bottom: 0;
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

        .final-card.highlight {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions {
                width: 100%;
            }

            .hero-actions .btn {
                flex: 1;
            }

            .section-header,
            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
@endpush