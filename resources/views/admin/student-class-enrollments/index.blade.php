@extends('layouts.app')

@section('title', 'Student Class Enrollments')
@section('page-title', 'Student Class Enrollments')

@section('content')

    <div class="enrollments-page">

        <!-- STATS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $classes->total() }}</h3>
                        <p>Total Classes</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h3>
                            {{ $classes->sum('total_students_count') }}
                        </h3>
                        <p>Total Enrollments</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <h3>
                            {{ $classes->sum('active_students_count') }}
                        </h3>
                        <p>Active Students</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon red">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div>
                        <h3>
                            {{ $classes->sum('inactive_students_count') }}
                        </h3>
                        <p>Inactive Students</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <!-- HEADER -->
            <div class="main-card-header">

                <div>
                    <h4>Student Class Enrollments</h4>
                    <p>Manage class-wise enrollment, fees and student counts</p>
                </div>

                <div class="header-buttons">
                    <a href="{{ route('admin.student-class-enrollments.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg"></i>
                        Add Enrollment
                    </a>

                    <!-- Future feature -->
                    <button class="btn btn-light border custom-btn" disabled>
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        Export
                    </button>
                </div>

            </div>

            <!-- SEARCH -->
            <div class="search-card">

                <form method="GET" action="{{ route('admin.student-class-enrollments.index') }}">
                    <div class="row g-3">

                        <div class="col-lg-8">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search by class, grade, subject, teacher..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <select name="per_page" class="form-select custom-input">
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <div class="col-lg-1">
                            <button type="submit" class="btn btn-primary w-100 custom-btn">
                                Search
                            </button>
                        </div>

                        <div class="col-lg-1">
                            <a href="{{ route('admin.student-class-enrollments.index') }}"
                                class="btn btn-light border w-100 custom-btn">
                                Reset
                            </a>
                        </div>

                    </div>

                    @if(request('search') || request('per_page'))
                        <div class="mt-3">
                            <a href="{{ route('admin.student-class-enrollments.index') }}"
                                class="btn btn-sm btn-outline-secondary custom-btn">
                                Clear Filter
                            </a>
                        </div>
                    @endif
                </form>

            </div>

            <!-- CLASSES LIST -->
            @forelse($classes as $class)
                <div class="class-card mb-4">

                    <div class="class-card-header">
                        <div>
                            <h5 class="mb-1 fw-bold">{{ $class->class_name }}</h5>
                            <div class="text-muted small">
                                Grade: {{ optional($class->grade)->grade_name ?? '-' }}
                                <span class="mx-2">|</span>
                                Subject: {{ optional($class->subject)->subject_name ?? '-' }}
                                <span class="mx-2">|</span>
                                Teacher: {{ optional($class->teacher)->initials ?? '-' }}
                            </div>
                        </div>

                        <div class="class-badges">
                            <span class="badge bg-primary">Total: {{ $class->total_students_count }}</span>
                            <span class="badge bg-success">Active: {{ $class->active_students_count }}</span>
                            <span class="badge bg-secondary">Inactive: {{ $class->inactive_students_count }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table custom-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Default Fee</th>
                                    <th>Total</th>
                                    <th>Active</th>
                                    <th>Inactive</th>
                                    <th>Default Fee Students</th>
                                    <th>Custom Fee Students</th>
                                    <th>Free Card Students</th>
                                    <th width="120" class="text-end">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($class->categoryFees as $fee)
                                    @php
                                        $classStats = $categoryStats->get($class->id);
                                        $stats = $classStats ? $classStats->firstWhere('class_category_fee_id', $fee->id) : null;
                                    @endphp

                                    <tr>
                                        <td>
                                            <span class="fw-semibold">
                                                {{ optional($fee->category)->category_name ?? '-' }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ number_format($fee->fee, 2) }}
                                        </td>

                                        <td>
                                            <span class="badge bg-primary custom-badge">
                                                {{ $stats ? $stats->total_count : 0 }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-success custom-badge">
                                                {{ $stats ? $stats->active_count : 0 }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-secondary custom-badge">
                                                {{ $stats ? $stats->inactive_count : 0 }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-info custom-badge">
                                                {{ $stats ? $stats->default_fee_count : 0 }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-warning text-dark custom-badge">
                                                {{ $stats ? $stats->custom_fee_count : 0 }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-dark custom-badge">
                                                {{ $stats ? $stats->free_card_count : 0 }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            @if($fee->category)
                                                <a href="{{ route('admin.student-class-enrollments.categoryStudents', [$class->id, $fee->category->id]) }}"
                                                    class="action-btn view-btn" title="View students">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <div class="empty-state">
                                                <i class="bi bi-people"></i>
                                                <h5>No Categories Found</h5>
                                                <p>This class has no category fees yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            @empty
                <div class="empty-main">
                    <div class="alert alert-info border-0 shadow-sm">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        No enrolled classes found.
                    </div>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $classes->links() }}
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <style>
        .enrollments-page {
            animation: fadeIn .4s ease;
        }

        .stats-card {
            background: #fff;
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .04);
            border: 1px solid #eef2f7;
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

        .stats-card h3 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .stats-card p {
            margin: 0;
            color: #64748b;
        }

        .main-card {
            background: #fff;
            border-radius: 28px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
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
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
        }

        .header-buttons {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.2rem;
            font-weight: 600;
            border: none;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .search-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.5rem;
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
        }

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
            padding-left: 42px;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .class-card {
            background: #fff;
            border-radius: 24px;
            padding: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .04);
            border: 1px solid #eef2f7;
        }

        .class-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .class-badges {
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
        }

        .custom-table tbody tr {
            transition: .2s ease;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
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
            transition: .2s ease;
            background: #eff6ff;
            color: #2563eb;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .view-btn {
            background: #eff6ff;
            color: #2563eb;
        }

        .empty-state {
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
        }

        .empty-main {
            margin-bottom: 1rem;
        }

        @media(max-width:768px) {
            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-buttons {
                width: 100%;
            }

            .header-buttons a,
            .header-buttons button {
                flex: 1;
            }
        }
    </style>
@endpush