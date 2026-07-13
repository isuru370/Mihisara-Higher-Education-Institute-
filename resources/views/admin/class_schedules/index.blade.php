@extends('layouts.app')

@section('title', 'Class Schedules')
@section('page-title', 'Class Schedules')

@section('content')
    @php
        $search = request('search', '');
    @endphp

    <div class="schedule-dashboard">

        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <h3 class="fw-bold mb-1">Class Schedules</h3>
                    <p class="text-muted mb-0">
                        Manage ongoing classes and category-wise scheduling
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.class-category-fees.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-cash-stack me-1"></i>
                        Class Fees
                    </a>

                    <button type="button" class="btn btn-outline-dark custom-btn" disabled>
                        <i class="bi bi-download me-1"></i>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <div class="filter-card mb-4">
            <form method="GET" action="{{ route('admin.class-schedules.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label small fw-semibold">Search</label>
                        <input type="text" name="search" class="form-control custom-input" value="{{ $search }}"
                            placeholder="Search class, grade, subject, teacher or category">
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary custom-btn w-100">
                            <i class="bi bi-search me-1"></i>
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row g-4">
            @forelse($classes as $class)
                @php
                    $gradeName = optional($class->grade)->grade_name ?? 'N/A';
                    $subjectName = optional($class->subject)->subject_name ?? 'N/A';
                    $teacherName = optional($class->teacher)->full_name ?? 'N/A';
                    $feeCount = $class->categoryFees->count();
                @endphp

                <div class="col-12">
                    <div class="class-card">
                        <div class="class-card-header">
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                    <h5 class="mb-0 fw-bold">{{ $class->class_name }}</h5>
                                    <span class="badge bg-success-soft text-success rounded-pill px-3 py-2">
                                        Ongoing
                                    </span>
                                </div>

                                <div class="text-muted small">
                                    Grade: {{ $gradeName }} • Subject: {{ $subjectName }} • Medium:
                                    {{ $class->medium ?? 'N/A' }}
                                </div>
                                <div class="text-muted small">
                                    Teacher: {{ $teacherName }}
                                </div>
                            </div>

                            <div class="class-stats">
                                <div class="stat-box">
                                    <div class="stat-value">{{ $feeCount }}</div>
                                    <div class="stat-label">Categories</div>
                                </div>
                            </div>
                        </div>

                        <div class="class-card-body">
                            @if($class->categoryFees->count())
                                <div class="row g-3">
                                    @foreach($class->categoryFees as $fee)
                                                    @php
                                                        $category = $fee->category;
                                                    @endphp

                                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                                        <div class="category-card">
                                                            <div class="category-card-top">
                                                                <div>
                                                                    <div class="category-name">
                                                                        {{ optional($category)->category_name ?? 'N/A' }}
                                                                    </div>

                                                                    <div class="category-code">
                                                                        @if(optional($category)->code)
                                                                            {{ $category->code }}
                                                                        @else
                                                                            No code
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class="category-fee">
                                                                    {{ number_format($fee->fee, 2) }}
                                                                </div>
                                                            </div>

                                                            <div class="category-card-bottom">
                                                                <div class="text-muted small">
                                                                    Fee category details
                                                                </div>

                                                                <div class="action-group">

                                                                    {{-- CREATE SCHEDULE --}}
                                                                    <a href="{{ route('admin.class-schedules.create', [
                                            'student_class_id' => $class->id,
                                            'class_category_fee_id' => $fee->id
                                        ]) }}" class="btn btn-sm btn-primary icon-btn" title="Schedule this category">
                                                                        <i class="bi bi-calendar-plus"></i>
                                                                    </a>

                                                                    {{-- VIEW SCHEDULES --}}
                                                                    <a href="{{ route('admin.class-schedules.categorySchedules', [
                                            'student_class_id' => $class->id,
                                            'class_category_fee_id' => $fee->id
                                        ]) }}" class="btn btn-sm btn-outline-success icon-btn" title="View schedules">
                                                                        <i class="bi bi-eye"></i>
                                                                    </a>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-box">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                    <div class="fw-semibold">No category fees found</div>
                                    <div class="text-muted small">This class does not have any assigned category fee yet.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-box py-5">
                        <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                        <div class="fw-semibold">No ongoing classes found</div>
                        <div class="text-muted small">Try a different search term.</div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-end">
            {{ $classes->withQueryString()->links() }}
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .schedule-dashboard {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .filter-card,
        .class-card,
        .empty-box {
            background: #fff;
            border: 1px solid #eef2f7;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        }

        .hero-card,
        .filter-card {
            padding: 1.5rem;
        }

        .hero-content {
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

        .custom-btn {
            border-radius: 14px;
            padding: .75rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .custom-input {
            border-radius: 14px !important;
            min-height: 48px;
            border: 1px solid #e2e8f0;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .class-card {
            overflow: hidden;
        }

        .class-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .class-card-body {
            padding: 1.5rem;
        }

        .class-stats {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .stat-box {
            min-width: 110px;
            padding: .9rem 1rem;
            border-radius: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .stat-value {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .stat-label {
            font-size: .78rem;
            color: #64748b;
            margin-top: .25rem;
            font-weight: 600;
        }

        .bg-success-soft {
            background: rgba(34, 197, 94, .12);
        }

        .category-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e8eef6;
            border-radius: 22px;
            padding: 1rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1rem;
            transition: .2s ease;
        }

        .category-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
        }

        .category-card-top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .category-name {
            font-weight: 800;
            font-size: 1rem;
            color: #0f172a;
        }

        .category-code {
            font-size: .82rem;
            color: #64748b;
            margin-top: .2rem;
        }

        .category-fee {
            font-weight: 800;
            font-size: 1.1rem;
            color: #2563eb;
            white-space: nowrap;
        }

        .category-card-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .action-group {
            display: flex;
            gap: .5rem;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .empty-box {
            padding: 2rem;
            text-align: center;
            color: #64748b;
        }

        @media (max-width: 768px) {

            .hero-content,
            .class-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions {
                width: 100%;
            }

            .hero-actions .btn {
                flex: 1;
            }

            .category-card-top,
            .category-card-bottom {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush