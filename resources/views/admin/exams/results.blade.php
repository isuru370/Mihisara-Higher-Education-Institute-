@extends('layouts.app')

@section('title', 'Exam Results')
@section('page-title', 'Exam Results')

@section('content')

    <div class="results-page">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <div class="header-left">
                <div class="header-icon">
                    <i class="bi bi-bar-chart-fill"></i>
                </div>
                <div>
                    <h4>Exam Results</h4>
                    <p>View results for <strong>{{ $exam->title }}</strong></p>
                </div>
            </div>
            <div class="header-right">
                <a href="{{ route('admin.exams.mark-entry', $exam->id) }}" class="btn btn-warning custom-btn">
                    <i class="bi bi-pencil"></i>
                    Edit Marks
                </a>
                <form action="{{ route('admin.exams.recalculate-ranks', $exam->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary custom-btn">
                        <i class="bi bi-arrow-repeat"></i>
                        Recalculate Ranks
                    </button>
                </form>
                <a href="{{ route('admin.exams.results.excel', $exam->id) }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    Export Excel
                </a>

                <a href="{{ route('admin.exams.results.pdf', $exam->id) }}" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    Export PDF
                </a>
                <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back
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

            <!-- STATS ROW -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon blue">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h3>{{ $results->count() }}</h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon green">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div>
                            <h3>{{ $results->where('status', 'passed')->count() }}</h3>
                            <p>Passed</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon red">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                        <div>
                            <h3>{{ $results->where('status', 'failed')->count() }}</h3>
                            <p>Failed</p>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="stats-card">
                        <div class="stats-icon orange">
                            <i class="bi bi-person-x-fill"></i>
                        </div>
                        <div>
                            <h3>{{ $results->where('status', 'absent')->count() }}</h3>
                            <p>Absent</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th width="70">Rank</th>
                            <th>Student</th>
                            <th width="130">Marks</th>
                            <th width="120">Max Marks</th>
                            <th width="120">%</th>
                            <th width="100">Grade</th>
                            <th width="120">Status</th>
                            <th>Entered By</th>
                            <th width="160">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                            @php
                                $isAbsent = $result->status === 'absent' || $result->is_absent;
                                $rankColors = [
                                    1 => 'gold',
                                    2 => 'silver',
                                    3 => 'bronze',
                                ];
                                $rankClass = $rankColors[$result->rank] ?? '';
                            @endphp

                            <tr class="{{ $isAbsent ? 'absent-row' : '' }}">
                                <td>
                                    @if ($isAbsent)
                                        <span class="badge bg-secondary">-</span>
                                    @elseif($result->rank == 1)
                                        <span class="rank-badge rank-1">🥇 1</span>
                                    @elseif($result->rank == 2)
                                        <span class="rank-badge rank-2">🥈 2</span>
                                    @elseif($result->rank == 3)
                                        <span class="rank-badge rank-3">🥉 3</span>
                                    @else
                                        <span class="rank-number">{{ $result->rank ?? '-' }}</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar {{ $isAbsent ? 'absent-avatar' : '' }}">
                                            {{ $result->student ? strtoupper(substr($result->student->initial_name, 0, 2)) : '??' }}
                                        </div>
                                        <div>
                                            <div class="student-name {{ $isAbsent ? 'text-muted' : '' }}">
                                                {{ $result->student?->initial_name ?? 'Unknown' }}
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-id-card"></i>
                                                {{ $result->student?->custom_id ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($isAbsent)
                                        <span class="text-muted">-</span>
                                    @else
                                        <span class="fw-bold">{{ number_format($result->marks, 2) }}</span>
                                    @endif
                                </td>

                                <td>
                                    {{ number_format($result->max_marks, 2) }}
                                </td>

                                <td>
                                    @if ($isAbsent)
                                        <span class="text-muted">-</span>
                                    @else
                                        <span
                                            class="percentage-badge {{ $result->percentage >= 75 ? 'excellent' : ($result->percentage >= 65 ? 'good' : ($result->percentage >= 50 ? 'average' : ($result->percentage >= 35 ? 'pass' : 'fail'))) }}">
                                            {{ number_format($result->percentage, 2) }}%
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($isAbsent)
                                        <span class="badge bg-secondary">ABS</span>
                                    @else
                                        <span class="grade-badge grade-{{ strtolower($result->grade) }}">
                                            {{ $result->grade }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($result->status === 'passed')
                                        <span class="badge status-passed"><i class="bi bi-check-circle"></i> Passed</span>
                                    @elseif($result->status === 'failed')
                                        <span class="badge status-failed"><i class="bi bi-x-circle"></i> Failed</span>
                                    @elseif($result->status === 'absent' || $result->is_absent)
                                        <span class="badge status-absent"><i class="bi bi-person-x"></i> Absent</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="entered-by">
                                        <i class="bi bi-person-circle"></i>
                                        {{ $result->enteredBy?->name ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i>
                                        {{ $result->updated_at?->format('d M Y h:i A') ?? '-' }}
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-bar-chart fs-1 d-block mb-3"></i>
                                    <h5>No results available</h5>
                                    <p class="mb-0">No results have been recorded for this exam yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- FOOTER -->
            <div class="table-footer">
                <div class="footer-left">
                    <span class="text-muted">
                        Showing {{ $results->count() }} results
                    </span>
                </div>
                <div class="footer-right">
                    <a href="{{ route('admin.exams.mark-entry', $exam->id) }}"
                        class="btn btn-sm btn-warning custom-btn-sm">
                        <i class="bi bi-pencil"></i>
                        Edit Marks
                    </a>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* ============================================ */
        /* PAGE ANIMATION                              */
        /* ============================================ */
        .results-page {
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
            font-size: 0.9rem;
        }

        .header-left p strong {
            color: #0f172a;
        }

        .header-right {
            display: flex;
            gap: 0.7rem;
            flex-wrap: wrap;
        }

        .custom-btn {
            border-radius: 14px;
            padding: 0.6rem 1.2rem;
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
            padding: 0.35rem 0.8rem;
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

        .exam-info-card .info-label i {
            font-size: 0.7rem;
        }

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

        .custom-alert .alert-content {
            flex: 1;
        }

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

        .stats-card:hover .stats-icon {
            transform: scale(1.05);
        }

        .stats-icon.blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .stats-icon.green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .stats-icon.red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .stats-icon.orange {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

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
        .custom-table {
            margin-bottom: 0;
        }

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
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .custom-table tbody tr.absent-row {
            background: #fef2f2;
            opacity: 0.7;
        }

        .custom-table tbody tr.absent-row:hover {
            background: #fee2e2;
            opacity: 0.8;
        }

        .custom-table tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }

        /* ============================================ */
        /* RANK BADGES                                 */
        /* ============================================ */
        .rank-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.7rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.8rem;
            background: #f1f5f9;
            color: #475569;
        }

        .rank-1 {
            background: #fef3c7;
            color: #92400e;
        }

        .rank-2 {
            background: #e5e7eb;
            color: #374151;
        }

        .rank-3 {
            background: #fde68a;
            color: #78350f;
        }

        .rank-number {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
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
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .student-avatar.absent-avatar {
            background: linear-gradient(135deg, #9ca3af, #d1d5db);
            opacity: 0.6;
        }

        .student-name {
            font-weight: 600;
            color: #0f172a;
        }

        .student-name.text-muted {
            color: #94a3b8 !important;
        }

        /* ============================================ */
        /* PERCENTAGE BADGES                           */
        /* ============================================ */
        .percentage-badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .percentage-badge.excellent {
            background: #d1fae5;
            color: #065f46;
        }

        .percentage-badge.good {
            background: #dbeafe;
            color: #1e40af;
        }

        .percentage-badge.average {
            background: #fef3c7;
            color: #92400e;
        }

        .percentage-badge.pass {
            background: #fde68a;
            color: #78350f;
        }

        .percentage-badge.fail {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ============================================ */
        /* GRADE BADGES                                */
        /* ============================================ */
        .grade-badge {
            display: inline-block;
            padding: 0.25rem 0.7rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            min-width: 35px;
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

        /* ============================================ */
        /* STATUS BADGES                               */
        /* ============================================ */
        .badge.status-passed {
            background: #d1fae5;
            color: #065f46;
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .badge.status-failed {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .badge.status-absent {
            background: #fef3c7;
            color: #92400e;
            padding: 0.3rem 0.7rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .entered-by {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.85rem;
            color: #475569;
        }

        .entered-by i {
            color: #94a3b8;
            font-size: 0.9rem;
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

            .exam-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .table-footer {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .footer-right {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .header-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .header-left h4 {
                font-size: 1.1rem;
            }

            .main-card {
                padding: 1rem;
            }

            .exam-info-card {
                padding: 1rem;
            }

            .exam-info-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 42px;
                height: 42px;
                font-size: 1.1rem;
            }

            .stats-card h3 {
                font-size: 1.1rem;
            }

            .custom-table thead th {
                font-size: 0.65rem;
                padding: 0.5rem;
            }

            .custom-table tbody td {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            .student-avatar {
                width: 30px;
                height: 30px;
                font-size: 0.65rem;
            }

            .grade-badge {
                font-size: 0.7rem;
                padding: 0.15rem 0.5rem;
                min-width: 28px;
            }

            .percentage-badge {
                font-size: 0.7rem;
                padding: 0.1rem 0.4rem;
            }

            .custom-btn-sm {
                font-size: 0.7rem;
                padding: 0.25rem 0.6rem;
            }

            .footer-right .custom-btn-sm {
                flex: 1;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .exam-info-grid {
                grid-template-columns: 1fr;
            }

            .main-card {
                padding: 0.75rem;
                border-radius: 20px;
            }

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
                font-size: 0.7rem;
            }

            .student-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .student-avatar {
                width: 26px;
                height: 26px;
                font-size: 0.55rem;
            }

            .student-name {
                font-size: 0.75rem;
            }

            .rank-badge {
                font-size: 0.65rem;
                padding: 0.15rem 0.4rem;
            }

            .entered-by {
                font-size: 0.7rem;
            }

            .badge.status-passed,
            .badge.status-failed,
            .badge.status-absent {
                font-size: 0.6rem;
                padding: 0.15rem 0.4rem;
            }

            .custom-btn {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
            }
        }

        /* ============================================ */
        /* PRINT STYLES                                */
        /* ============================================ */
        @media print {

            .page-header .header-right,
            .footer-right,
            .alert,
            .btn-close {
                display: none !important;
            }

            .main-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .exam-info-card {
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

            .custom-table tbody tr.absent-row {
                background: #f5f5f5 !important;
                opacity: 0.6;
            }
        }
    </style>
@endpush
