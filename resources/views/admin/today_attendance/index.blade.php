@extends('layouts.app')

@section('title', "Today's Class Attendance")
@section('page-title', "Today's Class Attendance")

@section('content')
    @php
        $todayDate = \Carbon\Carbon::parse($today);
        $dayName = $todayDate->format('l');
        $presentCount = collect($students)->where('status', 'present')->count();
        $absentCount = collect($students)->where('status', 'absent')->count();
        $notEnrolledCount = isset($notEnrolledAttendances) ? $notEnrolledAttendances->count() : 0;
        $attendancePercentage = count($students) > 0 ? round(($presentCount / count($students)) * 100, 1) : 0;
    @endphp

    <div class="attendance-page">

        {{-- HERO CARD --}}
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <div class="hero-badge mb-3">
                        <i class="bi bi-calendar-check-fill"></i>
                        Today's Attendance
                    </div>
                    <h2 class="fw-bold mb-2">Class Attendance Tracker</h2>
                    <p class="mb-0 text-light-soft">
                        {{ $todayDate->format('l, d F Y') }} | {{ ucfirst($dayName) }}
                    </p>
                </div>
                <div class="hero-actions">
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="stats-info">
                        <h3>{{ count($students) }}</h3>
                        <p>Enrolled Students</p>
                        <small class="text-muted">Total registered</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stats-info">
                        <h3>{{ $presentCount }}</h3>
                        <p>Present</p>
                        <small class="text-muted">{{ $attendancePercentage }}% of total</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon red">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="stats-info">
                        <h3>{{ $absentCount }}</h3>
                        <p>Absent</p>
                        <small class="text-muted">{{ 100 - $attendancePercentage }}% of total</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-person-exclamation"></i>
                    </div>
                    <div class="stats-info">
                        <h3>{{ $notEnrolledCount }}</h3>
                        <p>Not Enrolled</p>
                        <small class="text-muted">Unregistered attendance</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- PROGRESS BAR SECTION --}}
        <div class="progress-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="progress-label">Attendance Rate</span>
                <span class="progress-percentage">{{ $attendancePercentage }}%</span>
            </div>
            <div class="progress attendance-progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $attendancePercentage }}%"
                    aria-valuenow="{{ $attendancePercentage }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted"><i class="bi bi-check-circle-fill text-success me-1"></i> Present:
                    {{ $presentCount }}</small>
                <small class="text-muted"><i class="bi bi-x-circle-fill text-danger me-1"></i> Absent:
                    {{ $absentCount }}</small>
            </div>
        </div>

        {{-- MAIN CARD --}}
        <div class="main-card">
            <div class="main-card-header">
                <div>
                    <h4>
                        <i class="bi bi-book-fill me-2"></i>
                        {{ $schedule->studentClass->class_name ?? 'Class Schedule' }}
                    </h4>
                    <p class="mb-0">
                        <i class="bi bi-tag me-1"></i> Fee ID: {{ $schedule->classCategoryFee->id ?? '-' }} &nbsp;|&nbsp;
                        <i class="bi bi-building me-1"></i> Hall: {{ $schedule->hall->hall_name ?? '-' }} &nbsp;|&nbsp;
                        <i class="bi bi-clock me-1"></i> {{ $todayDate->format('h:i A') }}
                    </p>
                </div>
                <div class="class-badge">
                    <i class="bi bi-calendar-week me-1"></i>
                    {{ ucfirst($dayName) }}
                </div>
            </div>

            {{-- ENROLLED STUDENTS TABLE --}}
            <div class="table-responsive">
                <table class="custom-table align-middle">
                    <thead>
                        <tr>
                            <th><i class="bi bi-person-badge me-1"></i> Student</th>
                            <th><i class="bi bi-upc-scan me-1"></i> Enrollment ID</th>
                            <th><i class="bi bi-flag me-1"></i> Status</th>
                            <th><i class="bi bi-receipt me-1"></i> Attendance ID</th>
                            <th><i class="bi bi-chat-text me-1"></i> Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $row)
                            <tr>
                                <td class="fw-semibold">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="student-avatar">
                                            {{ strtoupper(substr($row['student']->full_name ?? $row['student']->name ?? 'S', 0, 1)) }}
                                        </div>
                                        {{ $row['student']->full_name ?? $row['student']->name ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <code class="enrollment-code">#{{ $row['enrollment']->id }}</code>
                                </td>
                                <td>
                                    @if($row['status'] === 'present')
                                        <span class="badge-present">
                                            <i class="bi bi-check-circle-fill me-1"></i> Present
                                        </span>
                                    @else
                                        <span class="badge-absent">
                                            <i class="bi bi-x-circle-fill me-1"></i> Absent
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($row['attendance']->id ?? false)
                                        <code class="attendance-code">{{ $row['attendance']->id }}</code>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $row['attendance']->note ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No Enrolled Students</h5>
                                    <p class="text-muted">No enrolled students found for this schedule.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- NOT ENROLLED ATTENDANCES SECTION --}}
            @if($notEnrolledCount > 0)
                <div class="mt-4 not-enrolled-section">
                    <div class="not-enrolled-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-exclamation me-2"></i>
                            Not Enrolled Attendance Records
                        </h5>
                        <span class="badge-not-enrolled">{{ $notEnrolledCount }} Records</span>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="custom-table align-middle">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-receipt me-1"></i> Attendance ID</th>
                                    <th><i class="bi bi-person me-1"></i> Student ID</th>
                                    <th><i class="bi bi-flag me-1"></i> Record Status</th>
                                    <th><i class="bi bi-chat-text me-1"></i> Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notEnrolledAttendances as $attendance)
                                    <tr>
                                        <td>
                                            <code class="attendance-code">{{ $attendance->id }}</code>
                                        </td>
                                        <td>{{ $attendance->student_id ?? '-' }}</td>
                                        <td>
                                            <span class="badge-not-enrolled-status">
                                                <i class="bi bi-question-circle me-1"></i> Not Enrolled
                                            </span>
                                        </td>
                                        <td>{{ $attendance->note ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- FOOTER NOTE --}}
        <div class="footer-note mt-4 text-center">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                Attendance recorded on {{ $todayDate->format('l, d F Y') }} at {{ now()->format('h:i A') }}
            </small>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .attendance-page {
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

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .text-light-soft {
            color: rgba(255, 255, 255, .78);
        }

        /* Stats Cards */
        .stats-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .stats-card:hover {
            transform: translateY(-3px);
            border-color: #2563eb;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .orange {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .stats-info h3 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
        }

        .stats-info p {
            margin: 0;
            font-weight: 600;
            color: #475569;
        }

        .stats-info small {
            font-size: 0.7rem;
        }

        /* Progress Card */
        .progress-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .progress-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        .progress-percentage {
            font-size: 0.9rem;
            font-weight: 800;
            color: #10b981;
        }

        .attendance-progress {
            height: 8px;
            border-radius: 10px;
            background: #e2e8f0;
        }

        .attendance-progress .progress-bar {
            border-radius: 10px;
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        /* Main Card */
        .main-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eef2f7;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 800;
            color: #0f172a;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
            font-size: 0.85rem;
        }

        .class-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Table Styles */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
            white-space: nowrap;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 0.85rem;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .custom-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Student Avatar */
        .student-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
        }

        /* Badges */
        .badge-present {
            background: #dcfce7;
            color: #166534;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-absent {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-not-enrolled {
            background: #fef3c7;
            color: #92400e;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-not-enrolled-status {
            background: #f1f5f9;
            color: #475569;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Code Styles */
        .enrollment-code,
        .attendance-code {
            background: #f1f5f9;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-family: monospace;
            color: #475569;
        }

        /* Empty State */
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

        /* Not Enrolled Section */
        .not-enrolled-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #f1f5f9;
        }

        .not-enrolled-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        /* Footer Note */
        .footer-note {
            padding: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }

            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .stats-info h3 {
                font-size: 1.2rem;
            }

            .main-card {
                padding: 1rem;
            }

            .main-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .custom-table thead th {
                font-size: 0.65rem;
                padding: 0.5rem;
            }

            .custom-table tbody td {
                padding: 0.5rem;
                font-size: 0.75rem;
            }

            .student-avatar {
                width: 25px;
                height: 25px;
                font-size: 0.65rem;
            }
        }
    </style>
@endpush