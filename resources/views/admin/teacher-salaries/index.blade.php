@extends('layouts.app')

@section('title', 'Teacher Salary Report')
@section('page-title', 'Teacher Salary Report')

@section('content')

    <div class="salary-report-page">

        <!-- HEADER -->
        <div class="hero-card mb-4">

            <div class="hero-content">

                <div>

                    <h3 class="fw-bold mb-1">
                        Teacher Salary Report
                    </h3>

                    <p class="text-muted mb-0">
                        {{ $monthName }} {{ $year }}
                    </p>

                </div>

                <div class="hero-actions">

                    <a href="{{ route('admin.teacher-salaries.index', [
        'year' => $year,
        'month' => $month
    ]) }}" class="btn btn-outline-secondary custom-btn">

                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Refresh

                    </a>

                    <a href="{{ route('admin.teacher-salaries.index', [
        'year' => $year,
        'month' => $month
    ]) }}" class="btn btn-danger custom-btn">

                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        PDF Export

                    </a>

                </div>

            </div>

        </div>

        <!-- FILTER -->
        <div class="filter-card mb-4">

            <form method="GET" class="row g-3 align-items-end">

                <div class="col-lg-3 col-md-6">

                    <label class="form-label fw-semibold">
                        Year
                    </label>

                    <select name="year" class="form-select custom-input">

                        @for($y = now()->year; $y >= 2020; $y--)

                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>

                                {{ $y }}

                            </option>

                        @endfor

                    </select>

                </div>

                <div class="col-lg-3 col-md-6">

                    <label class="form-label fw-semibold">
                        Month
                    </label>

                    <select name="month" class="form-select custom-input">

                        @for($m = 1; $m <= 12; $m++)

                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>

                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}

                            </option>

                        @endfor

                    </select>

                </div>

                <div class="col-lg-2 col-md-6">

                    <button class="btn btn-primary w-100 custom-btn">

                        <i class="bi bi-funnel me-1"></i>
                        Filter

                    </button>

                </div>

            </form>

        </div>

        <!-- SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">

                <div class="summary-card summary-blue">

                    <div class="card-body">

                        <i class="bi bi-people-fill summary-icon"></i>

                        <h6>Total Teachers</h6>

                        <h3>
                            {{ $summary['total_teachers'] }}
                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-md-6">

                <div class="summary-card summary-primary">

                    <div class="card-body">

                        <i class="bi bi-cash-stack summary-icon"></i>

                        <h6>Gross Earnings</h6>

                        <h3>
                            LKR {{ number_format($summary['gross_earnings'] ?? 0, 2) }}
                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-md-6">

                <div class="summary-card summary-red">

                    <div class="card-body">

                        <i class="bi bi-arrow-down-circle summary-icon"></i>

                        <h6>Total Deductions</h6>

                        <h3>
                            LKR {{
        number_format(
            ($summary['total_advance'] ?? 0) +
            ($summary['total_deduction'] ?? 0) +
            ($summary['total_other'] ?? 0),
            2
        )
                            }}
                        </h3>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-md-6">

                <div class="summary-card summary-green">

                    <div class="card-body">

                        <i class="bi bi-wallet2 summary-icon"></i>

                        <h6>Net Payable</h6>

                        <h3>
                            LKR {{ number_format($summary['net_payable'] ?? 0, 2) }}
                        </h3>

                    </div>

                </div>

            </div>

        </div>

        <!-- TABLE -->
        <div class="main-card">

            <div class="main-card-header">

                <div>

                    <h4>
                        Monthly Teacher Salary Summary
                    </h4>

                    <p>
                        Salary earnings and deductions overview
                    </p>

                </div>

                <div class="header-badge">

                    {{ count($salaryRows) }} Teachers

                </div>

            </div>

            <div class="table-responsive">

                <table class="table custom-table align-middle mb-0">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Teacher</th>
                            <th class="text-end">Gross</th>
                            <th class="text-end">Advance</th>
                            <th class="text-end">Deduction</th>
                            <th class="text-end">Other</th>
                            <th class="text-end">Net Payable</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($salaryRows as $i => $row)

                                            <tr>

                                                <td class="text-muted">
                                                    {{ $i + 1 }}
                                                </td>

                                                <td>

                                                    <div class="teacher-cell">

                                                        <div class="teacher-avatar">
                                                            {{ strtoupper(substr($row['teacher_name'], 0, 1)) }}
                                                        </div>

                                                        <div>

                                                            <div class="fw-bold">
                                                                {{ $row['teacher_name'] }}
                                                            </div>

                                                            <small class="text-muted">
                                                                {{ $row['teacher_custom_id'] }}
                                                            </small>

                                                        </div>

                                                    </div>

                                                </td>

                                                <td class="text-end fw-semibold">
                                                    {{ number_format($row['gross_earnings'] ?? 0, 2) }}
                                                </td>

                                                <td class="text-end text-warning fw-semibold">
                                                    {{ number_format($row['advance'] ?? 0, 2) }}
                                                </td>

                                                <td class="text-end text-danger fw-semibold">
                                                    {{ number_format($row['deduction'] ?? 0, 2) }}
                                                </td>

                                                <td class="text-end">
                                                    {{ number_format($row['other'] ?? 0, 2) }}
                                                </td>

                                                <td class="text-end fw-bold text-success">
                                                    {{ number_format($row['net_payable'] ?? 0, 2) }}
                                                </td>

                                                <td>

                                                    @if(($row['status'] ?? 'pending') === 'paid')

                                                        <span class="badge bg-success custom-badge">
                                                            Paid
                                                        </span>

                                                    @else

                                                        <span class="badge bg-warning text-dark custom-badge">
                                                            Pending
                                                        </span>

                                                    @endif

                                                </td>

                                                <td class="text-center">

                                                    <a href="{{ route('admin.teacher-salaries.show', [
                                'teacher' => $row['teacher_id'],
                                'year' => $year,
                                'month' => $month
                            ]) }}" class="action-btn view-btn">

                                                        <i class="bi bi-eye-fill"></i>

                                                    </a>

                                                </td>

                                            </tr>

                        @empty

                            <tr>

                                <td colspan="9" class="text-center py-5 text-muted">

                                    <div class="empty-state">

                                        <i class="bi bi-receipt"></i>

                                        <h5>
                                            No salary data found
                                        </h5>

                                        <p>
                                            Try changing the selected month or year
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection

@push('styles')

    <style>
        .salary-report-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .filter-card,
        .main-card {
            background: #fff;
            border-radius: 28px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
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
            padding: .75rem 1.2rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
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
            min-height: 150px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
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

        .summary-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
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
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            padding: .55rem .9rem;
            border-radius: 999px;
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

        .teacher-cell {
            display: flex;
            align-items: center;
            gap: .9rem;
        }

        .teacher-avatar {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
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
            background: #eff6ff;
            color: #2563eb;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .05);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            background: #2563eb;
            color: #fff;
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

        @media(max-width:768px) {

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
    </style>

@endpush