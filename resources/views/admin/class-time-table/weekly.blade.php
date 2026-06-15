@extends('layouts.app')

@section('title', 'Weekly Timetable')
@section('page-title', 'Weekly Timetable')

@section('content')

    <div class="weekly-timetable-page">

        <!-- STATS CARDS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                    <div>
                        <h3>{{ $schedules->count() }}</h3>
                        <p>Total Classes</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $schedules->where('status', 'completed')->count() }}</h3>
                        <p>Completed</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <h3>{{ $schedules->where('status', 'scheduled')->count() }}</h3>
                        <p>Scheduled</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon purple">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $schedules->where('status', 'ongoing')->count() }}</h3>
                        <p>Ongoing</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <!-- HEADER -->
            <div class="main-card-header">

                <div>
                    <h4>Weekly Class Schedule</h4>
                    <p>View and manage class timetable for the selected week</p>
                </div>

                <div class="header-buttons">

                    @if(isset($startOfWeek, $endOfWeek))
                        <div class="week-info">
                            <i class="bi bi-calendar-range"></i>
                            <span>{{ $startOfWeek->format('d M Y') }} - {{ $endOfWeek->format('d M Y') }}</span>
                        </div>
                    @endif

                    <a href="{{ route('admin.weekly.pdf', ['week_date' => request('week_date', $selectedDate ?? now()->toDateString())]) }}"
                        class="btn btn-danger custom-btn">
                        <i class="bi bi-file-pdf"></i>
                        PDF
                    </a>

                    <a href="{{ route('admin.weekly.excel', ['week_date' => request('week_date', $selectedDate ?? now()->toDateString())]) }}"
                        class="btn btn-success custom-btn">
                        <i class="bi bi-file-excel"></i>
                        Excel
                    </a>

                </div>

            </div>

            <!-- SEARCH & FILTER -->
            <div class="search-card">

                <form method="GET" action="{{ route('admin.weekly-timetable') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold mb-2">Select Week</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-calendar-date"></i>
                                <input type="date" name="week_date" class="form-control custom-input"
                                    value="{{ old('week_date', $selectedDate ?? now()->toDateString()) }}">
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold mb-2">Status</label>
                            <select name="status" class="form-select custom-input">
                                <option value="">All Status</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled
                                </option>
                                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold mb-2">Grade</label>
                            <select name="grade" class="form-select custom-input">
                                <option value="">All Grades</option>
                                @foreach($grades ?? [] as $grade)
                                    <option value="{{ $grade->id }}" {{ request('grade') == $grade->id ? 'selected' : '' }}>
                                        {{ $grade->grade_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold mb-2">Class Hall</label>
                            <select name="hall_id" class="form-select custom-input">
                                <option value="">All Halls</option>
                                @foreach($halls ?? [] as $hall)
                                    <option value="{{ $hall->id }}" {{ request('hall_id') == $hall->id ? 'selected' : '' }}>
                                        {{ $hall->hall_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary flex-grow-1 custom-btn" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                <a href="{{ route('admin.weekly-timetable') }}"
                                    class="btn btn-light border flex-grow-1 custom-btn">
                                    <i class="bi bi-arrow-repeat"></i> Reset
                                </a>
                            </div>
                        </div>

                    </div>
                </form>

                @if(session('error'))
                    <div class="alert alert-danger mt-3 mb-0 rounded-3 d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @error('week_date')
                    <div class="alert alert-danger mt-3 mb-0 rounded-3 d-flex align-items-center">
                        <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                        {{ $message }}
                    </div>
                @enderror

            </div>

            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table custom-table align-middle">

                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 18%">Date & Time</th>
                            <th style="width: 25%">Class Details</th>
                            <th style="width: 10%">Grade</th>
                            <th style="width: 15%">Category</th>
                            <th style="width: 10%">Fee</th>
                            <th style="width: 12%">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($schedules as $schedule)
                            @php
                                $classDate = $schedule->class_date ? Carbon\Carbon::parse($schedule->class_date) : null;
                                $startTime = $schedule->start_time ? Carbon\Carbon::parse($schedule->start_time) : null;
                                $endTime = $schedule->end_time ? Carbon\Carbon::parse($schedule->end_time) : null;

                                $statusColors = [
                                    'scheduled' => ['bg' => '#E0E7FF', 'color' => '#4338CA', 'icon' => 'bi-calendar-check'],
                                    'ongoing' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'bi-play-circle-fill'],
                                    'completed' => ['bg' => '#D1FAE5', 'color' => '#059669', 'icon' => 'bi-check-circle-fill'],
                                    'cancelled' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'bi-x-circle-fill'],
                                ];
                                $status = strtolower($schedule->status ?? 'scheduled');
                                $statusStyle = $statusColors[$status] ?? ['bg' => '#F3F4F6', 'color' => '#6B7280', 'icon' => 'bi-question-circle'];
                            @endphp

                            <tr>
                                <td class="fw-semibold">{{ $loop->iteration }}</td>

                                <!-- DATE & TIME -->
                                <td>
                                    <div class="date-time-cell">
                                        <div class="date">
                                            <i class="bi bi-calendar3"></i>
                                            <span>{{ $classDate ? $classDate->format('d M Y') : '-' }}</span>
                                        </div>
                                        <div class="time">
                                            <i class="bi bi-clock"></i>
                                            <span>
                                                {{ $startTime ? $startTime->format('h:i A') : '-' }} -
                                                {{ $endTime ? $endTime->format('h:i A') : '-' }}
                                            </span>
                                        </div>
                                        <div class="day">
                                            {{ $classDate ? $classDate->format('l') : '' }}
                                        </div>
                                    </div>
                                </td>

                                <!-- CLASS DETAILS (with Hall info inside) -->
                                <td>
                                    <div class="class-info">
                                        <div class="class-avatar">
                                            {{ strtoupper(substr($schedule->studentClass->class_name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="class-name">
                                                {{ $schedule->studentClass->class_name ?? '-' }}
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-easel"></i>
                                                {{ $schedule->Hall->hall_name ?? $schedule->classHall->hall_name ?? 'No Hall Assigned' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- GRADE -->
                                <td>
                                    @php
                                        $gradeColors = [
                                            '1' => 'grade-1',
                                            '2' => 'grade-2',
                                            '3' => 'grade-3',
                                            '4' => 'grade-4',
                                            '5' => 'grade-5',
                                            '6' => 'grade-6'
                                        ];
                                        $gradeName = $schedule->studentClass->grade->grade_name ?? '-';
                                        $gradeNum = preg_replace('/[^0-9]/', '', $gradeName);
                                        $gradeClass = $gradeColors[$gradeNum] ?? 'grade-default';
                                    @endphp
                                    <span class="grade-badge {{ $gradeClass }}">
                                        <i class="bi bi-star-fill"></i>
                                        {{ $gradeName }}
                                    </span>
                                </td>

                                <!-- CATEGORY -->
                                <td>
                                    <span class="category-badge">
                                        <i class="bi bi-tag-fill"></i>
                                        {{ $schedule->classCategoryFee->category->category_name ?? '-' }}
                                    </span>
                                </td>

                                <!-- FEE -->
                                <td>
                                    @php
                                        $fee = $schedule->classCategoryFee->fee ?? 0;
                                    @endphp
                                    @if($fee > 0)
                                        <div class="fee-amount">
                                            <span class="currency">LKR</span>
                                            {{ number_format($fee, 2) }}
                                        </div>
                                    @else
                                        <span class="free-badge">
                                            <i class="bi bi-gift-fill"></i> Free
                                        </span>
                                    @endif
                                </td>

                                <!-- STATUS -->
                                <td>
                                    <span class="status-badge"
                                        style="background-color: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['color'] }};">
                                        <i class="bi {{ $statusStyle['icon'] }} me-1"></i>
                                        {{ ucfirst($schedule->status ?? '-') }}
                                    </span>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-calendar-x"></i>
                                        <h5>No Classes Found</h5>
                                        <p>No timetable records found for this week</p>
                                        <small class="text-muted">Try selecting a different week or reset filters</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>

            <!-- PAGINATION -->
            @if(method_exists($schedules, 'hasPages') && $schedules->hasPages())
                <div class="mt-4">
                    {{ $schedules->links() }}
                </div>
            @endif

        </div>
    </div>

@endsection

@push('styles')
    <style>
        .weekly-timetable-page {
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats Cards */
        .stats-card {
            background: #fff;
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .04);
            border: 1px solid #eef2f7;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px rgba(0, 0, 0, .08);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.8rem;
        }

        .stats-icon.blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .stats-icon.green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .stats-icon.orange {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .stats-icon.purple {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .stats-card h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stats-card p {
            margin: 0;
            color: #64748b;
            font-weight: 500;
        }

        /* Main Card */
        .main-card {
            background: #fff;
            border-radius: 28px;
            padding: 1.8rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.8rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
            margin-top: 4px;
        }

        .week-info {
            background: linear-gradient(135deg, #eff6ff, #e0e7ff);
            padding: 0.6rem 1.2rem;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: #1e40af;
            font-weight: 600;
        }

        .header-buttons {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .custom-btn {
            border-radius: 14px;
            padding: 0.7rem 1.2rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }

        /* Search Card */
        .search-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.8rem;
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
            font-size: 1rem;
        }

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
            padding-left: 42px;
            transition: all 0.2s ease;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        select.custom-input {
            padding-left: 42px;
            cursor: pointer;
        }

        /* Table Styles */
        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: 0.82rem;
            text-transform: uppercase;
            padding: 1rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .custom-table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .custom-table tbody td {
            padding: 1.2rem 1rem;
            border: none;
            vertical-align: middle;
        }

        /* Date & Time Cell */
        .date-time-cell {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .date,
        .time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .date i,
        .time i {
            color: #64748b;
            font-size: 0.85rem;
        }

        .date span,
        .time span {
            color: #334155;
        }

        .day {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 0.2rem;
            font-weight: 500;
        }

        /* Class Info */
        .class-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .class-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 8px 18px rgba(79, 70, 229, .20);
        }

        .class-name {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.2rem;
            font-size: 0.9rem;
        }

        /* Grade Badges */
        .grade-badge {
            padding: 0.45rem 0.85rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .grade-1 {
            background: #dbeafe;
            color: #1e40af;
        }

        .grade-2 {
            background: #e0e7ff;
            color: #4338ca;
        }

        .grade-3 {
            background: #cffafe;
            color: #0e7490;
        }

        .grade-4 {
            background: #fef3c7;
            color: #b45309;
        }

        .grade-5 {
            background: #e0f2fe;
            color: #0369a1;
        }

        .grade-6 {
            background: #fce7f3;
            color: #be185d;
        }

        .grade-default {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Category Badge */
        .category-badge {
            background: #f3e8ff;
            color: #6b21a5;
            padding: 0.45rem 0.85rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
        }

        /* Fee */
        .fee-amount {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e293b;
        }

        .fee-amount .currency {
            color: #64748b;
            font-size: 0.75rem;
        }

        .free-badge {
            background: #d1fae5;
            color: #065f46;
            padding: 0.35rem 0.7rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Status Badge */
        .status-badge {
            padding: 0.45rem 0.9rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #94a3b8;
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1rem;
        }

        /* Pagination */
        .pagination {
            justify-content: center;
            gap: 0.3rem;
        }

        .pagination .page-link {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 0.5rem 0.9rem;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
            transform: translateY(-2px);
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            border-color: #3b82f6;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-buttons {
                width: 100%;
                justify-content: space-between;
            }

            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .stats-card h3 {
                font-size: 1.3rem;
            }
        }

        /* Table Responsive */
        @media (max-width: 992px) {
            .table-responsive {
                overflow-x: auto;
            }

            .custom-table {
                min-width: 700px;
            }
        }
    </style>
@endpush