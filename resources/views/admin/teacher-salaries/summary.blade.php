@extends('layouts.app')

@section('title', 'Class-wise Payment Summary')
@section('page-title', 'Class-wise Payment Summary')

@section('content')

    @php
        $teacherTotal = $data['teacher_total'] ?? 0;
        $totalStudentCount = $data['total_student_count'] ?? 0;
        $totalClassCount = $data['total_class_count'] ?? 0;
        $reportMonthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
    @endphp

    <div class="class-payment-summary-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">

                <div>
                    <a href="{{ route('admin.teacher-salaries.show', [$teacherId, $year, $month]) }}"
                        class="btn btn-outline-secondary custom-btn mb-3">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Salary Report
                    </a>

                    <h3 class="fw-bold mb-1">
                        Class-wise Payment Summary
                    </h3>

                    <p class="text-muted mb-0">
                        Payment details for {{ $reportMonthName }}
                    </p>
                </div>

                <div class="hero-actions">
                    <button onclick="window.print()" class="btn btn-primary custom-btn">
                        <i class="bi bi-printer me-1"></i>
                        Print Report
                    </button>
                </div>

            </div>
        </div>

        <!-- STATS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-cash-stack summary-icon"></i>
                        <h6>Teacher Total</h6>
                        <h3>LKR {{ number_format($teacherTotal, 2) }}</h3>
                        <small class="summary-sub">Teacher income for
                            {{ \Carbon\Carbon::create($year, $month, 1)->format('F') }}</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-people-fill summary-icon"></i>
                        <h6>Total Students</h6>
                        <h3>{{ number_format($totalStudentCount) }}</h3>
                        <small class="summary-sub">Across {{ $totalClassCount }} classes</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-orange">
                    <div class="card-body">
                        <i class="bi bi-grid-3x3-gap-fill summary-icon"></i>
                        <h6>Total Classes</h6>
                        <h3>{{ number_format($totalClassCount) }}</h3>
                        <small class="summary-sub">Active classes</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-primary">
                    <div class="card-body">
                        <i class="bi bi-calendar-event summary-icon"></i>
                        <h6>Reporting Month</h6>
                        <h3>{{ \Carbon\Carbon::create($year, $month, 1)->format('M Y') }}</h3>
                        <small class="summary-sub">Payment summary</small>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <div class="main-card-header">
                <div>
                    <h4>Class Breakdown</h4>
                    <p>Monthly payment summary by class and category</p>
                </div>

                <div class="header-badge">
                    {{ count($data['classes'] ?? []) }} Classes
                </div>
            </div>

            <div class="row g-4" id="classesGrid">
                @forelse($data['classes'] ?? [] as $class)
                    <div class="col-md-6 col-lg-4">
                        <div class="class-card h-100">

                            <div class="class-card-header">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $class['class_name'] }}</h5>

                                    @if(!empty($class['grade_name']))
                                        <div class="text-muted fw-semibold">
                                            Grade: {{ $class['grade_name'] }}
                                        </div>
                                    @endif

                                    <p class="text-muted small mb-0 mt-1">
                                        <i class="bi bi-people-fill me-1"></i>
                                        Total Students: {{ $class['total_students'] }}
                                    </p>
                                </div>

                                <span class="badge bg-primary custom-badge">
                                    LKR {{ number_format($class['class_total'], 2) }}
                                </span>
                            </div>

                            @if(!empty($class['categories']) && count($class['categories']) > 0)
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2 fw-semibold">Categories Breakdown</small>

                                    <div class="row g-2">
                                        @foreach($class['categories'] as $category)
                                                            <div class="col-12">
                                                                <div class="category-item">

                                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                                        <strong class="small">
                                                                            {{ $category['category_name'] }}
                                                                        </strong>

                                                                        <span class="badge bg-secondary custom-badge">
                                                                            {{ $category['student_count'] }} Students
                                                                        </span>
                                                                    </div>

                                                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                                                        @if($category['paid_students'] > 0)
                                                                            <span class="badge bg-success custom-badge">
                                                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                                                Paid: {{ $category['paid_students'] }}
                                                                            </span>
                                                                        @endif

                                                                        @if($category['partial_students'] > 0)
                                                                            <span class="badge bg-warning text-dark custom-badge">
                                                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                                                Partial: {{ $category['partial_students'] }}
                                                                            </span>
                                                                        @endif

                                                                        @if($category['free_card_students'] > 0)
                                                                            <span class="badge bg-info custom-badge">
                                                                                <i class="bi bi-gift-fill me-1"></i>
                                                                                Free: {{ $category['free_card_students'] }}
                                                                            </span>
                                                                        @endif

                                                                        @if($category['unpaid_students'] > 0)
                                                                            <span class="badge bg-danger custom-badge">
                                                                                <i class="bi bi-x-circle-fill me-1"></i>
                                                                                Unpaid: {{ $category['unpaid_students'] }}
                                                                            </span>
                                                                        @endif
                                                                    </div>

                                                                    <div class="d-flex justify-content-between small text-muted">
                                                                        <span>
                                                                            Category Fee: LKR {{ number_format($category['fee'], 2) }}
                                                                        </span>
                                                                        <span class="text-success fw-semibold">
                                                                            Collected: LKR {{ number_format($category['category_total'], 2) }}
                                                                        </span>
                                                                    </div>

                                                                    @php
                                                                        $expectedTotal = $category['fee'] * $category['student_count'];
                                                                        $collectedPercent = $expectedTotal > 0
                                                                            ? ($category['category_total'] / $expectedTotal) * 100
                                                                            : 0;
                                                                    @endphp

                                                                    <div class="progress mt-2" style="height: 6px;">
                                                                        <div class="progress-bar bg-success" role="progressbar"
                                                                            style="width: {{ $collectedPercent }}%"></div>
                                                                    </div>

                                                                    <div class="mt-3">
                                                                        <a href="{{ route('admin.student-class-enrollments.category-wise-payment', [
                                                'class' => $class['class_id'],
                                                'classCategoryFee' => $category['category_fee_id'],
                                                'year' => $year,
                                                'month' => $month
                                            ]) }}" class="btn btn-outline-info btn-sm w-100 custom-btn">
                                                                            <i class="bi bi-eye me-1"></i>
                                                                            View {{ $category['category_name'] }} Students
                                                                        </a>
                                                                    </div>

                                                                </div>
                                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-light text-center py-3 mb-3">
                                    <i class="bi bi-folder2-open me-1"></i>
                                    <small class="text-muted d-block">No categories assigned</small>
                                </div>
                            @endif

                            @php
                                $totalPaid = collect($class['categories'])->sum('paid_students');
                                $totalPartial = collect($class['categories'])->sum('partial_students');
                                $totalWithPayment = $totalPaid + $totalPartial;
                                $paidPercent = $class['total_students'] > 0
                                    ? ($totalWithPayment / $class['total_students']) * 100
                                    : 0;
                            @endphp

                            <div class="class-footer">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-semibold">Overall Collection Rate</span>
                                    <span class="fw-semibold">{{ number_format($paidPercent, 1) }}%</span>
                                </div>

                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $paidPercent }}%">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between small mt-2 flex-wrap gap-2">
                                    <span class="text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Paid: {{ $totalPaid }}
                                    </span>
                                    <span class="text-warning">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Partial: {{ $totalPartial }}
                                    </span>
                                    <span class="text-danger">
                                        <i class="bi bi-x-circle-fill me-1"></i>
                                        Unpaid: {{ $class['total_students'] - $totalWithPayment }}
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-card">
                            <i class="bi bi-inbox"></i>
                            <h5>No active classes found for this teacher</h5>
                            <p class="mb-0 text-muted">There are no class records available for the selected period.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- GUIDE -->
            <div class="mt-4">
                <div class="alert alert-info custom-alert mb-0">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Payment Status Guide:</strong>
                    <div class="row mt-3 g-3">
                        <div class="col-md-3">
                            <span class="badge bg-success custom-badge">✓ Paid</span>
                            <small class="d-block text-muted mt-1">Full payment completed</small>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-warning text-dark custom-badge">⚠ Partial</span>
                            <small class="d-block text-muted mt-1">Partial payment received</small>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-info custom-badge">🎫 Free Card</span>
                            <small class="d-block text-muted mt-1">Free student (no fee)</small>
                        </div>
                        <div class="col-md-3">
                            <span class="badge bg-danger custom-badge">✗ Unpaid</span>
                            <small class="d-block text-muted mt-1">No payment received</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <style>
        .class-payment-summary-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card,
        .empty-card {
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

        .summary-green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .summary-orange {
            background: linear-gradient(135deg, #ea580c, #f97316);
        }

        .summary-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .main-card {
            padding: 1.5rem;
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
            padding: .55rem .9rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
        }

        .class-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .04);
            transition: .2s ease;
            padding: 1.25rem;
            height: 100%;
        }

        .class-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .08);
        }

        .class-card-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .category-item {
            transition: all 0.2s ease;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: .85rem;
        }

        .category-item:hover {
            background-color: #eef2f7;
            transform: translateX(2px);
        }

        .progress {
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .class-footer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eef2f7;
        }

        .empty-card {
            text-align: center;
            padding: 4rem 1rem;
        }

        .empty-card i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-card h5 {
            font-weight: 700;
        }

        .custom-alert {
            border-radius: 18px;
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

            .class-card-header {
                flex-direction: column;
                align-items: stretch;
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
            .main-card,
            .class-card,
            .empty-card {
                box-shadow: none !important;
            }
        }
    </style>
@endpush