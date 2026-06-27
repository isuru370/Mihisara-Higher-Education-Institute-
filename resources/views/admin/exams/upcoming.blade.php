@extends('layouts.app')

@section('title', 'Upcoming Exams')
@section('page-title', 'Upcoming Exams')

@section('content')

<div class="upcoming-exams-page">

    <!-- HEADER -->
    <div class="page-header">
        <div class="header-left">
            <div class="header-icon">
                <i class="bi bi-calendar-event"></i>
            </div>
            <div>
                <h4>Upcoming Exams</h4>
                <p>View and manage all upcoming exams</p>
            </div>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary custom-btn">
                <i class="bi bi-arrow-left"></i>
                Back to All Exams
            </a>
        </div>
    </div>

    <!-- STATS ROW -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div>
                    <h3>{{ $stats['upcoming'] ?? 0 }}</h3>
                    <p>Upcoming Exams</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon green">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h3>{{ $stats['scheduled'] ?? 0 }}</h3>
                    <p>Scheduled</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon orange">
                    <i class="bi bi-play-circle"></i>
                </div>
                <div>
                    <h3>{{ $stats['ongoing'] ?? 0 }}</h3>
                    <p>Ongoing</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- SEARCH BAR -->
        <div class="search-card">
            <form method="GET" action="{{ route('admin.exams.upcoming') }}">
                <div class="row g-3">
                    <div class="col-lg-5">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control custom-input"
                                placeholder="Search exam title..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="search-input-wrapper">
                            <i class="bi bi-people"></i>
                            <select name="student_class_id" class="form-select custom-input">
                                <option value="">All Classes</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ ($filters['student_class_id'] ?? '') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                        @if ($class->grade)
                                            ({{ $class->grade->grade_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="d-grid gap-2 d-flex">
                            <button type="submit" class="btn btn-primary custom-btn flex-grow-1">
                                <i class="bi bi-funnel"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.exams.upcoming') }}" class="btn btn-light border custom-btn">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Exam Title</th>
                        <th>Class</th>
                        <th>Category</th>
                        <th>Hall</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exams as $exam)
                        @php
                            $statusColors = [
                                'scheduled' => 'primary',
                                'ongoing' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ];
                            $statusColor = $statusColors[$exam->status] ?? 'secondary';

                            $statusIcons = [
                                'scheduled' => 'clock',
                                'ongoing' => 'play-circle',
                                'completed' => 'check-circle',
                                'cancelled' => 'x-circle',
                            ];
                            $statusIcon = $statusIcons[$exam->status] ?? 'question-circle';

                            $isToday = $exam->exam_date && $exam->exam_date->isToday();
                        @endphp

                        <tr>
                            <td>
                                <span class="fw-semibold text-muted">
                                    {{ $loop->iteration + ($exams->currentPage() - 1) * $exams->perPage() }}
                                </span>
                            </td>

                            <td>
                                <div>
                                    <div class="exam-title">
                                        {{ $exam->title }}
                                    </div>
                                    @if ($exam->note)
                                        <small class="text-muted">
                                            <i class="bi bi-pencil"></i>
                                            {{ Str::limit($exam->note, 30) }}
                                        </small>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $exam->studentClass?->class_name ?? 'N/A' }}
                                </div>
                                @if ($exam->studentClass && $exam->studentClass->grade)
                                    <small class="text-muted">
                                        {{ $exam->studentClass->grade->grade_name }}
                                    </small>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-info-subtle text-info custom-badge">
                                    {{ $exam->category?->category_name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $exam->hall?->hall_name ?? 'N/A' }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $exam->exam_date ? $exam->exam_date->format('d M Y') : 'N/A' }}
                                    @if ($isToday)
                                        <span class="badge bg-success custom-badge ms-1">Today</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    {{ $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('h:i A') : 'N/A' }}
                                    -
                                    {{ $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('h:i A') : 'N/A' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge bg-{{ $statusColor }} custom-badge">
                                    <i class="bi bi-{{ $statusIcon }} me-1"></i>
                                    {{ ucfirst($exam->status) }}
                                </span>
                            </td>

                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.exams.show', $exam->id) }}" 
                                       class="action-btn view-btn" 
                                       title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    @if($exam->status != 'cancelled')
                                        <a href="{{ route('admin.exams.edit', $exam->id) }}" 
                                           class="action-btn edit-btn" 
                                           title="Edit Exam">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                    @endif

                                    @if($exam->status == 'scheduled')
                                        <button type="button" 
                                                class="action-btn cancel-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal"
                                                data-exam-id="{{ $exam->id }}"
                                                data-exam-title="{{ $exam->title }}"
                                                data-exam-route="{{ route('admin.exams.cancel', $exam->id) }}"
                                                title="Cancel Exam">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                <h5>No upcoming exams found</h5>
                                <p class="mb-0">Try adjusting your search filters or create a new exam.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="mt-4">
            {{ $exams->links() }}
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- ✅ SINGLE MODAL - ONE FOR ALL EXAMS        -->
<!-- ========================================== -->
<div class="modal fade" id="cancelModal" tabindex="-1" 
     aria-labelledby="cancelModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="cancelForm" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                        Cancel Exam
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel <strong id="cancelExamTitle">"Exam Name"</strong>?</p>

                    <div class="form-group mt-3">
                        <label class="fw-semibold">Reason for cancellation</label>
                        <textarea name="cancel_reason" class="form-control custom-input" rows="3" required
                            placeholder="Enter cancellation reason..."></textarea>
                        <small class="text-muted">This reason will be visible to students and staff.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Close
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Cancel Exam
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ============================================ */
    /* PAGE ANIMATION                              */
    /* ============================================ */
    .upcoming-exams-page {
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

    /* ============================================ */
    /* PAGE HEADER                                 */
    /* ============================================ */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-icon {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .header-left h4 {
        margin: 0;
        font-weight: 700;
        color: #0f172a;
    }

    .header-left p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .header-right {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .custom-btn {
        border-radius: 14px;
        padding: 0.7rem 1.2rem;
        font-weight: 600;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .custom-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }

    .custom-btn:active {
        transform: translateY(0);
    }

    /* ============================================ */
    /* STATS CARDS                                 */
    /* ============================================ */
    .stats-card {
        background: white;
        border-radius: 24px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eef2f7;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
    }

    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }

    .stats-card:hover .stats-icon {
        transform: scale(1.05) rotate(-3deg);
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

    .stats-card h3 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #0f172a;
    }

    .stats-card p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* ============================================ */
    /* MAIN CARD                                   */
    /* ============================================ */
    .main-card {
        background: white;
        border-radius: 28px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
    }

    /* ============================================ */
    /* SEARCH CARD                                 */
    /* ============================================ */
    .search-card {
        background: #f8fafc;
        border-radius: 20px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid #eef2f7;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper i {
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.1rem;
        z-index: 1;
    }

    .search-input-wrapper .form-select {
        padding-left: 42px !important;
    }

    .custom-input {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        min-height: 48px;
        padding-left: 42px;
        transition: all 0.3s ease;
        background: white;
    }

    .custom-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .custom-input select {
        padding-left: 15px;
    }

    /* ============================================ */
    /* TABLE                                       */
    /* ============================================ */
    .custom-table {
        margin-bottom: 0;
    }

    .custom-table thead th {
        border: none;
        background: #f8fafc;
        color: #475569;
        font-size: 0.82rem;
        text-transform: uppercase;
        padding: 1rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        border-bottom: 2px solid #eef2f7;
    }

    .custom-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-table tbody tr:hover {
        background: #f8fafc;
        transform: scale(1.002);
    }

    .custom-table tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    /* ============================================ */
    /* EXAM TITLE                                  */
    /* ============================================ */
    .exam-title {
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
    }

    /* ============================================ */
    /* BADGES                                      */
    /* ============================================ */
    .custom-badge {
        border-radius: 10px;
        padding: 0.4rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .bg-info-subtle {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
    }

    /* ============================================ */
    /* ACTION BUTTONS                              */
    /* ============================================ */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        font-size: 1rem;
        position: relative;
    }

    .action-btn:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .action-btn:active {
        transform: scale(0.95);
    }

    .action-btn::after {
        content: attr(title);
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) scale(0.8);
        background: #0f172a;
        color: white;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 500;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: all 0.2s ease;
    }

    .action-btn:hover::after {
        opacity: 1;
        transform: translateX(-50%) scale(1);
    }

    .view-btn {
        background: #eff6ff;
        color: #2563eb;
    }

    .view-btn:hover {
        background: #2563eb;
        color: white;
    }

    .edit-btn {
        background: #fef3c7;
        color: #d97706;
    }

    .edit-btn:hover {
        background: #d97706;
        color: white;
    }

    .cancel-btn {
        background: #fef2f2;
        color: #ef4444;
    }

    .cancel-btn:hover {
        background: #ef4444;
        color: white;
    }

    /* ============================================ */
    /* PAGINATION                                  */
    /* ============================================ */
    .pagination {
        justify-content: center;
        gap: 0.25rem;
    }

    .pagination .page-link {
        border-radius: 10px !important;
        margin: 0 2px;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .pagination .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
    }

    /* ============================================ */
    /* MODAL                                       */
    /* ============================================ */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.5);
        transition: opacity 0.15s linear;
    }

    .modal-backdrop.show {
        opacity: 0.5;
    }

    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 0.4rem;
    }

    /* ============================================ */
    /* RESPONSIVE                                  */
    /* ============================================ */
    @media (max-width: 992px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-right {
            width: 100%;
        }

        .header-right .custom-btn {
            flex: 1;
            justify-content: center;
        }

        .main-card {
            padding: 1.25rem;
        }
    }

    @media (max-width: 768px) {
        .stats-card {
            padding: 1rem;
        }

        .stats-icon {
            width: 45px;
            height: 45px;
            font-size: 1.2rem;
        }

        .stats-card h3 {
            font-size: 1.2rem;
        }

        .stats-card p {
            font-size: 0.8rem;
        }

        .main-card {
            padding: 1rem;
        }

        .search-card {
            padding: 1rem;
        }

        .custom-table thead th {
            font-size: 0.7rem;
            padding: 0.75rem 0.5rem;
        }

        .custom-table tbody td {
            padding: 0.75rem 0.5rem;
            font-size: 0.85rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .stats-card {
            flex-direction: column;
            text-align: center;
        }

        .action-buttons {
            justify-content: center;
        }

        .action-btn {
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }

        .action-btn::after {
            display: none;
        }

        .custom-table {
            font-size: 0.8rem;
        }

        .page-header h4 {
            font-size: 1.1rem;
        }

        .main-card {
            padding: 0.75rem;
        }
    }

    /* ============================================ */
    /* PRINT STYLES                                */
    /* ============================================ */
    @media print {
        .action-buttons,
        .header-right,
        .search-card {
            display: none !important;
        }

        .main-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .stats-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ============================================
        // 1. INITIALIZE TOOLTIPS
        // ============================================
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'top',
                trigger: 'hover'
            });
        });

        // ============================================
        // 2. AUTO-SUBMIT FORM ON SELECT CHANGE
        // ============================================
        document.querySelectorAll('.search-card select').forEach(function(select) {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });

        // ============================================
        // 3. CANCEL MODAL - Populate with exam data
        // ============================================
        var cancelModal = document.getElementById('cancelModal');

        if (cancelModal) {
            cancelModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;

                var examTitle = button.getAttribute('data-exam-title');
                var examRoute = button.getAttribute('data-exam-route');

                var titleElement = document.getElementById('cancelExamTitle');
                if (titleElement) {
                    titleElement.textContent = '"' + examTitle + '"';
                }

                var form = document.getElementById('cancelForm');
                if (form) {
                    form.action = examRoute;
                }

                var textarea = form.querySelector('textarea[name="cancel_reason"]');
                if (textarea) {
                    textarea.value = '';
                }
            });
        }

        // ============================================
        // 4. FILTER INDICATOR
        // ============================================
        var searchParams = new URLSearchParams(window.location.search);
        if (searchParams.toString().length > 0) {
            var filterBadge = document.createElement('span');
            filterBadge.className = 'badge bg-primary custom-badge ms-2';
            filterBadge.textContent = 'Filters Active';
            var headerTitle = document.querySelector('.header-left h4');
            if (headerTitle) {
                headerTitle.appendChild(filterBadge);
            }
        }

        console.log('✅ Upcoming exams page initialized');
    });
</script>
@endpush