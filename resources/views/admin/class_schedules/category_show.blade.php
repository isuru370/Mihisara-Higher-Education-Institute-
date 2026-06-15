@extends('layouts.app')

@section('title', 'Category Schedules')
@section('page-title', 'Category Schedules')

@section('content')
    @php
        $className = $studentClass->class_name ?? '-';
        $gradeName = optional($studentClass->grade)->grade_name ?? 'N/A';
        $subjectName = optional($studentClass->subject)->subject_name ?? 'N/A';
        $teacherName = optional($studentClass->teacher)->full_name ?? 'N/A';

        $categoryName = $category->category_name ?? '-';
        $categoryCode = $category->code ?? '-';

        $searchValue = request('search');
    @endphp

    <div class="schedule-details-page">

        {{-- HERO --}}
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <div class="eyebrow mb-2">
                        Category View
                    </div>

                    <h3 class="fw-bold mb-1">
                        {{ $className }} / {{ $categoryName }}
                    </h3>

                    <p class="text-muted mb-0">
                        Grade: {{ $gradeName }}
                        •
                        Subject: {{ $subjectName }}
                        •
                        Teacher: {{ $teacherName }}
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.class-schedules.create', [
        'type' => 'single',
        'student_class_id' => $studentClass->id,
        'class_category_id' => $category->id,
        'class_category_fee_id' => $categoryFee->id,
    ]) }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-calendar-plus me-1"></i>
                        Add New Day
                    </a>

                    <a href="{{ route('admin.class-schedules.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>

        {{-- SUMMARY --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="summary-card summary-blue">
                    <div class="summary-icon">
                        <i class="bi bi-book"></i>
                    </div>

                    <div>
                        <div class="summary-label">
                            Class
                        </div>

                        <h4 class="summary-value">
                            {{ $className }}
                        </h4>

                        <div class="summary-sub">
                            {{ $gradeName }} · {{ $subjectName }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="summary-card summary-green">
                    <div class="summary-icon">
                        <i class="bi bi-collection"></i>
                    </div>

                    <div>
                        <div class="summary-label">
                            Category
                        </div>

                        <h4 class="summary-value">
                            {{ $categoryName }}
                        </h4>

                        <div class="summary-sub">
                            {{ $categoryCode }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEARCH --}}
        <div class="search-card mb-4">
            <form method="GET" action="{{ route('admin.class-schedules.categorySchedules') }}">
                <input type="hidden" name="student_class_id" value="{{ $studentClass->id }}">
                <input type="hidden" name="class_category_fee_id" value="{{ $categoryFee->id }}">

                <div class="row g-3">
                    <div class="col-md-8 col-lg-9">
                        <div class="position-relative">
                            <i class="bi bi-calendar-event search-icon"></i>
                            <input type="date" name="search" class="form-control custom-input search-input"
                                value="{{ $searchValue }}">
                        </div>
                        <small class="text-muted d-block mt-1">
                            Pick a date to filter schedules.
                        </small>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary search-btn">
                            <i class="bi bi-search me-1"></i>
                            Search
                        </button>
                    </div>

                    <div class="col-md-2 d-grid">
                        <a href="{{ route('admin.class-schedules.categorySchedules', [
        'student_class_id' => $studentClass->id,
        'class_category_fee_id' => $categoryFee->id,
    ]) }}" class="btn btn-outline-secondary search-btn">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLE --}}
        <div class="main-card">
            <div class="main-card-header">
                <div>
                    <h4 class="mb-1 fw-bold">
                        Schedules
                    </h4>

                    <p class="mb-0 text-muted">
                        All schedules for this class and category
                    </p>
                </div>

                <div class="header-badge">
                    {{ $schedules->total() }} Records
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle custom-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Hall</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $startNumber = $schedules->firstItem() ?? 1;
                        @endphp

                        @forelse($schedules as $index => $schedule)
                            @php
                                $scheduleDate = $schedule->class_date
                                    ? \Carbon\Carbon::parse($schedule->class_date)
                                    : null;

                                $startTime = $schedule->start_time
                                    ? \Carbon\Carbon::parse($schedule->start_time)->format('h:i A')
                                    : '-';

                                $endTime = $schedule->end_time
                                    ? \Carbon\Carbon::parse($schedule->end_time)->format('h:i A')
                                    : '-';

                                $hallName = optional($schedule->hall)->hall_name ?? '-';

                                $statusClass = 'bg-secondary';
                                if ($schedule->status === 'scheduled') {
                                    $statusClass = 'bg-primary';
                                } elseif ($schedule->status === 'ongoing') {
                                    $statusClass = 'bg-warning text-dark';
                                } elseif ($schedule->status === 'completed') {
                                    $statusClass = 'bg-success';
                                } elseif ($schedule->status === 'cancelled') {
                                    $statusClass = 'bg-danger';
                                }

                                $isLocked = $scheduleDate ? $scheduleDate->lte(today()) : false;
                                $isCancelable = !$isLocked
                                    && !$schedule->is_active
                                    && !in_array($schedule->status, ['cancelled', 'completed']);
                                $isEditable = !$isLocked
                                    && $schedule->is_active
                                    && !in_array($schedule->status, ['cancelled', 'completed']);
                                $canToggleActive = !$isLocked
                                    && !in_array($schedule->status, ['cancelled', 'completed']);
                            @endphp

                            <tr>
                                <td>{{ $startNumber + $index }}</td>

                                <td>
                                    {{ $scheduleDate ? $scheduleDate->format('Y-m-d') : '-' }}
                                </td>

                                <td>
                                    {{ ucfirst($schedule->day_of_week) }}
                                </td>

                                <td>
                                    {{ $startTime }} - {{ $endTime }}
                                </td>

                                <td>
                                    {{ $hallName }}
                                </td>

                                <td>
                                    <span class="badge {{ $statusClass }} custom-badge">
                                        {{ ucfirst($schedule->status) }}
                                    </span>

                                    <br>

                                    <span
                                        class="badge {{ $schedule->is_active ? 'bg-success' : 'bg-secondary' }} custom-badge mt-1">
                                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">

                                        <a href="{{ route('admin.class-schedules.show', $schedule) }}"
                                            class="btn btn-sm btn-outline-primary icon-btn" title="View schedule">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($isLocked)
                                            <span class="badge bg-dark custom-badge align-self-center">Locked</span>
                                        @else
                                            @if($isEditable)
                                                <a href="{{ route('admin.class-schedules.edit', $schedule) }}"
                                                    class="btn btn-sm btn-outline-warning icon-btn" title="Edit schedule">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                            @endif

                                            @if($canToggleActive)
                                                <form method="POST"
                                                    action="{{ route('admin.class-schedules.toggleActive', $schedule) }}"
                                                    class="d-inline" onsubmit="return confirm('Change active status?')">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                        class="btn btn-sm {{ $schedule->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }} icon-btn"
                                                        title="{{ $schedule->is_active ? 'Set Inactive' : 'Set Active' }}">
                                                        <i
                                                            class="bi {{ $schedule->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($isCancelable)
                                                <button type="button" class="btn btn-sm btn-outline-danger icon-btn"
                                                    data-bs-toggle="modal" data-bs-target="#cancelModal{{ $schedule->id }}"
                                                    title="Cancel schedule">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                    No schedules found for this category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($schedules->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $schedules->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    @foreach($schedules as $schedule)
        @php
            $scheduleDate = $schedule->class_date
                ? \Carbon\Carbon::parse($schedule->class_date)
                : null;

            $isLocked = $scheduleDate ? $scheduleDate->lte(today()) : false;
            $isCancelable = !$isLocked
                && !$schedule->is_active
                && !in_array($schedule->status, ['cancelled', 'completed']);
        @endphp

        @if($isCancelable)
            <div class="modal fade" id="cancelModal{{ $schedule->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <form method="POST" action="{{ route('admin.class-schedules.cancel', $schedule) }}">
                            @csrf
                            @method('PATCH')

                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Cancel Schedule</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <p class="text-muted mb-3">
                                    {{ \Carbon\Carbon::parse($schedule->class_date)->format('Y-m-d') }}
                                    |
                                    {{ ucfirst($schedule->day_of_week) }}
                                    |
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                    - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </p>

                                <label class="form-label fw-semibold">Cancel Reason *</label>
                                <textarea name="cancel_reason" class="form-control custom-input" rows="3" required
                                    placeholder="Enter cancellation reason"></textarea>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-danger">
                                    Cancel Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@push('styles')
    <style>
        .schedule-details-page {
            animation: fadeIn .35s ease;
        }

        .hero-card,
        .main-card,
        .search-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #e8eef5;
            box-shadow: 0 14px 35px rgba(15, 23, 42, .06);
        }

        .hero-card {
            padding: 1.6rem;
        }

        .main-card {
            padding: 1.5rem;
        }

        .search-card {
            padding: 1.25rem;
        }

        .hero-content,
        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-actions {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #2563eb;
            background: rgba(37, 99, 235, .08);
            border: 1px solid rgba(37, 99, 235, .12);
            border-radius: 999px;
            padding: .35rem .7rem;
        }

        .summary-card {
            position: relative;
            overflow: hidden;
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            min-height: 140px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, .05);
        }

        .summary-blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
        }

        .summary-green {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
        }

        .summary-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .summary-label {
            font-size: .82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            opacity: .9;
        }

        .summary-value {
            margin: .4rem 0;
            font-weight: 800;
        }

        .summary-sub {
            opacity: .9;
            font-size: .92rem;
        }

        .header-badge {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: .5rem .9rem;
            font-size: .82rem;
            font-weight: 700;
        }

        .custom-table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            color: #0f172a;
            font-weight: 700;
            padding: 1rem;
            white-space: nowrap;
        }

        .custom-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #eef2f7;
        }

        .custom-table tbody tr {
            transition: .2s ease;
        }

        .custom-table tbody tr:hover {
            background: #f8fbff;
        }

        .custom-badge {
            border-radius: 999px;
            padding: .55rem .8rem;
            font-size: .75rem;
            font-weight: 700;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .75rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .search-input {
            min-height: 48px;
            border-radius: 14px !important;
            border: 1px solid #dbe3ec;
            padding-left: 44px;
        }

        .search-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .search-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #64748b;
            z-index: 2;
            pointer-events: none;
        }

        .search-btn {
            border-radius: 12px;
            padding: .55rem 1rem;
            font-weight: 600;
            height: 48px;
        }

        .icon-btn {
            border-radius: 12px;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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

            .summary-card {
                min-height: auto;
            }
        }
    </style>
@endpush