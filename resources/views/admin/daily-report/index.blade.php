@extends('layouts.app')

@section('title', 'Daily Reports')
@section('page-title', 'Daily Reports')

@push('styles')
    <style>
        .daily-report-page {
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

        /* Filter Card */
        .filter-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .filter-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .filter-input {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
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
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
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
        }

        .stat-card:hover {
            border-color: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0;
        }

        .stat-value.text-primary { color: #2563eb; }
        .stat-value.text-success { color: #10b981; }
        .stat-value.text-warning { color: #f59e0b; }
        .stat-value.text-danger { color: #ef4444; }
        .stat-value.text-info { color: #0ea5e9; }

        /* Report Card */
        .report-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .report-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
        }

        .report-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .report-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .btn-pdf {
            background: #dc2626;
            border: none;
            border-radius: 12px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-pdf:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .btn-excel {
            background: #059669;
            border: none;
            border-radius: 12px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-excel:hover {
            background: #047857;
            transform: translateY(-2px);
        }

        /* Table Styles */
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
        }

        .data-table tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .amount-income {
            color: #10b981;
            font-weight: 700;
        }

        .amount-expense {
            color: #ef4444;
            font-weight: 700;
        }

        .amount-net {
            color: #2563eb;
            font-weight: 700;
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
            .hero-card { padding: 1.5rem; }
            .stat-value { font-size: 1.1rem; }
            .data-table thead th { font-size: 0.65rem; padding: 0.75rem; }
            .data-table tbody td { padding: 0.75rem; font-size: 0.75rem; }
        }
    </style>
@endpush

@section('content')
    <div class="daily-report-page">
        
        <!-- Hero Card -->
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="mb-3">
                        <span class="hero-badge">
                            <i class="bi bi-file-text-fill"></i>
                            Daily Reports
                        </span>
                    </div>
                    <h2 class="fw-bold mb-2">Financial Reports</h2>
                    <p class="mb-0 text-light-soft">
                        View and download daily financial reports including income, expenses, and net balance.
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-calendar-event-fill"></i>
                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.daily-report.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <div class="filter-label">Select Date</div>
                    <input type="date" name="date" class="filter-input" value="{{ $date }}">
                </div>
                <div class="col-md-4">
                    <div class="filter-label">Report Type</div>
                    <select name="type" class="filter-input">
                        <option value="summary" {{ $type == 'summary' ? 'selected' : '' }}>📊 Summary Report (Income/Expense)</option>
                        <option value="student" {{ $type == 'student' ? 'selected' : '' }}>📚 Student Payment Report</option>
                        <option value="teacher" {{ $type == 'teacher' ? 'selected' : '' }}>👨‍🏫 Teacher Collection Report</option>
                        <option value="institution" {{ $type == 'institution' ? 'selected' : '' }}>🏛️ Institution Report</option>
                        <option value="organizer" {{ $type == 'organizer' ? 'selected' : '' }}>📅 Organizer Report</option>
                        <option value="admission" {{ $type == 'admission' ? 'selected' : '' }}>🎓 Admission Report</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">&nbsp;</div>
                    <button type="submit" class="btn-filter">
                        <i class="bi bi-search me-1"></i> Generate Report
                    </button>
                </div>
                <div class="col-md-2">
                    <div class="filter-label">&nbsp;</div>
                    <a href="{{ route('admin.daily-report.index') }}" class="btn-filter" style="background: #64748b; display: block; text-align: center; text-decoration: none;">
                        <i class="bi bi-arrow-repeat me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Summary Stats Cards (Only for Summary Report) -->
        @if($type == 'summary' && isset($summary))
        <div class="stats-row">
            <div class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-label">Total Income</div>
                        <div class="stat-value text-success">
                            Rs. {{ number_format(($summary['payment_total'] ?? 0) + ($summary['admission_total'] ?? 0) + ($summary['extra_income_total'] ?? 0), 2) }}
                        </div>
                        <small class="text-muted">Student + Admission + Extra</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-label">Total Expenses</div>
                        <div class="stat-value text-danger">
                            Rs. {{ number_format(($summary['teacher_expense_total'] ?? 0) + ($summary['organizer_expense_total'] ?? 0) + ($summary['instituteExpencesTotal'] ?? 0), 2) }}
                        </div>
                        <small class="text-muted">Teacher + Organizer + Institute</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-label">Student Payments</div>
                        <div class="stat-value text-primary">
                            Rs. {{ number_format($summary['payment_total'] ?? 0, 2) }}
                        </div>
                        <small class="text-muted">Today's collections</small>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-label">Net Balance</div>
                        <div class="stat-value text-info">
                            Rs. {{ number_format($summary['net_total'] ?? 0, 2) }}
                        </div>
                        <small class="text-muted">Income - Expenses</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Report Table -->
        <div class="report-card">
            <div class="report-header">
                <h5 class="report-title">
                    <i class="bi bi-table"></i> {{ $report['title'] ?? 'Report' }}
                </h5>
                <div class="d-flex gap-2">
                    @if($type == 'summary')
                        <a href="{{ route('admin.daily-report.summary.pdf', ['date' => $date]) }}" class="btn-pdf">
                            <i class="bi bi-filetype-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('admin.daily-report.summary.excel', ['date' => $date]) }}" class="btn-excel">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                        </a>
                    @else
                        <a href="{{ route('admin.daily-report.pdf', ['type' => $type, 'date' => $date]) }}" class="btn-pdf">
                            <i class="bi bi-filetype-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('admin.daily-report.excel', ['type' => $type, 'date' => $date]) }}" class="btn-excel">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Excel
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            @foreach($report['headings'] ?? [] as $heading)
                                <th>{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['rows'] ?? [] as $row)
                            <tr>
                                @foreach($report['columns'] ?? [] as $column)
                                    @php
                                        $value = data_get($row, $column, '-');
                                        $isAmount = str_contains($column, 'amount') || str_contains($column, 'fee') || str_contains($column, 'total');
                                        $rowType = $row['type'] ?? '';
                                    @endphp
                                    <td class="{{ $isAmount ? 'text-end' : '' }}">
                                        @if($isAmount && is_numeric($value))
                                            @if($rowType == 'expense')
                                                <span class="amount-expense">- Rs. {{ number_format($value, 2) }}</span>
                                            @elseif($rowType == 'income')
                                                <span class="amount-income">Rs. {{ number_format($value, 2) }}</span>
                                            @elseif($rowType == 'net')
                                                <span class="amount-net">Rs. {{ number_format($value, 2) }}</span>
                                            @else
                                                Rs. {{ number_format($value, 2) }}
                                            @endif
                                        @elseif($isAmount && !is_numeric($value))
                                            {{ $value }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($report['headings'] ?? 1) }}" class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No Records Found</h5>
                                    <p class="text-muted">No data available for the selected date and report type.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(($report['count'] ?? 0) > 0)
            <div class="report-header" style="border-top: 1px solid #eef2f7; border-bottom: none;">
                <div class="text-muted small">
                    <i class="bi bi-info-circle"></i> 
                    Showing {{ $report['count'] }} record(s) | 
                    {{ $report['summary_label'] ?? 'Total' }}: 
                    <strong>Rs. {{ number_format($report['summary_value'] ?? 0, 2) }}</strong>
                </div>
            </div>
            @endif
        </div>

        <!-- Footer Note -->
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-printer"></i> Report generated on {{ now()->format('d M Y, h:i A') }}
            </small>
        </div>
    </div>
@endsection