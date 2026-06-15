@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    $greeting = 'Good Evening';
    if (now()->hour < 12) {
        $greeting = 'Good Morning';
    } elseif (now()->hour < 17) {
        $greeting = 'Good Afternoon';
    }
@endphp

@push('styles')
    <style>
        .dashboard-page {
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

        /* Dashboard Cards */
        .dashboard-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #eef2f7;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
            overflow: hidden;
            transition: all .3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px rgba(15, 23, 42, .1);
        }

        /* Stat Cards */
        .stat-card {
            padding: 1.4rem;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .05);
            right: -40px;
            bottom: -40px;
        }

        .stat-icon {
            width: 62px;
            height: 62px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            flex-shrink: 0;
        }

        .bg-primary-soft {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .bg-success-soft {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .bg-warning-soft {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .bg-danger-soft {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .bg-dark-soft {
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }

        /* Quick Actions - Button Styles */
        .quick-action-btn {
            border-radius: 20px;
            padding: 1rem 1.2rem;
            font-weight: 700;
            border: none;
            transition: .25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .7rem;
            position: relative;
            overflow: hidden;
            width: 100%;
            cursor: pointer;
        }

        .quick-action-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .08);
            opacity: 0;
            transition: .25s ease;
        }

        .quick-action-btn:hover::before {
            opacity: 1;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .btn-warning-custom {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        /* Summary Box */
        .summary-box {
            border-radius: 24px;
            padding: 1.4rem;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            text-align: center;
            height: 100%;
            transition: .25s ease;
        }

        .summary-box:hover {
            transform: translateY(-4px);
            background: #fff;
            border-color: #2563eb;
        }

        .summary-box h2 {
            font-weight: 800;
            margin-bottom: .3rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
        }

        /* Tables */
        .mini-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
            padding: 1rem;
        }

        .mini-table td {
            vertical-align: middle;
            padding: 0.9rem 1rem;
        }

        /* Badges */
        .badge-soft {
            padding: .45rem .75rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .78rem;
        }

        .badge-low {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-good {
            background: #dcfce7;
            color: #166534;
        }

        /* Expiring Students Card */
        .expiring-card {
            border: 2px solid #ef4444 !important;
            animation: pulse-border 2s ease-in-out infinite;
        }

        @keyframes pulse-border {
            0% {
                border-color: #ef4444;
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.2);
            }
            50% {
                border-color: #f87171;
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }
            100% {
                border-color: #ef4444;
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .badge-critical {
            background: #dc2626;
            color: white;
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .badge-warning-custom {
            background: #f59e0b;
            color: #78350f;
        }

        /* Chart Card */
        .chart-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            transition: all .3s ease;
        }

        .chart-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .chart-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .chart-container {
            padding: 1.5rem;
            position: relative;
        }

        .chart-stats {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.5rem 1.5rem;
            flex-wrap: wrap;
        }

        .chart-stat-item {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 16px;
            transition: all .2s ease;
        }

        .chart-stat-item:hover {
            background: #eff6ff;
            transform: translateY(-2px);
        }

        .chart-stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .chart-stat-value {
            font-size: 1.1rem;
            font-weight: 800;
            color: #2563eb;
        }

        .chart-stat-value.total {
            font-size: 1.3rem;
            color: #0f172a;
        }

        .year-selector {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: #475569;
            cursor: pointer;
            transition: all .2s ease;
        }

        .year-selector:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .year-selector:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Loading Overlay */
        .chart-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 28px;
            z-index: 10;
        }

        .chart-loading .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e2e8f0;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Button reset for nav-link-custom */
        .nav-link-custom {
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
        }

        @media(max-width: 768px) {
            .hero-card { padding: 1.5rem; border-radius: 26px; }
            .dashboard-card { border-radius: 24px; }
            .stat-card { padding: 1.2rem; }
            .chart-stats { flex-direction: column; }
            .chart-stat-item { flex: auto; }
            .chart-container { padding: 1rem; }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 dashboard-page">

        {{-- HERO --}}
        <div class="hero-card mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="mb-3">
                        <span class="hero-badge">
                            <i class="bi bi-stars"></i>
                            {{ $greeting }}
                        </span>
                    </div>
                    <h2 class="fw-bold mb-2">Welcome Back, {{ auth()->user()->name ?? 'Administrator' }}</h2>
                    <p class="mb-0 text-light-soft">Manage students, payments, attendance, classes, and ID cards from one premium dashboard.</p>
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

        {{-- ALERT --}}
        @if($showTemporaryIdCardWarning ?? false)
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Warning!</strong> Temporary ID cards are running low.
                Remaining stock: <b>{{ $temporaryIdCardPendingCount ?? 0 }}</b>
            </div>
        @endif

        {{-- STATS ROW --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-card stat-card">
                    <div class="d-flex justify-content-between align-items-center position-relative">
                        <div>
                            <small class="text-muted fw-semibold">Total Students</small>
                            <h2 class="fw-bold mt-2 mb-0">{{ $studentsCount ?? 0 }}</h2>
                            <small class="text-success">+{{ rand(5, 15) }}% this month</small>
                        </div>
                        <div class="stat-icon bg-primary-soft">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-card stat-card">
                    <div class="d-flex justify-content-between align-items-center position-relative">
                        <div>
                            <small class="text-muted fw-semibold">Total Teachers</small>
                            <h2 class="fw-bold mt-2 mb-0">{{ $teachersCount ?? 0 }}</h2>
                            <small class="text-success">Active faculty</small>
                        </div>
                        <div class="stat-icon bg-success-soft">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-card stat-card">
                    <div class="d-flex justify-content-between align-items-center position-relative">
                        <div>
                            <small class="text-muted fw-semibold">Running Classes</small>
                            <h2 class="fw-bold mt-2 mb-0">{{ $classesCount ?? 0 }}</h2>
                            <small class="text-muted">Active courses</small>
                        </div>
                        <div class="stat-icon bg-warning-soft">
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="dashboard-card stat-card">
                    <div class="d-flex justify-content-between align-items-center position-relative">
                        <div>
                            <small class="text-muted fw-semibold">Today's Income</small>
                            <h3 class="fw-bold mt-2 mb-0 text-success">Rs. {{ number_format($todayIncome ?? 0, 2) }}</h3>
                            <small class="text-muted">Updated now</small>
                        </div>
                        <div class="stat-icon bg-danger-soft">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECOND ROW --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-4">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="section-title mb-0">Quick Actions</h5>
                        <span class="badge bg-primary rounded-pill">Actions</span>
                    </div>
                    <div class="d-grid gap-3">
                        <button type="button" class="quick-action-btn btn-primary-custom" data-href="{{ route('admin.students.create') }}">
                            <i class="bi bi-person-plus-fill"></i> Add Student
                        </button>
                        <button type="button" class="quick-action-btn btn-success-custom" data-href="{{ route('admin.payments.index') }}">
                            <i class="bi bi-credit-card-2-front-fill"></i> Add Payment
                        </button>
                        <button type="button" class="quick-action-btn btn-warning-custom" data-href="{{ route('admin.student-classes.create') }}">
                            <i class="bi bi-calendar-plus-fill"></i> Create Class
                        </button>
                        <button type="button" class="quick-action-btn btn-info-custom" data-href="{{ route('admin.monthly-report.index') }}">
                            <i class="bi bi-file-earmark-bar-graph"></i> Report
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="section-title mb-0">System Overview</h5>
                        <span class="badge bg-dark rounded-pill">Live</span>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="summary-box">
                                <h2 class="text-primary">{{ $studentsCount ?? 0 }}</h2>
                                <p class="text-muted mb-0">Students Registered</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box">
                                <h2 class="text-success">{{ $teachersCount ?? 0 }}</h2>
                                <p class="text-muted mb-0">Teachers Active</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box">
                                <h2 class="text-warning">{{ $classesCount ?? 0 }}</h2>
                                <p class="text-muted mb-0">Classes Running</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHART SECTION - GRADIENT LINE CHART --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h5 class="chart-title">
                                <i class="bi bi-graph-up-arrow"></i> Institute Yearly Payment Report
                            </h5>
                            <p class="text-muted mb-0 small">Monthly payment analytics with dual dataset comparison</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="d-flex align-items-center gap-1"><span style="width: 12px; height: 12px; background: #2563eb; border-radius: 4px;"></span>
                                    <small>Total Payments</small></span>
                                <span class="d-flex align-items-center gap-1"><span style="width: 12px; height: 12px; background: #10b981; border-radius: 4px;"></span>
                                    <small>Institute Income</small></span>
                            </div>
                            <select id="yearSelector" class="year-selector">
                                @for($y = 2022; $y <= now()->year; $y++)
                                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="chart-container" style="position: relative;">
                        <canvas id="yearlyPaymentChart" style="width: 100%; height: 400px;"></canvas>
                        <div id="chartLoading" class="chart-loading" style="display: none;">
                            <div class="spinner"></div>
                        </div>
                    </div>
                    <div class="chart-stats" id="chartStats">
                        <div class="chart-stat-item">
                            <div class="chart-stat-label">Total Revenue</div>
                            <div class="chart-stat-value total" id="totalRevenue">Rs. 0.00</div>
                        </div>
                        <div class="chart-stat-item">
                            <div class="chart-stat-label">Institute Income</div>
                            <div class="chart-stat-value total" id="instituteIncome">Rs. 0.00</div>
                        </div>
                        <div class="chart-stat-item">
                            <div class="chart-stat-label">Best Month</div>
                            <div class="chart-stat-value" id="bestMonth">-</div>
                        </div>
                        <div class="chart-stat-item">
                            <div class="chart-stat-label">Annual Growth</div>
                            <div class="chart-stat-value" id="growthRate">0%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EXPIRING STUDENTS SECTION --}}
        @if(($expiringStudentsCount ?? 0) > 0)
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="dashboard-card expiring-card p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                            <div>
                                <h5 class="section-title text-danger mb-1">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Temporary QR Codes Expiring Soon
                                </h5>
                                <p class="text-muted mb-0">Students whose temporary QR codes will expire within the next 10 days.</p>
                            </div>
                            <span class="badge badge-critical rounded-pill px-3 py-2 fs-6">
                                <i class="bi bi-clock-history me-1"></i> {{ $expiringStudentsCount }} Students Expiring
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table mini-table table-hover align-middle mb-0">
                                <thead>
                                    <tr><th>Student ID</th><th>QR Code</th><th>Student Name</th><th>Guardian Mobile</th><th>Expire Date</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($expiringStudents as $student)
                                        @php $daysLeft = max(0, now()->diffInDays($student->temporary_qr_code_expire_date, false)); @endphp
                                        <tr>
                                            <td>{{ $student->id }}</td>
                                            <td><code class="bg-light px-2 py-1 rounded">{{ $student->temporary_qr_code ?? 'N/A' }}</code></td>
                                            <td class="fw-semibold">{{ $student->initial_name ?? '-' }}</td>
                                            <td>{{ $student->guardian_mobile ?? '-' }}</td>
                                            <td>{{ optional($student->temporary_qr_code_expire_date)->format('d M Y') }}</td>
                                            <td>@if($daysLeft <= 3)<span class="badge badge-critical">{{ $daysLeft }} Days Left (Critical)</span>@elseif($daysLeft <= 7)<span class="badge badge-warning-custom">{{ $daysLeft }} Days Left</span>@else<span class="badge bg-primary">{{ $daysLeft }} Days Left</span>@endif</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 renew-btn"
                                                    data-bs-toggle="modal" data-bs-target="#renewNoticeModal"
                                                    data-student-id="{{ $student->id }}"
                                                    data-student-name="{{ $student->initial_name ?? 'Student' }}"
                                                    data-renew-url="{{ route('admin.students.edit', $student->id) }}">
                                                    <i class="bi bi-qr-code me-1"></i> Renew
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Renew Notice Modal --}}
        <div class="modal fade" id="renewNoticeModal" tabindex="-1" aria-labelledby="renewNoticeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="renewNoticeModalLabel">Renewal Notice</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">You are about to renew the temporary ID card for: <strong id="renewStudentName">Student</strong></p>
                        <div class="alert alert-warning mb-0">
                            If the child's permanent ID card has not been issued yet, or if you notice any problem during renewal,
                            please email <strong>admin@nexorait.lk</strong> or send a WhatsApp message to <strong>0766499254</strong>.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="#" id="renewContinueBtn" class="btn btn-primary">Continue</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- THIRD ROW --}}
        <div class="row g-4">
            <div class="col-xl-4">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted fw-semibold">Temporary ID Cards</small>
                            <h2 class="fw-bold mt-2 mb-3">{{ $temporaryIdCardPendingCount ?? 0 }}</h2>
                            @if($showTemporaryIdCardWarning ?? false)
                                <span class="badge-soft badge-low"><i class="bi bi-exclamation-triangle me-1"></i>Low Stock</span>
                            @else
                                <span class="badge-soft badge-good"><i class="bi bi-check-lg me-1"></i>Enough Stock</span>
                            @endif
                        </div>
                        <div class="stat-icon bg-dark-soft"><i class="bi bi-person-vcard-fill"></i></div>
                    </div>
                    @if(($temporaryIdCardPendingCount ?? 0) < 20)
                        <div class="mt-3">
                            <div class="progress rounded-pill" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: {{ min(100, ($temporaryIdCardPendingCount ?? 0) / 50 * 100) }}%"></div>
                            </div>
                            <small class="text-muted">Reorder when stock reaches 20</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-xl-8">
                <div class="dashboard-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="section-title mb-0">Incomplete Registrations</h5>
                        <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-hourglass-split me-1"></i> {{ $incompleteRegistrationCount ?? 0 }} Pending</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table mini-table table-hover align-middle mb-0">
                            <thead><tr><th>Custom ID</th><th>Name</th><th>Temporary QR</th><th>Guardian Mobile</th><th>Action</th></tr></thead>
                            <tbody>
                                @forelse($incompleteRegistrations ?? [] as $card)
                                    <tr>
                                        <td>{{ $card->student->custom_id ?? '-' }}</td>
                                        <td>{{ $card->student->initial_name ?? '-' }}</td>
                                        <td>{{ $card->student->temporary_qr_code ?? '-' }}</td>
                                        <td>{{ $card->student->guardian_mobile ?? '-' }}</td>
                                        <td><a href="{{ route('admin.students.edit', $card->student->id ?? 0) }}" class="btn btn-sm btn-primary rounded-pill px-3">Complete</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-check-circle fs-2 d-block mb-2"></i>No incomplete registrations found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="dashboard-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="section-title mb-0">Latest Students</h5>
                        <span class="badge bg-primary rounded-pill">Recent</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table mini-table table-hover align-middle mb-0">
                            <thead><tr><th>Custom ID</th><th>Name</th><th>Mobile</th><th>Joined</th><th>QR Status</th></tr></thead>
                            <tbody>
                                @forelse($latestStudents ?? [] as $student)
                                    <tr>
                                        <td>{{ $student->custom_id ?? '-' }}</td>
                                        <td>{{ $student->initial_name ?? '-' }}</td>
                                        <td>{{ $student->guardian_mobile ?? '-' }}</td>
                                        <td>{{ optional($student->created_at)->format('d M Y') }}</td>
                                        <td>@if($student->temporary_qr_code_expire_date && now()->diffInDays($student->temporary_qr_code_expire_date, false) <= 10)<span class="badge bg-warning text-dark">Expiring Soon</span>@else<span class="badge bg-success">Active</span>@endif</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No students found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            // Navigation handler for buttons
            document.querySelectorAll('button[data-href]').forEach(btn => {
                btn.addEventListener('click', function() {
                    const href = this.getAttribute('data-href');
                    if (href && href !== '#') window.location.href = href;
                });
            });

            // Renew modal handler
            const renewBtns = document.querySelectorAll('.renew-btn');
            const renewContinue = document.getElementById('renewContinueBtn');
            const renewNameSpan = document.getElementById('renewStudentName');

            renewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const name = this.getAttribute('data-student-name');
                    const url = this.getAttribute('data-renew-url');
                    if (renewNameSpan) renewNameSpan.textContent = name || 'Student';
                    if (renewContinue) renewContinue.href = url || '#';
                });
            });

            // Chart logic
            let yearlyChart = null;

            function formatCurrency(amount) {
                return 'Rs. ' + parseFloat(amount).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function showLoading(show) {
                const loader = document.getElementById('chartLoading');
                if (loader) loader.style.display = show ? 'flex' : 'none';
            }

            function updateStats(totalData, instituteData, labels) {
                const totalRevenue = totalData.reduce((a, b) => a + b, 0);
                const instituteRevenue = instituteData.reduce((a, b) => a + b, 0);
                let maxTotal = 0, maxIndex = 0;
                for (let i = 0; i < totalData.length; i++) {
                    if (totalData[i] > maxTotal) { maxTotal = totalData[i]; maxIndex = i; }
                }
                const firstHalf = totalData.slice(0, 6).reduce((a, b) => a + b, 0);
                const secondHalf = totalData.slice(6, 12).reduce((a, b) => a + b, 0);
                const growthRate = firstHalf > 0 ? ((secondHalf - firstHalf) / firstHalf * 100).toFixed(1) : 0;

                document.getElementById('totalRevenue').innerHTML = formatCurrency(totalRevenue);
                document.getElementById('instituteIncome').innerHTML = formatCurrency(instituteRevenue);
                document.getElementById('bestMonth').innerHTML = `${labels[maxIndex]} (${formatCurrency(maxTotal)})`;
                const growthElem = document.getElementById('growthRate');
                growthElem.innerHTML = (growthRate >= 0 ? '+' : '') + growthRate + '%';
                growthElem.style.color = growthRate >= 0 ? '#10b981' : '#ef4444';
            }

            function loadYearlyReport(year) {
                showLoading(true);
                fetch(`{{ route('admin.institute-yearly-report') }}?year=${year}`)
                    .then(response => response.json())
                    .then(result => {
                        if (!result.success) throw new Error(result.message);
                        const canvas = document.getElementById('yearlyPaymentChart');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        if (yearlyChart) yearlyChart.destroy();

                        const gradientTotal = ctx.createLinearGradient(0, 0, 0, 400);
                        gradientTotal.addColorStop(0, 'rgba(37, 99, 235, 0.5)');
                        gradientTotal.addColorStop(1, 'rgba(37, 99, 235, 0.02)');

                        const gradientInstitute = ctx.createLinearGradient(0, 0, 0, 400);
                        gradientInstitute.addColorStop(0, 'rgba(16, 185, 129, 0.5)');
                        gradientInstitute.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

                        yearlyChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: result.labels,
                                datasets: [{
                                    label: 'Total Payments',
                                    data: result.total_payments,
                                    borderColor: '#2563eb',
                                    backgroundColor: gradientTotal,
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#2563eb',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                }, {
                                    label: 'Institute Income',
                                    data: result.institution_payments,
                                    borderColor: '#10b981',
                                    backgroundColor: gradientInstitute,
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#10b981',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { intersect: false, mode: 'index' },
                                plugins: {
                                    legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } },
                                    tooltip: {
                                        backgroundColor: '#0f172a',
                                        callbacks: { label: (ctx) => `${ctx.dataset.label}: Rs. ${ctx.raw.toLocaleString('en-LK', { minimumFractionDigits: 2 })}` }
                                    }
                                },
                                scales: {
                                    y: { beginAtZero: true, ticks: { callback: (val) => 'Rs. ' + val.toLocaleString() } }
                                }
                            }
                        });
                        updateStats(result.total_payments, result.institution_payments, result.labels);
                        showLoading(false);
                    })
                    .catch(() => { showLoading(false); });
            }

            const yearSelector = document.getElementById('yearSelector');
            if (yearSelector) {
                loadYearlyReport(yearSelector.value);
                yearSelector.addEventListener('change', () => loadYearlyReport(yearSelector.value));
            }
        })();
    </script>
@endpush