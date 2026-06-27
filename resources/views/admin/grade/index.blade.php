@extends('layouts.app')

@section('title', 'Grade Management')
@section('page-title', 'Grade Management')

@section('content')

<div class="grade-management-page">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="header-left">
            <div class="header-icon">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <h4>Grade Management</h4>
                <p class="text-muted small">Manage Student Grades</p>
            </div>
        </div>
        <div class="header-right">
            <button class="btn btn-primary custom-btn" data-bs-toggle="modal" data-bs-target="#createGradeModal">
                <i class="bi bi-plus-lg"></i>
                Add Grade
            </button>
        </div>
    </div>

    <!-- ALERTS -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
            <div class="alert-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="alert-content">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
            <div class="alert-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="alert-content">{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
            <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="alert-content">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li><i class="bi bi-dot"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- STATS ROW -->
        <div class="row g-4 mb-4">
            <div class="col-xl-4 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon purple">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <h3>{{ $grades->count() ?? 0 }}</h3>
                        <p>Total Grades</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <h3>{{ $grades->where('is_active', true)->count() ?? 0 }}</h3>
                        <p>Active Grades</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3>{{ $grades->sum('students_count') ?? 0 }}</h3>
                        <p>Total Students</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table custom-table align-middle" id="gradeTable">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Grade Name</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Created</th>
                        <th class="text-center" width="150">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grades as $key => $grade)
                        <tr>
                            <td>
                                <span class="row-number">{{ $key + 1 }}</span>
                            </td>

                            <td>
                                <div class="grade-name">
                                    <span class="grade-badge grade-{{ strtolower($grade->grade_name) }}">
                                        {{ $grade->grade_name }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="students-count-badge">
                                    <i class="bi bi-people"></i>
                                    {{ $grade->students_count }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($grade->is_active)
                                    <span class="badge status-active">
                                        <i class="bi bi-check-circle"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="badge status-inactive">
                                        <i class="bi bi-x-circle"></i>
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i>
                                    {{ $grade->created_at->format('d M Y') }}
                                </small>
                            </td>

                            <td class="text-center">
                                <div class="action-buttons justify-content-center">
                                    <button class="action-btn edit-btn editBtn"
                                            data-id="{{ $grade->id }}"
                                            data-name="{{ $grade->grade_name }}"
                                            data-status="{{ $grade->is_active }}"
                                            title="Edit Grade">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>

                                    <form action="{{ route('admin.grades.destroy', $grade->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="action-btn delete-btn"
                                                onclick="return confirm('Delete this grade?')"
                                                title="Delete Grade">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-graph-up fs-1 d-block mb-3"></i>
                                <h5>No Grades Found</h5>
                                <p class="mb-0">Click "Add Grade" to create your first grade.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TABLE FOOTER -->
        <div class="table-footer">
            <div class="footer-left">
                <span class="text-muted">
                    Showing {{ $grades->count() }} grades
                </span>
            </div>
            <div class="footer-right">
                <button class="btn btn-primary custom-btn-sm" data-bs-toggle="modal" data-bs-target="#createGradeModal">
                    <i class="bi bi-plus-lg"></i>
                    Add Grade
                </button>
            </div>
        </div>

    </div>

</div>

<!-- ========================================== -->
<!-- CREATE MODAL                              -->
<!-- ========================================== -->
<div class="modal fade" id="createGradeModal" tabindex="-1" 
     aria-labelledby="createGradeModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.grades.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header modal-header-primary">
                    <h5 class="modal-title" id="createGradeModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add New Grade
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-award text-primary"></i>
                            Grade Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="grade_name"
                               class="form-control custom-input @error('grade_name') is-invalid @enderror"
                               value="{{ old('grade_name') }}"
                               placeholder="Enter Grade Name" required>
                        @error('grade_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" 
                               name="is_active" id="createStatus" value="1" checked>
                        <label class="form-check-label fw-semibold" for="createStatus">
                            <i class="bi bi-toggle-on text-success"></i>
                            Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Save Grade
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- EDIT MODAL                                -->
<!-- ========================================== -->
<div class="modal fade" id="editGradeModal" tabindex="-1" 
     aria-labelledby="editGradeModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editGradeForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header modal-header-warning">
                    <h5 class="modal-title" id="editGradeModalLabel">
                        <i class="bi bi-pencil-square me-2"></i>
                        Edit Grade
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-award text-primary"></i>
                            Grade Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="grade_name" id="edit_grade_name"
                               class="form-control custom-input" required>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" 
                               name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label fw-semibold" for="edit_is_active">
                            <i class="bi bi-toggle-on text-success"></i>
                            Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Close
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-check-circle"></i> Update Grade
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ============================================ */
    /* PAGE ANIMATION                              */
    /* ============================================ */
    .grade-management-page {
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
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
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .header-left h4 {
        margin: 0;
        font-weight: 700;
        color: #0f172a;
    }

    .header-left p {
        margin: 0;
        color: #64748b;
        font-size: 0.85rem;
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

    .custom-btn-sm {
        border-radius: 10px;
        padding: 0.4rem 0.8rem;
        font-weight: 600;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .custom-btn-sm:hover {
        transform: translateY(-1px);
    }

    /* ============================================ */
    /* ALERTS                                      */
    /* ============================================ */
    .custom-alert {
        border-radius: 16px;
        border: none;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .custom-alert .alert-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    .custom-alert .alert-content { flex: 1; }

    .custom-alert ul {
        padding-left: 0;
        list-style: none;
    }

    .custom-alert ul li { padding: 0.15rem 0; }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border-left: 4px solid #10b981;
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
    /* STATS CARDS                                 */
    /* ============================================ */
    .stats-card {
        background: white;
        border-radius: 24px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        border: 1px solid #eef2f7;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
    }

    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
    }

    .stats-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: white;
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }

    .stats-card:hover .stats-icon { transform: scale(1.05); }

    .stats-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
    .stats-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
    .stats-icon.blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }

    .stats-card h3 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 700;
        color: #0f172a;
    }

    .stats-card p {
        margin: 0;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* ============================================ */
    /* TABLE                                       */
    /* ============================================ */
    .custom-table { margin-bottom: 0; }

    .custom-table thead th {
        border: none;
        background: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 0.75rem 1rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        border-bottom: 2px solid #eef2f7;
    }

    .custom-table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f1f5f9;
    }

    .custom-table tbody tr:hover { background: #f8fafc; }

    .custom-table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
    }

    .row-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #64748b;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* ============================================ */
    /* GRADE BADGES                                */
    /* ============================================ */
    .grade-badge {
        display: inline-block;
        padding: 0.3rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.85rem;
        min-width: 40px;
        text-align: center;
    }

    .grade-badge.grade-a {
        background: #d1fae5;
        color: #065f46;
    }

    .grade-badge.grade-b {
        background: #dbeafe;
        color: #1e40af;
    }

    .grade-badge.grade-c {
        background: #fef3c7;
        color: #92400e;
    }

    .grade-badge.grade-s {
        background: #fde68a;
        color: #78350f;
    }

    .grade-badge.grade-f {
        background: #fee2e2;
        color: #991b1b;
    }

    .grade-badge.grade-aplus {
        background: #d1fae5;
        color: #065f46;
    }

    .grade-badge.grade-a {
        background: #d1fae5;
        color: #065f46;
    }

    /* ============================================ */
    /* STUDENTS COUNT                              */
    /* ============================================ */
    .students-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.8rem;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.8rem;
    }

    /* ============================================ */
    /* STATUS BADGES                               */
    /* ============================================ */
    .badge.status-active {
        background: #d1fae5;
        color: #065f46;
        padding: 0.3rem 0.7rem;
        border-radius: 50px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .badge.status-inactive {
        background: #fee2e2;
        color: #991b1b;
        padding: 0.3rem 0.7rem;
        border-radius: 50px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    /* ============================================ */
    /* ACTION BUTTONS                              */
    /* ============================================ */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        font-size: 0.9rem;
    }

    .action-btn:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .edit-btn {
        background: #fef3c7;
        color: #d97706;
    }

    .edit-btn:hover {
        background: #d97706;
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

    /* ============================================ */
    /* TABLE FOOTER                                */
    /* ============================================ */
    .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .footer-right {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
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

    .modal-header-primary {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: white;
    }

    .modal-header-primary .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-header-warning {
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        color: white;
    }

    .modal-header-warning .btn-close {
        filter: brightness(0) invert(1);
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
    .form-group { margin-bottom: 0; }

    .form-group .form-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

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

    .custom-input.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .form-check {
        padding-left: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-check .form-check-input {
        width: 48px;
        height: 26px;
        border-radius: 26px;
        border: 2px solid #e2e8f0;
        background: #f1f5f9;
        cursor: pointer;
        transition: all 0.3s ease;
        margin: 0;
    }

    .form-check .form-check-input:checked {
        background: #2563eb;
        border-color: #2563eb;
    }

    .form-check .form-check-input:focus {
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .form-check .form-check-label {
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
    }

    /* ============================================ */
    /* DATATABLE OVERRIDES                         */
    /* ============================================ */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 14px !important;
        border: 2px solid #e2e8f0 !important;
        padding: 0.5rem 1rem !important;
        min-height: 42px;
        margin-left: 0.5rem;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 10px !important;
        border: 2px solid #e2e8f0 !important;
        padding: 0.3rem 1rem !important;
        min-height: 38px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 10px !important;
        padding: 0.3rem 0.8rem !important;
        margin: 0 2px !important;
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        background: white !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #2563eb !important;
        color: white !important;
        border-color: #2563eb !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    /* ============================================ */
    /* RESPONSIVE                                  */
    /* ============================================ */
    @media (max-width: 992px) {
        .page-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-right { width: 100%; }
        .header-right .custom-btn { flex: 1; justify-content: center; }
        .main-card { padding: 1.25rem; }
        .table-footer {
            flex-direction: column;
            align-items: stretch;
            text-align: center;
        }
        .footer-right { justify-content: center; }
    }

    @media (max-width: 768px) {
        .header-icon { width: 40px; height: 40px; font-size: 1.2rem; }
        .header-left h4 { font-size: 1.1rem; }
        .main-card { padding: 1rem; }

        .stats-card { padding: 1rem; }
        .stats-icon { width: 42px; height: 42px; font-size: 1.1rem; }
        .stats-card h3 { font-size: 1.1rem; }

        .custom-table thead th {
            font-size: 0.65rem;
            padding: 0.5rem;
        }

        .custom-table tbody td {
            padding: 0.5rem;
            font-size: 0.8rem;
        }

        .grade-badge { font-size: 0.75rem; padding: 0.2rem 0.6rem; }
        .action-btn { width: 32px; height: 32px; font-size: 0.8rem; }
        .custom-btn-sm { font-size: 0.7rem; padding: 0.25rem 0.6rem; }
        .footer-right .custom-btn-sm { flex: 1; justify-content: center; }
    }

    @media (max-width: 576px) {
        .main-card { padding: 0.75rem; border-radius: 20px; }

        .stats-card {
            flex-direction: column;
            text-align: center;
        }

        .custom-table {
            font-size: 0.7rem;
        }

        .custom-table thead th {
            font-size: 0.6rem;
            padding: 0.35rem;
            white-space: nowrap;
        }

        .custom-table tbody td {
            padding: 0.35rem;
            font-size: 0.65rem;
        }

        .grade-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
        }

        .action-btn {
            width: 28px;
            height: 28px;
            font-size: 0.7rem;
        }

        .row-number {
            width: 24px;
            height: 24px;
            font-size: 0.65rem;
        }

        .custom-btn {
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
        }

        .modal-body { padding: 1rem; }
        .modal-header { padding: 1rem; }
        .modal-footer { padding: 1rem; }

        .students-count-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
        }

        .badge.status-active,
        .badge.status-inactive {
            font-size: 0.65rem;
            padding: 0.15rem 0.5rem;
        }
    }
    

    /* ============================================ */
    /* PRINT STYLES                                */
    /* ============================================ */
    @media print {
        .page-header .header-right,
        .footer-right,
        .alert,
        .btn-close,
        .action-buttons {
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

        .custom-table thead th {
            background: #f5f5f5 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ============================================
        // 1. EDIT MODAL - Populate with data
        // ============================================
        const editButtons = document.querySelectorAll('.editBtn');

        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                let id = this.dataset.id;
                let name = this.dataset.name;
                let status = this.dataset.status;

                document.getElementById('edit_grade_name').value = name;
                document.getElementById('edit_is_active').checked = status == 1 ? true : false;
                document.getElementById('editGradeForm').action = "{{ url('admin/grades') }}/" + id;

                let modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
                modal.show();
            });
        });

        // ============================================
        // 2. DataTable Initialization (Existing)
        // ============================================
        if ($.fn.DataTable) {
            $('#gradeTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                ordering: true,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    zeroRecords: "No Grades Found",
                    info: "Showing _START_ to _END_ of _TOTAL_ Grades",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                }
            });
        }

        // ============================================
        // 3. Reopen Modal on Validation Error
        // ============================================
        @if($errors->any())
            var modal = new bootstrap.Modal(document.getElementById('createGradeModal'));
            modal.show();
        @endif

        // ============================================
        // 4. Keyboard Shortcut - Ctrl+N
        // ============================================
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                const createBtn = document.querySelector('[data-bs-target="#createGradeModal"]');
                if (createBtn) {
                    createBtn.click();
                }
            }
        });

        console.log('✅ Grade Management page initialized');
    });
</script>
@endpush