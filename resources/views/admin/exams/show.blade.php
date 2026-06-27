@extends('layouts.app')

@section('title', 'Exam Details')
@section('page-title', 'Exam Details')

@section('content')

<div class="exam-details-page">

    <!-- BACK BUTTON & ACTIONS -->
    <div class="details-header">
        <div class="header-left">
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary custom-btn">
                <i class="bi bi-arrow-left"></i>
                Back to Exams
            </a>
        </div>
        <div class="header-right">
            {{-- ✅ Edit button - ONLY show if NOT cancelled --}}
            @if($exam->status != 'cancelled')
                <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-primary custom-btn">
                    <i class="bi bi-pencil-fill"></i>
                    Edit Exam
                </a>
            @endif

            {{-- Cancel button - ONLY show if scheduled --}}
            @if($exam->status == 'scheduled')
                <button type="button" class="btn btn-danger custom-btn" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="bi bi-x-circle-fill"></i>
                    Cancel Exam
                </button>
            @endif
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- EXAM HEADER -->
        <div class="exam-header">
            <div class="exam-title-section">
                <div class="title-icon">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div>
                    <h1 class="exam-title">{{ $exam->title }}</h1>
                    <div class="exam-meta">
                        <span class="meta-item">
                            <i class="bi bi-calendar"></i>
                            {{ $exam->exam_date->format('l, d M Y') }}
                        </span>
                        <span class="meta-item">
                            <i class="bi bi-clock"></i>
                            {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                        </span>
                        <span class="meta-item">
                            <i class="bi bi-building"></i>
                            {{ $exam->hall->hall_name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="exam-status-badge">
                <span class="badge status-{{ $exam->status }}">
                    <i class="bi bi-{{ $exam->status == 'scheduled' ? 'clock' : 
                                      ($exam->status == 'ongoing' ? 'play-circle' : 
                                      ($exam->status == 'completed' ? 'check-circle' : 'x-circle')) }}">
                    </i>
                    {{ ucfirst($exam->status) }}
                </span>
            </div>
        </div>

        <!-- STATS ROW -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card mini">
                    <div class="stats-icon-sm blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3>{{ $exam->studentClass->class_name ?? 'N/A' }}</h3>
                        <p>Class</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card mini">
                    <div class="stats-icon-sm purple">
                        <i class="bi bi-tags"></i>
                    </div>
                    <div>
                        <h3>{{ $exam->category->category_name ?? 'N/A' }}</h3>
                        <p>Category</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card mini">
                    <div class="stats-icon-sm green">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <h3>{{ $exam->results ? $exam->results->count() : 0 }}</h3>
                        <p>Total Results</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card mini">
                    <div class="stats-icon-sm orange">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $exam->studentClass && $exam->studentClass->students ? $exam->studentClass->students->count() : 0 }}</h3>
                        <p>Total Students</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TWO COLUMN LAYOUT -->
        <div class="row g-4">

            <!-- LEFT COLUMN -->
            <div class="col-lg-7">

                <!-- INFORMATION CARD -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="header-left">
                            <i class="bi bi-info-circle"></i>
                            <h6>Exam Information</h6>
                        </div>
                    </div>
                    <div class="detail-card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">
                                    <i class="bi bi-pencil"></i> Title
                                </span>
                                <span class="info-value">{{ $exam->title }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">
                                    <i class="bi bi-people"></i> Class
                                </span>
                                <span class="info-value">{{ $exam->studentClass->class_name ?? 'N/A' }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">
                                    <i class="bi bi-tags"></i> Category
                                </span>
                                <span class="info-value">{{ $exam->category->category_name ?? 'N/A' }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">
                                    <i class="bi bi-building"></i> Hall
                                </span>
                                <span class="info-value">{{ $exam->hall->hall_name ?? 'N/A' }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">
                                    <i class="bi bi-calendar"></i> Date
                                </span>
                                <span class="info-value">{{ $exam->exam_date->format('l, d M Y') }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">
                                    <i class="bi bi-clock"></i> Time
                                </span>
                                <span class="info-value">
                                    {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($exam->end_time)->format('h:i A') }}
                                </span>
                            </div>

                            <div class="info-item full-width">
                                <span class="info-label">
                                    <i class="bi bi-sticky"></i> Note
                                </span>
                                <span class="info-value">
                                    @if($exam->note)
                                        {{ $exam->note }}
                                    @else
                                        <span class="text-muted">No notes provided</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-lg-5">

                <!-- CANCELLATION DETAILS -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="header-left">
                            <i class="bi bi-exclamation-triangle"></i>
                            <h6>Cancellation Details</h6>
                        </div>
                    </div>
                    <div class="detail-card-body">
                        @if($exam->status == 'cancelled')
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="bi bi-file-text"></i> Reason
                                    </span>
                                    <span class="info-value">{{ $exam->cancel_reason ?? 'No reason provided' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="bi bi-person"></i> Cancelled By
                                    </span>
                                    <span class="info-value">{{ $exam->cancelledBy->name ?? 'N/A' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">
                                        <i class="bi bi-clock"></i> Cancelled At
                                    </span>
                                    <span class="info-value">
                                        {{ $exam->cancelled_at ? $exam->cancelled_at->format('d M Y h:i A') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <p class="text-center text-muted my-4">
                                <i class="bi bi-check-circle text-success"></i>
                                This exam has not been cancelled.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- RESULTS SUMMARY -->
                <div class="detail-card mt-3">
                    <div class="detail-card-header">
                        <div class="header-left">
                            <i class="bi bi-bar-chart"></i>
                            <h6>Results Summary</h6>
                        </div>
                    </div>
                    <div class="detail-card-body">
                        <div class="text-center mb-3">
                            <h3 class="mb-0">{{ $exam->results ? $exam->results->count() : 0 }}</h3>
                            <p class="text-muted small">Total Results Recorded</p>
                        </div>
                        @if($exam->results && $exam->results->count() > 0)
                            <a href="#" class="btn btn-success custom-btn w-100">
                                <i class="bi bi-file-earmark-text"></i>
                                View All Results
                            </a>
                        @else
                            <p class="text-center text-muted mb-0">
                                <i class="bi bi-info-circle"></i>
                                No results recorded yet.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- CANCEL MODAL                               -->
<!-- ========================================== -->
@if($exam->status == 'scheduled')
<div class="modal fade" id="cancelModal" tabindex="-1" 
     aria-labelledby="cancelModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.exams.cancel', $exam) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                        Cancel Exam
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel <strong>"{{ $exam->title }}"</strong>?</p>

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
@endif

@endsection

@push('styles')
<style>
    /* ============================================ */
    /* PAGE ANIMATION                              */
    /* ============================================ */
    .exam-details-page {
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
    /* HEADER                                      */
    /* ============================================ */
    .details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-left,
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

    .custom-btn:disabled,
    .custom-btn.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
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
    /* EXAM HEADER                                 */
    /* ============================================ */
    .exam-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        border-radius: 20px;
        border: 1px solid #f1f5f9;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .exam-title-section {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .title-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }

    .exam-title {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #0f172a;
    }

    .exam-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        margin-top: 0.25rem;
    }

    .meta-item {
        font-size: 0.85rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .meta-item i {
        font-size: 0.9rem;
        color: #94a3b8;
    }

    .exam-status-badge .badge {
        font-size: 1rem;
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .badge.status-scheduled {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .badge.status-ongoing {
        background: #fef3c7;
        color: #d97706;
    }

    .badge.status-completed {
        background: #d1fae5;
        color: #059669;
    }

    .badge.status-cancelled {
        background: #fee2e2;
        color: #dc2626;
    }

    /* ============================================ */
    /* MINI STATS CARDS                            */
    /* ============================================ */
    .stats-card.mini {
        background: white;
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
        height: 100%;
    }

    .stats-card.mini:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }

    .stats-icon-sm {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        flex-shrink: 0;
    }

    .stats-icon-sm.blue {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
    }
    .stats-icon-sm.purple {
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    }
    .stats-icon-sm.green {
        background: linear-gradient(135deg, #10b981, #34d399);
    }
    .stats-icon-sm.orange {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
    }

    .stats-card.mini h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: #0f172a;
    }

    .stats-card.mini p {
        margin: 0;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* ============================================ */
    /* DETAIL CARDS                                */
    /* ============================================ */
    .detail-card {
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .detail-card:hover {
        border-color: #e2e8f0;
    }

    .detail-card-header {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .detail-card-header .header-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .detail-card-header .header-left i {
        font-size: 1.2rem;
        color: #2563eb;
    }

    .detail-card-header .header-left h6 {
        margin: 0;
        font-weight: 700;
        color: #0f172a;
        font-size: 0.95rem;
    }

    .detail-card-body {
        padding: 1.5rem;
    }

    /* ============================================ */
    /* INFO GRID                                   */
    /* ============================================ */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        padding: 0.6rem 0.8rem;
        background: #fafbfc;
        border-radius: 10px;
        border: 1px solid #f1f5f9;
    }

    .info-item.full-width {
        grid-column: 1 / -1;
    }

    .info-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .info-label i {
        font-size: 0.7rem;
    }

    .info-value {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.95rem;
        margin-top: 0.15rem;
        word-break: break-word;
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

    /* ============================================ */
    /* FORM INPUTS                                 */
    /* ============================================ */
    .custom-input {
        border-radius: 14px !important;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
        background: #fafbfc;
        min-height: 48px;
    }

    .custom-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        background: white;
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
        .main-card {
            padding: 1.25rem;
        }

        .exam-header {
            padding: 1rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .exam-title-section {
            width: 100%;
        }

        .exam-status-badge {
            width: 100%;
        }

        .exam-status-badge .badge {
            width: 100%;
            justify-content: center;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .details-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-left,
        .header-right {
            width: 100%;
        }

        .header-left .custom-btn,
        .header-right .custom-btn {
            flex: 1;
            justify-content: center;
        }

        .main-card {
            padding: 1rem;
        }

        .exam-title {
            font-size: 1.2rem;
        }

        .exam-meta {
            gap: 0.75rem;
        }

        .meta-item {
            font-size: 0.75rem;
        }

        .title-icon {
            width: 44px;
            height: 44px;
            font-size: 1.2rem;
        }

        .stats-card.mini {
            padding: 1rem;
        }

        .stats-icon-sm {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .stats-card.mini h3 {
            font-size: 1rem;
        }

        .detail-card-header {
            padding: 0.75rem 1rem;
        }

        .detail-card-body {
            padding: 1rem;
        }

        .custom-btn {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    }

    @media (max-width: 576px) {
        .main-card {
            padding: 0.75rem;
            border-radius: 20px;
        }

        .exam-header {
            border-radius: 16px;
            padding: 0.75rem;
        }

        .detail-card {
            border-radius: 16px;
        }

        .info-value {
            font-size: 0.85rem;
        }

        .custom-btn {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }
    }

    /* ============================================ */
    /* PRINT STYLES                                */
    /* ============================================ */
    @media print {
        .details-header,
        .btn-close,
        .modal,
        .modal-backdrop {
            display: none !important;
        }

        .main-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .exam-header {
            background: none !important;
            border: 1px solid #ddd !important;
        }

        .stats-card.mini {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .detail-card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        .detail-card-header {
            background: #f5f5f5 !important;
            border: 1px solid #ddd !important;
        }

        .info-item {
            background: #fafafa !important;
        }
    }
</style>
@endpush