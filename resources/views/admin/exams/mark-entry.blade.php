@extends('layouts.app')

@section('title', 'Mark Entry')
@section('page-title', 'Mark Entry')

@section('content')

<div class="mark-entry-page">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="header-left">
            <div class="header-icon">
                <i class="bi bi-journal-check"></i>
            </div>
            <div>
                <h4>Mark Entry</h4>
                <p>Enter marks for <strong>{{ $exam->title }}</strong></p>
            </div>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary custom-btn">
                <i class="bi bi-arrow-left"></i>
                Back to Exams
            </a>
        </div>
    </div>

    <!-- EXAM INFO CARD -->
    <div class="exam-info-card">
        <div class="exam-info-grid">
            <div class="info-item">
                <span class="info-label"><i class="bi bi-journal-text"></i> Exam</span>
                <span class="info-value">{{ $exam->title }}</span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="bi bi-people"></i> Class</span>
                <span class="info-value">{{ $exam->studentClass?->class_name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="bi bi-tags"></i> Category</span>
                <span class="info-value">{{ $exam->category?->category_name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="bi bi-calendar"></i> Date</span>
                <span class="info-value">{{ $exam->exam_date?->format('d M Y') ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="bi bi-clock"></i> Time</span>
                <span class="info-value">
                    {{ $exam->start_time ? \Carbon\Carbon::parse($exam->start_time)->format('h:i A') : 'N/A' }} - 
                    {{ $exam->end_time ? \Carbon\Carbon::parse($exam->end_time)->format('h:i A') : 'N/A' }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label"><i class="bi bi-building"></i> Hall</span>
                <span class="info-value">{{ $exam->hall?->hall_name ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- ALERTS -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
                <div class="alert-icon"><i class="bi bi-check-circle-fill"></i></div>
                <div class="alert-content">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                <div class="alert-icon"><i class="bi bi-x-circle-fill"></i></div>
                <div class="alert-content">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div class="alert-content">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li><i class="bi bi-dot"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- FORM -->
        <form action="{{ route('admin.exams.save-marks', $exam->id) }}" method="POST" id="markEntryForm">
            @csrf

            <!-- MAX MARKS -->
            <div class="max-marks-section">
                <div class="max-marks-input">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-star text-primary"></i>
                        Maximum Marks
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-arrow-up"></i></span>
                        <input type="number" name="max_marks" class="form-control form-control-lg custom-input" 
                               value="{{ old('max_marks', 100) }}" min="1" step="0.01" required 
                               id="maxMarks" placeholder="Enter maximum marks">
                        <span class="input-group-text">marks</span>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        Set the maximum marks for this exam. Leave marks empty for absent students.
                    </small>
                </div>

                <div class="students-count">
                    <span class="count-number">{{ $students->count() }}</span>
                    <span class="count-label">Students</span>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Student</th>
                            <th width="220">Marks</th>
                            <th width="100">Status</th>
                            <th width="110">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            @php
                                $result = $student->student?->results?->first();
                                $marks = old('marks.' . $student->student_id . '.marks', $result?->marks);
                                $isAbsent = old('marks.' . $student->student_id . '.is_absent', $result?->is_absent ?? false);
                                $hasMarks = !is_null($marks) && $marks !== '' && !$isAbsent;
                            @endphp

                            <tr class="student-row {{ $isAbsent ? 'absent' : '' }} {{ $hasMarks ? 'has-marks' : '' }}"
                                data-student-id="{{ $student->student_id }}">
                                <td>
                                    <span class="row-number">{{ $loop->iteration }}</span>
                                </td>

                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            {{ $student->student ? strtoupper(substr($student->student->initial_name, 0, 2)) : '??' }}
                                        </div>
                                        <div>
                                            <div class="student-name">
                                                {{ $student->student?->initial_name ?? 'Unknown' }}
                                            </div>
                                            @if ($student->student?->custom_id)
                                                <small class="text-muted">
                                                    <i class="bi bi-id-card"></i>
                                                    {{ $student->student->custom_id }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="marks-input-wrapper">
                                        <input type="number" step="0.01" min="0" 
                                               name="marks[{{ $student->student_id }}][marks]"
                                               value="{{ $marks }}"
                                               class="form-control marks-input {{ $hasMarks ? 'is-valid' : '' }} {{ $isAbsent ? 'absent-field' : '' }}"
                                               placeholder="Enter marks..."
                                               data-student="{{ $student->student?->initial_name ?? 'Unknown' }}"
                                               data-student-id="{{ $student->student_id }}"
                                               {{ $isAbsent ? 'disabled' : '' }}>
                                        
                                        <input type="hidden" name="marks[{{ $student->student_id }}][is_absent]" 
                                               id="absent_{{ $student->student_id }}" 
                                               value="{{ $isAbsent ? '1' : '0' }}">
                                        
                                        @if ($hasMarks)
                                            <span class="marks-status"><i class="bi bi-check-circle-fill text-success"></i></span>
                                        @endif
                                        @if ($isAbsent)
                                            <span class="marks-status"><i class="bi bi-person-x-fill text-danger"></i></span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="status-badge {{ $isAbsent ? 'status-absent' : ($hasMarks ? 'status-filled' : 'status-pending') }}">
                                        <i class="bi bi-{{ $isAbsent ? 'person-x' : ($hasMarks ? 'check-circle' : 'hourglass') }}"></i>
                                        {{ $isAbsent ? 'Absent' : ($hasMarks ? 'Filled' : 'Pending') }}
                                    </span>
                                </td>

                                <td>
                                    <button type="button" 
                                            class="absent-toggle-btn {{ $isAbsent ? 'btn-success' : 'btn-outline-danger' }}"
                                            data-student-id="{{ $student->student_id }}"
                                            title="{{ $isAbsent ? 'Mark as Present' : 'Mark as Absent' }}">
                                        <i class="bi bi-{{ $isAbsent ? 'person-check' : 'person-x' }}"></i>
                                        {{ $isAbsent ? 'Present' : 'Absent' }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-3"></i>
                                    <h5>No students found</h5>
                                    <p class="mb-0">This class has no students enrolled.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ACTION BUTTONS -->
            @if ($students->count())
                <div class="form-actions">
                    <div class="actions-left">
                        <div class="progress-info">
                            <span class="progress-label">Completion</span>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" id="progressFill"></div>
                            </div>
                            <span class="progress-text" id="progressText">0%</span>
                        </div>
                        <div class="stats-info">
                            <span class="stat-item">
                                <span class="stat-dot filled"></span>
                                <span id="filledCount">0</span> Filled
                            </span>
                            <span class="stat-item">
                                <span class="stat-dot absent"></span>
                                <span id="absentCount">0</span> Absent
                            </span>
                            <span class="stat-item">
                                <span class="stat-dot pending"></span>
                                <span id="pendingCount">0</span> Pending
                            </span>
                        </div>
                    </div>
                    <div class="actions-right">
                        <button type="submit" class="btn btn-primary custom-submit-btn">
                            <i class="bi bi-save"></i>
                            Save All Results
                        </button>
                        <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary custom-btn">
                            <i class="bi bi-x"></i>
                            Cancel
                        </a>
                    </div>
                </div>
            @endif

        </form>

    </div>
</div>

@endsection

@push('styles')
<style>
    /* ============================================ */
    /* PAGE ANIMATION                              */
    /* ============================================ */
    .mark-entry-page {
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
        background: linear-gradient(135deg, #2563eb, #3b82f6);
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
        font-size: 0.9rem;
    }

    .header-left p strong { color: #0f172a; }

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

    .custom-btn:active { transform: translateY(0); }

    /* ============================================ */
    /* EXAM INFO CARD                              */
    /* ============================================ */
    .exam-info-card {
        background: white;
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .exam-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
    }

    .exam-info-card .info-item {
        display: flex;
        flex-direction: column;
        padding: 0.4rem 0.6rem;
    }

    .exam-info-card .info-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .exam-info-card .info-label i { font-size: 0.7rem; }

    .exam-info-card .info-value {
        font-weight: 600;
        color: #0f172a;
        font-size: 0.9rem;
        margin-top: 0.1rem;
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
    .custom-alert ul { padding-left: 0; list-style: none; }
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
    /* MAX MARKS SECTION                           */
    /* ============================================ */
    .max-marks-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #eef2f7;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .max-marks-input { flex: 1; min-width: 250px; }

    .max-marks-input .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
        color: #1e293b;
    }

    .max-marks-input .input-group { max-width: 300px; }

    .max-marks-input .input-group .input-group-text {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 14px 0 0 14px;
        color: #64748b;
        font-weight: 500;
        padding: 0 1rem;
    }

    .max-marks-input .input-group .input-group-text:last-child {
        border-radius: 0 14px 14px 0;
    }

    .max-marks-input .form-control {
        border-radius: 0;
        border: 2px solid #e2e8f0;
        padding: 0.7rem 1rem;
        transition: all 0.3s ease;
        font-size: 1rem;
        font-weight: 600;
        height: 52px;
        background: white;
    }

    .max-marks-input .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        z-index: 2;
    }

    .max-marks-input .form-control.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
    }

    .max-marks-input .form-text {
        font-size: 0.78rem;
        color: #94a3b8;
        margin-top: 0.3rem;
    }

    .students-count {
        text-align: center;
        padding: 0.5rem 1.5rem;
        background: white;
        border-radius: 14px;
        border: 1px solid #eef2f7;
    }

    .students-count .count-number {
        display: block;
        font-size: 2rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .students-count .count-label {
        font-size: 0.75rem;
        color: #94a3b8;
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
        font-size: 0.82rem;
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
    .custom-table tbody tr.has-marks { background: #f0fdf4; }
    .custom-table tbody tr.has-marks:hover { background: #dcfce7; }
    .custom-table tbody tr.absent { background: #fef2f2; }
    .custom-table tbody tr.absent:hover { background: #fee2e2; }

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
    /* STUDENT INFO                                */
    /* ============================================ */
    .student-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb, #60a5fa);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .student-name {
        font-weight: 600;
        color: #0f172a;
    }

    /* ============================================ */
    /* MARKS INPUT                                 */
    /* ============================================ */
    .marks-input-wrapper {
        position: relative;
        max-width: 200px;
    }

    .marks-input {
        border-radius: 12px !important;
        border: 2px solid #e2e8f0;
        padding: 0.6rem 1rem;
        transition: all 0.3s ease;
        background: #fafbfc;
        height: 44px;
        font-weight: 600;
        font-size: 1rem;
    }

    .marks-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        background: white;
    }

    .marks-input.is-valid {
        border-color: #10b981;
        background: #f0fdf4;
        padding-right: 35px;
    }

    .marks-input.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .marks-input:focus.is-valid {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .marks-input.absent-field {
        background: #fef2f2;
        border-color: #fca5a5;
        color: #991b1b;
        opacity: 0.6;
        cursor: not-allowed;
    }

    .marks-status {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.1rem;
    }

    /* ============================================ */
    /* STATUS BADGE                                */
    /* ============================================ */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.7rem;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-badge i { font-size: 0.8rem; }

    .status-filled {
        background: #d1fae5;
        color: #065f46;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-absent {
        background: #fee2e2;
        color: #991b1b;
    }

    /* ============================================ */
    /* ABSENT TOGGLE BUTTON                        */
    /* ============================================ */
    .absent-toggle-btn {
        border-radius: 10px;
        padding: 0.3rem 0.7rem;
        font-size: 0.7rem;
        font-weight: 600;
        border: 2px solid transparent;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        white-space: nowrap;
        cursor: pointer;
    }

    .absent-toggle-btn.btn-outline-danger {
        border-color: #fca5a5;
        color: #dc2626;
        background: transparent;
    }

    .absent-toggle-btn.btn-outline-danger:hover {
        background: #fee2e2;
        border-color: #dc2626;
    }

    .absent-toggle-btn.btn-success {
        background: #dcfce7;
        color: #16a34a;
        border-color: #86efac;
    }

    .absent-toggle-btn.btn-success:hover {
        background: #bbf7d0;
        border-color: #16a34a;
    }

    /* ============================================ */
    /* FORM ACTIONS                                */
    /* ============================================ */
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .actions-left {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .progress-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .progress-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
    }

    .progress-bar-container {
        width: 120px;
        height: 8px;
        background: #f1f5f9;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(135deg, #10b981, #34d399);
        border-radius: 10px;
        transition: width 0.5s ease;
        width: 0%;
    }

    .progress-text {
        font-size: 0.85rem;
        font-weight: 700;
        color: #0f172a;
        min-width: 40px;
    }

    .stats-info {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }

    .stat-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .stat-dot.filled { background: #10b981; }
    .stat-dot.absent { background: #ef4444; }
    .stat-dot.pending { background: #f59e0b; }

    .actions-right {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .custom-submit-btn {
        border-radius: 14px;
        padding: 0.7rem 1.5rem;
        font-weight: 600;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: white;
    }

    .custom-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
    }

    .custom-submit-btn:active { transform: translateY(0); }

    .custom-submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
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
        .exam-info-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 768px) {
        .header-icon { width: 40px; height: 40px; font-size: 1.2rem; }
        .header-left h4 { font-size: 1.1rem; }
        .main-card { padding: 1rem; }
        .exam-info-card { padding: 1rem; }
        .exam-info-grid { grid-template-columns: 1fr; gap: 0.5rem; }

        .max-marks-section {
            flex-direction: column;
            align-items: stretch;
        }

        .max-marks-input .input-group { max-width: 100%; }

        .students-count {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
        }

        .students-count .count-number {
            font-size: 1.5rem;
            margin: 0;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .actions-left {
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .stats-info {
            flex-wrap: wrap;
            justify-content: center;
        }

        .actions-right { justify-content: center; }
        .actions-right .custom-btn,
        .actions-right .custom-submit-btn { flex: 1; justify-content: center; }

        .progress-bar-container { width: 100px; }

        .custom-table thead th {
            font-size: 0.7rem;
            padding: 0.5rem;
        }

        .custom-table tbody td {
            padding: 0.5rem;
            font-size: 0.85rem;
        }

        .marks-input-wrapper { max-width: 100%; }
        .marks-input { height: 38px; font-size: 0.9rem; }
        .student-avatar { width: 32px; height: 32px; font-size: 0.7rem; }
    }

    @media (max-width: 576px) {
        .exam-info-grid { grid-template-columns: 1fr; }
        .exam-info-card .info-value { font-size: 0.85rem; }
        .custom-table { font-size: 0.8rem; }

        .status-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
        }

        .main-card {
            padding: 0.75rem;
            border-radius: 20px;
        }

        .progress-info {
            flex-wrap: wrap;
            justify-content: center;
        }

        .custom-submit-btn {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }

        .custom-btn {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }

        .student-info { gap: 0.5rem; }
        .absent-toggle-btn {
            font-size: 0.6rem;
            padding: 0.15rem 0.5rem;
        }
    }

    /* ============================================ */
    /* LOADING STATE                               */
    /* ============================================ */
    .btn-loading {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 24px;
        height: 24px;
        top: 50%;
        left: 50%;
        margin: -12px 0 0 -12px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ============================================ */
    /* PRINT STYLES                                */
    /* ============================================ */
    @media print {
        .page-header .header-right,
        .form-actions,
        .alert,
        .btn-close,
        .absent-toggle-btn {
            display: none !important;
        }

        .main-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        .exam-info-card {
            border: 1px solid #ddd !important;
        }

        .custom-table thead th {
            background: #f5f5f5 !important;
        }

        .marks-input {
            border: 1px solid #ddd !important;
            background: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // ============================================
        // 1. ABSENT TOGGLE FUNCTIONALITY
        // ============================================
        document.querySelectorAll('.absent-toggle-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const studentId = this.dataset.studentId;
                const row = this.closest('tr');
                const marksInput = row.querySelector('.marks-input');
                const hiddenInput = document.getElementById('absent_' + studentId);
                const statusBadge = row.querySelector('.status-badge');
                const marksStatus = row.querySelector('.marks-status');

                const isCurrentlyAbsent = hiddenInput.value === '1';

                // Toggle absent status
                const newAbsent = !isCurrentlyAbsent;
                hiddenInput.value = newAbsent ? '1' : '0';

                // Update UI
                if (newAbsent) {
                    // Mark as Absent
                    row.classList.add('absent');
                    row.classList.remove('has-marks');
                    marksInput.disabled = true;
                    marksInput.value = '';
                    marksInput.classList.remove('is-valid');
                    marksInput.classList.add('absent-field');

                    this.className = 'absent-toggle-btn btn-success';
                    this.innerHTML = '<i class="bi bi-person-check"></i> Present';
                    this.title = 'Mark as Present';

                    statusBadge.className = 'status-badge status-absent';
                    statusBadge.innerHTML = '<i class="bi bi-person-x"></i> Absent';

                    if (marksStatus) {
                        marksStatus.innerHTML = '<i class="bi bi-person-x-fill text-danger"></i>';
                    }
                } else {
                    // Mark as Present
                    row.classList.remove('absent');
                    marksInput.disabled = false;
                    marksInput.classList.remove('absent-field');
                    marksInput.focus();

                    this.className = 'absent-toggle-btn btn-outline-danger';
                    this.innerHTML = '<i class="bi bi-person-x"></i> Absent';
                    this.title = 'Mark as Absent';

                    statusBadge.className = 'status-badge status-pending';
                    statusBadge.innerHTML = '<i class="bi bi-hourglass"></i> Pending';

                    if (marksStatus) {
                        marksStatus.innerHTML = '';
                    }
                }

                updateStats();
                updateProgress();
            });
        });

        // ============================================
        // 2. PROGRESS BAR & STATS CALCULATION
        // ============================================
        const marksInputs = document.querySelectorAll('.marks-input');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const filledCountEl = document.getElementById('filledCount');
        const absentCountEl = document.getElementById('absentCount');
        const pendingCountEl = document.getElementById('pendingCount');

        function updateStats() {
            let filled = 0;
            let absent = 0;
            let pending = 0;

            document.querySelectorAll('.student-row').forEach(function(row) {
                const marksInput = row.querySelector('.marks-input');
                const hiddenInput = row.querySelector('input[type="hidden"][name*="[is_absent]"]');
                const isAbsent = hiddenInput && hiddenInput.value === '1';
                const hasMarks = marksInput && marksInput.value && parseFloat(marksInput.value) >= 0;

                if (isAbsent) {
                    absent++;
                } else if (hasMarks) {
                    filled++;
                } else {
                    pending++;
                }
            });

            if (filledCountEl) filledCountEl.textContent = filled;
            if (absentCountEl) absentCountEl.textContent = absent;
            if (pendingCountEl) pendingCountEl.textContent = pending;
        }

        function updateProgress() {
            let filled = 0;
            let total = marksInputs.length;

            marksInputs.forEach(function(input) {
                const row = input.closest('tr');
                const hiddenInput = row.querySelector('input[type="hidden"][name*="[is_absent]"]');
                const isAbsent = hiddenInput && hiddenInput.value === '1';

                if (isAbsent || (input.value && parseFloat(input.value) >= 0)) {
                    filled++;
                }
            });

            const percentage = total > 0 ? Math.round((filled / total) * 100) : 0;

            if (progressFill) {
                progressFill.style.width = percentage + '%';
            }

            if (progressText) {
                progressText.textContent = percentage + '%';
            }

            updateStats();
        }

        // Update on input change
        marksInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                const row = this.closest('tr');
                const hiddenInput = row.querySelector('input[type="hidden"][name*="[is_absent]"]');
                const isAbsent = hiddenInput && hiddenInput.value === '1';
                const statusBadge = row.querySelector('.status-badge');
                const marksStatus = row.querySelector('.marks-status');

                // Don't update if absent
                if (isAbsent) return;

                if (this.value && parseFloat(this.value) >= 0) {
                    row.classList.add('has-marks');
                    row.classList.remove('absent');
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');

                    statusBadge.className = 'status-badge status-filled';
                    statusBadge.innerHTML = '<i class="bi bi-check-circle"></i> Filled';

                    if (marksStatus) {
                        marksStatus.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                    }
                } else {
                    row.classList.remove('has-marks');
                    this.classList.remove('is-valid');
                    this.classList.remove('is-invalid');

                    statusBadge.className = 'status-badge status-pending';
                    statusBadge.innerHTML = '<i class="bi bi-hourglass"></i> Pending';

                    if (marksStatus) {
                        marksStatus.innerHTML = '';
                    }
                }

                updateProgress();
            });
        });

        // Initial update
        updateProgress();

        // ============================================
        // 3. MAX MARKS VALIDATION
        // ============================================
        const maxMarksInput = document.getElementById('maxMarks');

        if (maxMarksInput) {
            maxMarksInput.addEventListener('change', function() {
                const value = parseFloat(this.value);
                if (value < 1 || isNaN(value)) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        }

        // ============================================
        // 4. FORM SUBMISSION VALIDATION
        // ============================================
        const form = document.getElementById('markEntryForm');

        if (form) {
            form.addEventListener('submit', function(e) {
                let hasErrors = false;

                // Validate max marks
                const maxMarks = document.getElementById('maxMarks');
                if (maxMarks && (parseFloat(maxMarks.value) < 1 || isNaN(parseFloat(maxMarks.value)))) {
                    maxMarks.classList.add('is-invalid');
                    hasErrors = true;
                }

                // Validate each marks input (only if not absent)
                marksInputs.forEach(function(input) {
                    const row = input.closest('tr');
                    const hiddenInput = row.querySelector('input[type="hidden"][name*="[is_absent]"]');
                    const isAbsent = hiddenInput && hiddenInput.value === '1';

                    if (!isAbsent) {
                        const value = parseFloat(input.value);
                        const max = parseFloat(maxMarks?.value || 100);

                        if (isNaN(value) || value < 0 || value > max) {
                            input.classList.add('is-invalid');
                            hasErrors = true;
                        } else if (value >= 0) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        }
                    }
                });

                if (hasErrors) {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fix all errors before submitting.',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });

                    const firstError = document.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                } else {
                    const submitBtn = this.querySelector('.custom-submit-btn');
                    submitBtn.classList.add('btn-loading');
                    submitBtn.disabled = true;
                }
            });
        }

        // ============================================
        // 5. KEYBOARD SHORTCUTS
        // ============================================
        document.addEventListener('keydown', function(e) {
            // Ctrl + S to save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }

            // Enter to go to next input (skip absent fields)
            if (e.key === 'Enter') {
                const target = e.target;
                if (target.classList.contains('marks-input')) {
                    e.preventDefault();
                    const inputs = Array.from(marksInputs);
                    const index = inputs.indexOf(target);

                    // Find next non-disabled input
                    let nextIndex = index + 1;
                    while (nextIndex < inputs.length) {
                        if (!inputs[nextIndex].disabled) {
                            inputs[nextIndex].focus();
                            return;
                        }
                        nextIndex++;
                    }

                    // Submit if last input
                    if (form) {
                        form.dispatchEvent(new Event('submit'));
                    }
                }
            }
        });

        console.log('✅ Mark entry page initialized with absent support');
    });
</script>
@endpush