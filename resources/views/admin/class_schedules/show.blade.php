@extends('layouts.app')

@section('title', 'Class Schedule Details')
@section('page-title', 'Class Schedule Details')

@section('content')

    @php
        $studentClass = $classSchedule->studentClass ?? null;
        $category = $classSchedule->category ?? null;
        $hall = $classSchedule->hall ?? null;
        $cancelledBy = $classSchedule->cancelledBy ?? null;

        $className = $studentClass->class_name ?? '-';
        $gradeName = $studentClass->grade->grade_name ?? 'N/A';
        $subjectName = $studentClass->subject->subject_name ?? 'N/A';
        $teacherName = $studentClass->teacher->full_name ?? 'N/A';

        $categoryName = $category->category_name ?? '-';
        $categoryCode = $category->code ?? '-';
        $hallName = $hall->hall_name ?? '-';

        $classDate = $classSchedule->class_date
            ? \Carbon\Carbon::parse($classSchedule->class_date)->format('Y-m-d')
            : '-';

        $createdAt = $classSchedule->created_at
            ? \Carbon\Carbon::parse($classSchedule->created_at)->format('Y-m-d h:i A')
            : '-';

        $cancelledAt = $classSchedule->cancelled_at
            ? \Carbon\Carbon::parse($classSchedule->cancelled_at)->format('Y-m-d h:i A')
            : '-';

        $startTime = $classSchedule->start_time
            ? \Carbon\Carbon::parse($classSchedule->start_time)->format('h:i A')
            : '-';

        $endTime = $classSchedule->end_time
            ? \Carbon\Carbon::parse($classSchedule->end_time)->format('h:i A')
            : '-';

        $cancelledByName = $cancelledBy->name ?? '-';
        $cancelReason = $classSchedule->cancel_reason ?? '-';
        $note = $classSchedule->note ?? '-';

        $statusClass = 'bg-secondary';

        if ($classSchedule->status === 'scheduled') {
            $statusClass = 'bg-primary';
        } elseif ($classSchedule->status === 'ongoing') {
            $statusClass = 'bg-warning text-dark';
        } elseif ($classSchedule->status === 'completed') {
            $statusClass = 'bg-success';
        } elseif ($classSchedule->status === 'cancelled') {
            $statusClass = 'bg-danger';
        }
    @endphp

    <div class="schedule-details-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">

                <div>
                    <h3 class="fw-bold mb-1">Class Schedule Details</h3>
                    <p class="text-muted mb-0">
                        {{ $className }} / {{ $categoryName }}
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.class-schedules.edit', $classSchedule) }}" class="btn btn-warning custom-btn">
                        <i class="bi bi-pencil-square me-1"></i>
                        Edit
                    </a>

                    <a href="{{ route('admin.class-schedules.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>

                    <button type="button" onclick="window.print()" class="btn btn-outline-primary custom-btn">
                        <i class="bi bi-printer me-1"></i>
                        Print
                    </button>
                </div>

            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success custom-alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger custom-alert">
                {{ session('error') }}
            </div>
        @endif

        <!-- SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-blue">
                    <div class="card-body">
                        <i class="bi bi-journal-bookmark-fill summary-icon"></i>
                        <h6>Class</h6>
                        <h3>{{ $className }}</h3>
                        <small class="summary-sub">{{ $gradeName }} · {{ $subjectName }}</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-green">
                    <div class="card-body">
                        <i class="bi bi-person-badge-fill summary-icon"></i>
                        <h6>Teacher</h6>
                        <h3>{{ $teacherName }}</h3>
                        <small class="summary-sub">{{ $categoryName }}</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-orange">
                    <div class="card-body">
                        <i class="bi bi-calendar-event summary-icon"></i>
                        <h6>Date</h6>
                        <h3>{{ $classDate }}</h3>
                        <small class="summary-sub">{{ ucfirst($classSchedule->day_of_week) }}</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="summary-card summary-red">
                    <div class="card-body">
                        <i class="bi bi-shield-check summary-icon"></i>
                        <h6>Status</h6>
                        <h3>
                            <span class="badge {{ $statusClass }} custom-badge">
                                {{ ucfirst($classSchedule->status) }}
                            </span>
                        </h3>
                        <small class="summary-sub">
                            {{ $classSchedule->is_active ? 'Active' : 'Inactive' }}
                        </small>
                    </div>
                </div>
            </div>

        </div>

        <!-- DETAILS -->
        <div class="main-card">

            <div class="main-card-header">
                <div>
                    <h4 class="mb-1 fw-bold">Schedule Information</h4>
                    <p class="mb-0 text-muted">
                        Full schedule configuration and record details
                    </p>
                </div>

                <div class="header-badge">
                    Record #{{ $classSchedule->id }}
                </div>
            </div>

            <div class="row g-4">

                <!-- CLASS INFORMATION -->
                <div class="col-lg-6">
                    <div class="detail-card h-100">
                        <h5 class="section-title">
                            <i class="bi bi-book me-2"></i>
                            Class Information
                        </h5>

                        <div class="detail-item">
                            <span class="detail-label">Class</span>
                            <span class="detail-value">{{ $className }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Grade</span>
                            <span class="detail-value">{{ $gradeName }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Subject</span>
                            <span class="detail-value">{{ $subjectName }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Teacher</span>
                            <span class="detail-value">{{ $teacherName }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Category</span>
                            <span class="detail-value">{{ $categoryName }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Category Code</span>
                            <span class="detail-value">{{ $categoryCode }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Hall</span>
                            <span class="detail-value">{{ $hallName }}</span>
                        </div>
                    </div>
                </div>

                <!-- SCHEDULE INFORMATION -->
                <div class="col-lg-6">
                    <div class="detail-card h-100">
                        <h5 class="section-title">
                            <i class="bi bi-calendar2-week me-2"></i>
                            Schedule Information
                        </h5>

                        <div class="detail-item">
                            <span class="detail-label">Date</span>
                            <span class="detail-value">{{ $classDate }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Day</span>
                            <span class="detail-value">{{ ucfirst($classSchedule->day_of_week) }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Time</span>
                            <span class="detail-value">{{ $startTime }} - {{ $endTime }}</span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="detail-value">
                                <span class="badge {{ $statusClass }} custom-badge">
                                    {{ ucfirst($classSchedule->status) }}
                                </span>
                            </span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Active</span>
                            <span class="detail-value">
                                <span
                                    class="badge {{ $classSchedule->is_active ? 'bg-success' : 'bg-secondary' }} custom-badge">
                                    {{ $classSchedule->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </span>
                        </div>

                        <div class="detail-item">
                            <span class="detail-label">Created At</span>
                            <span class="detail-value">{{ $createdAt }}</span>
                        </div>
                    </div>
                </div>

                <!-- CANCEL DETAILS -->
                @if($classSchedule->status === 'cancelled')
                    <div class="col-12">
                        <div class="detail-card">
                            <h5 class="section-title">
                                <i class="bi bi-x-circle me-2"></i>
                                Cancel Details
                            </h5>

                            <div class="row g-3">
                                <div class="col-lg-4 col-md-6">
                                    <div class="detail-item">
                                        <span class="detail-label">Reason</span>
                                        <span class="detail-value">{{ $cancelReason }}</span>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <div class="detail-item">
                                        <span class="detail-label">Cancelled By</span>
                                        <span class="detail-value">{{ $cancelledByName }}</span>
                                    </div>
                                </div>

                                <div class="col-lg-4 col-md-6">
                                    <div class="detail-item">
                                        <span class="detail-label">Cancelled At</span>
                                        <span class="detail-value">{{ $cancelledAt }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- NOTE -->
                <div class="col-12">
                    <div class="detail-card">
                        <h5 class="section-title">
                            <i class="bi bi-chat-left-text me-2"></i>
                            Note
                        </h5>

                        <div class="note-box">
                            {{ $note }}
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection

@push('styles')
    <style>
        .schedule-details-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card,
        .detail-card,
        .summary-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
        }

        .hero-card,
        .main-card {
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

        .custom-alert {
            border-radius: 18px;
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

        .summary-red {
            background: linear-gradient(135deg, #ef4444, #f87171);
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
            font-size: .85rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: #0f172a;
        }

        .detail-card {
            padding: 1.5rem;
            height: 100%;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #64748b;
            font-weight: 600;
            min-width: 130px;
        }

        .detail-value {
            text-align: right;
            color: #0f172a;
            word-break: break-word;
        }

        .note-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 1rem;
            line-height: 1.7;
            color: #334155;
        }

        .badge {
            border-radius: 10px;
            padding: .45rem .7rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .45rem .7rem;
            font-size: .75rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {

            .hero-content,
            .main-card-header,
            .detail-item {
                flex-direction: column;
                align-items: stretch;
            }

            .detail-value {
                text-align: left;
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
            .main-card,
            .detail-card,
            .summary-card {
                box-shadow: none !important;
            }
        }
    </style>
@endpush