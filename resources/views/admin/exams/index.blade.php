@extends('layouts.app')

@section('title', 'Exams')
@section('page-title', 'Exams')

@section('content')

    <div class="exams-page">

        <!-- TOP STATS -->
        @include('admin.exams.partials.stats')

        <!-- MAIN CARD -->
        <div class="main-card">

            <!-- HEADER -->
            <div class="main-card-header">
                <div>
                    <h4>Exam Management</h4>
                    <p>Manage all exams, schedules, and results</p>
                </div>

                <!-- BUTTONS -->
                <div class="header-buttons">
                    <a href="{{ route('admin.exams.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg"></i>
                        Add Exam
                    </a>
                    <a href="{{ route('admin.exams.upcoming') }}" class="btn btn-info custom-btn">
                        <i class="bi bi-calendar-event"></i>
                        Upcoming
                    </a>
                    <a href="{{ route('admin.exams.export.excel') }}" class="btn btn-success custom-btn">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        Excel
                    </a>

                    <a href="{{ route('admin.exams.export.pdf') }}" class="btn btn-danger custom-btn">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        PDF
                    </a>
                </div>
            </div>

            <!-- UPCOMING EXAMS ALERT -->
            @if (isset($upcomingExams) && count($upcomingExams) > 0)
                <div class="alert alert-info custom-alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            <strong>Upcoming Exams:</strong>
                            @foreach ($upcomingExams as $exam)
                                <span class="badge bg-primary custom-badge">
                                    {{ $exam->title }}
                                </span>
                                <small>({{ $exam->exam_date->format('d M Y') }})</small>
                                @if (!$loop->last)
                                    |
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- SEARCH BAR -->
            <div class="search-card">
                <form method="GET" action="{{ route('admin.exams.index') }}">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search exam title..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <select name="status" class="form-select custom-input">
                                <option value="">All Status</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>
                                    Scheduled
                                </option>
                                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>
                                    Ongoing
                                </option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                    Completed
                                </option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                    Cancelled
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <select name="student_class_id" class="form-select custom-input">
                                <option value="">All Classes</option>
                                @foreach ($classes ?? [] as $class)
                                    <option value="{{ $class->id }}"
                                        {{ request('student_class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <input type="date" name="exam_date_from" class="form-control custom-input"
                                value="{{ request('exam_date_from') }}" placeholder="Date From">
                        </div>

                        <div class="col-lg-1">
                            <button type="submit" class="btn btn-primary w-100 custom-btn">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>

                        <div class="col-lg-1">
                            <a href="{{ route('admin.exams.index') }}" class="btn btn-light border w-100 custom-btn">
                                Reset
                            </a>
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
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            @php
                                $status = $exam->status;
                                $isToday = $exam->exam_date->isToday();
                                $isFuture = $exam->exam_date->isFuture();

                                $showEdit = false;
                                $showDelete = false;
                                $showCancel = false;
                                $showMarkEntry = false;
                                $showResults = false;

                                switch ($status) {
                                    case 'completed':
                                        $showResults = true;
                                        break;

                                    case 'scheduled':
                                        if ($isToday) {
                                            $showEdit = true;
                                            $showCancel = true;
                                            $showMarkEntry = false;
                                            $showDelete = false;
                                        } elseif ($isFuture) {
                                            $showEdit = false;
                                            $showDelete = true;
                                            $showCancel = true;
                                        } else {
                                            $showEdit = false;
                                            $showDelete = true;
                                            $showCancel = false;
                                        }
                                        break;

                                    case 'ongoing':
                                        $showMarkEntry = true;
                                        break;

                                    case 'cancelled':
                                        break;

                                    default:
                                        break;
                                }

                                $statusColors = [
                                    'scheduled' => 'primary',
                                    'ongoing' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                ];
                                $statusColor = $statusColors[$status] ?? 'secondary';
                            @endphp

                            <tr>
                                <td>
                                    {{ $loop->iteration + ($exams->currentPage() - 1) * $exams->perPage() }}
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
                                        {{ $exam->studentClass->class_name ?? 'N/A' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $exam->studentClass->grade->grade_name ?? '' }}
                                    </small>
                                </td>

                                <td>
                                    <span class="badge bg-info-subtle text-info custom-badge">
                                        {{ $exam->category->category_name ?? 'N/A' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $exam->hall->hall_name ?? 'N/A' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $exam->exam_date->format('d M Y') }}
                                        @if ($isToday)
                                            <span class="badge bg-success custom-badge">Today</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i>
                                        {{ $exam->start_time }} - {{ $exam->end_time }}
                                    </small>
                                </td>

                                <td>
                                    <span class="badge bg-{{ $statusColor }} custom-badge">
                                        {{ ucfirst($status) }}
                                    </span>

                                    @if ($status == 'cancelled' && $exam->cancel_reason)
                                        <br>
                                        <small class="text-danger" data-bs-toggle="tooltip"
                                            title="{{ $exam->cancel_reason }}">
                                            <i class="bi bi-info-circle"></i>
                                            Cancelled
                                        </small>
                                    @endif
                                </td>

                                <td class="text-end">

                                    @include('admin.exams.partials.actions', [
                                        'exam' => $exam,
                                        'showEdit' => $showEdit,
                                        'showDelete' => $showDelete,
                                        'showCancel' => $showCancel,
                                        'showMarkEntry' => $showMarkEntry,
                                        'showResults' => $showResults,
                                    ])

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                    <h5>No exams found</h5>
                                    <p class="mb-0">Try adjusting your search filters or create a new exam.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="mt-4">
                {{ $exams->appends(request()->query())->links() }}
            </div>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- ✅ SINGLE MODAL - ONE FOR ALL EXAMS        -->
    <!-- ========================================== -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true"
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
        .exams-page {
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

        .purple {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .info {
            background: linear-gradient(135deg, #06b6d4, #22d3ee);
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

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 700;
            color: #0f172a;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .header-buttons {
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

        .exam-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
        }

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

        .custom-alert {
            border-radius: 16px;
            border: none;
            background: #f0f9ff;
            color: #0c4a6e;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid #0ea5e9;
        }

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

        .results-btn {
            background: #f0fdf4;
            color: #16a34a;
        }

        .results-btn:hover {
            background: #16a34a;
            color: white;
        }

        .mark-btn {
            background: #ecfeff;
            color: #0891b2;
        }

        .mark-btn:hover {
            background: #0891b2;
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

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        .delete-btn:hover {
            background: #dc2626;
            color: white;
        }

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

        @media (max-width: 992px) {
            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-buttons {
                width: 100%;
            }

            .header-buttons .custom-btn {
                flex: 1;
                justify-content: center;
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

            .main-card-header h4 {
                font-size: 1.1rem;
            }
        }

        @media print {

            .action-buttons,
            .header-buttons,
            .search-card,
            .custom-alert {
                display: none !important;
            }

            .main-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .stats-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                break-inside: avoid;
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
            // 3. AUTO-SUBMIT ON DATE CHANGE
            // ============================================
            document.querySelectorAll('.search-card input[type="date"]').forEach(function(input) {
                input.addEventListener('change', function() {
                    this.closest('form').submit();
                });
            });

            // ============================================
            // 4. ENHANCED DELETE CONFIRMATION
            // ============================================
            document.querySelectorAll('.delete-btn').forEach(function(button) {
                button.closest('form').addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: '⚠️ Delete Exam?',
                        text: 'Are you sure you want to delete this exam? This action cannot be undone!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        showLoaderOnConfirm: true,
                        preConfirm: () => {
                            this.submit();
                        }
                    });
                });
            });

            // ============================================
            // 5. ✅ SINGLE MODAL - Populate with exam data
            // ============================================
            var cancelModal = document.getElementById('cancelModal');

            if (cancelModal) {
                cancelModal.addEventListener('show.bs.modal', function(event) {
                    // Get the button that triggered the modal
                    var button = event.relatedTarget;

                    // Extract data from data attributes
                    var examId = button.getAttribute('data-exam-id');
                    var examTitle = button.getAttribute('data-exam-title');
                    var examRoute = button.getAttribute('data-exam-route');

                    // Update modal content
                    var titleElement = document.getElementById('cancelExamTitle');
                    if (titleElement) {
                        titleElement.textContent = '"' + examTitle + '"';
                    }

                    // Update form action
                    var form = document.getElementById('cancelForm');
                    if (form) {
                        form.action = examRoute;
                    }

                    // Clear previous reason
                    var textarea = form.querySelector('textarea[name="cancel_reason"]');
                    if (textarea) {
                        textarea.value = '';
                    }
                });
            }

            // ============================================
            // 6. KEYBOARD SHORTCUTS
            // ============================================
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    var searchInput = document.querySelector('.search-card input[name="search"]');
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }
            });

            // ============================================
            // 7. TOAST NOTIFICATIONS
            // ============================================
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    timer: 4000,
                    showConfirmButton: true,
                    toast: true,
                    position: 'top-end'
                });
            @endif

            // ============================================
            // 8. FILTER INDICATOR
            // ============================================
            var searchParams = new URLSearchParams(window.location.search);
            if (searchParams.toString().length > 0) {
                var filterBadge = document.createElement('span');
                filterBadge.className = 'badge bg-primary custom-badge ms-2';
                filterBadge.textContent = 'Filters Active';
                var headerTitle = document.querySelector('.main-card-header h4');
                if (headerTitle) {
                    headerTitle.appendChild(filterBadge);
                }
            }

        });

        // ============================================
        // 9. HELPER FUNCTION - Export to CSV
        // ============================================
        function exportCSV() {
            var rows = document.querySelectorAll('.custom-table tbody tr');
            if (rows.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Data',
                    text: 'No exams to export!'
                });
                return;
            }

            var csv = [];
            var headers = ['#', 'Exam Title', 'Class', 'Category', 'Hall', 'Date', 'Time', 'Status'];
            csv.push(headers.join(','));

            rows.forEach(function(row) {
                var rowData = [];
                var cells = row.querySelectorAll('td');
                cells.forEach(function(cell, index) {
                    if (index < 7) {
                        var text = cell.textContent.trim();
                        text = text.replace(/\s+/g, ' ');
                        text = text.replace(/Today/g, '');
                        text = text.replace(/badge/g, '');
                        rowData.push('"' + text + '"');
                    }
                });
                if (rowData.length > 0) {
                    csv.push(rowData.join(','));
                }
            });

            var blob = new Blob([csv.join('\n')], {
                type: 'text/csv'
            });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'exams_export_' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);

            Swal.fire({
                icon: 'success',
                title: 'Exported!',
                text: 'CSV file downloaded successfully.',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    </script>
@endpush
