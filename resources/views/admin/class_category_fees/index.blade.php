@extends('layouts.app')

@section('title', 'Class Category Fees')
@section('page-title', 'Class Category Fees')

@section('content')

    @php
        $currentStudentClassId = request('student_class_id');
        $currentCategoryId = request('class_category_id');
        $currentStatus = request('is_active');
        $currentPerPage = request('per_page', 10);

        $totalFees = $fees->total();
        $activeFees = \App\Models\ClassCategoryFee::where('is_active', 1)->count();
        $inactiveFees = \App\Models\ClassCategoryFee::where('is_active', 0)->count();
        $scheduleReadyFees = \App\Models\ClassCategoryFee::where('is_active', 1)->count();
    @endphp

    <div class="fees-page">

        <!-- STATS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <div>
                        <h3>{{ $totalFees }}</h3>
                        <p>Total Fees</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $activeFees }}</h3>
                        <p>Active Fees</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $scheduleReadyFees }}</h3>
                        <p>Schedule Ready</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon red">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $inactiveFees }}</h3>
                        <p>Inactive Fees</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <!-- HEADER -->
            <div class="main-card-header">

                <div>
                    <h4>Class Category Fees</h4>
                    <p>Manage class-wise theory, revision and paper fees</p>
                </div>

                <div class="header-buttons">

                    <a href="{{ route('admin.class-category-fees.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg"></i>
                        Add Fee
                    </a>

                    <a href="{{ route('admin.student-classes.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        View Classes
                    </a>

                    <button class="btn btn-light border custom-btn" disabled>
                        <i class="bi bi-graph-up-arrow"></i>
                        Analytics
                    </button>

                </div>

            </div>

            <!-- SEARCH -->
            <div class="search-card">

                <form method="GET" action="{{ route('admin.class-category-fees.index') }}">
                    <div class="row g-3">

                        <div class="col-lg-3">
                            <select name="student_class_id" class="form-select custom-input">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" <?php    echo $currentStudentClassId == $class->id ? 'selected="selected"' : ''; ?>>
                                        {{ $class->class_name }}
                                        | Grade: {{ optional($class->grade)->grade_name }}
                                        | Subject: {{ optional($class->subject)->subject_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <select name="class_category_id" class="form-select custom-input">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" <?php    echo $currentCategoryId == $category->id ? 'selected="selected"' : ''; ?>>
                                        {{ $category->category_name }}
                                        @if($category->code)
                                            - {{ $category->code }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <select name="is_active" class="form-select custom-input">
                                <option value="">All Status</option>
                                <option value="true" <?php echo $currentStatus === 'true' ? 'selected="selected"' : ''; ?>>
                                    Active</option>
                                <option value="false" <?php echo $currentStatus === 'false' ? 'selected="selected"' : ''; ?>>
                                    Inactive</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <select name="per_page" class="form-select custom-input">
                                <option value="10" <?php echo (string) $currentPerPage === '10' ? 'selected="selected"' : ''; ?>>10 Rows</option>
                                <option value="25" <?php echo (string) $currentPerPage === '25' ? 'selected="selected"' : ''; ?>>25 Rows</option>
                                <option value="50" <?php echo (string) $currentPerPage === '50' ? 'selected="selected"' : ''; ?>>50 Rows</option>
                                <option value="100" <?php echo (string) $currentPerPage === '100' ? 'selected="selected"' : ''; ?>>100 Rows</option>
                            </select>
                        </div>

                        <div class="col-lg-1">
                            <button type="submit" class="btn btn-primary w-100 custom-btn">
                                Search
                            </button>
                        </div>

                        <div class="col-lg-1">
                            <a href="{{ route('admin.class-category-fees.index') }}"
                                class="btn btn-light border w-100 custom-btn">
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
                            <th>Class Details</th>
                            <th>Category</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th class="text-center">Schedule</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($fees as $fee)
                                            @php
                                                $studentClass = $fee->studentClass;
                                                $category = $fee->category;

                                                $classIsActive = false;
                                                $categoryIsActive = false;
                                                $canSchedule = false;

                                                if ($studentClass && $studentClass->is_active) {
                                                    $classIsActive = true;
                                                }

                                                if ($category && $category->is_active) {
                                                    $categoryIsActive = true;
                                                }

                                                if ($classIsActive && $categoryIsActive && $fee->is_active) {
                                                    $canSchedule = true;
                                                }
                                            @endphp

                                            <tr>
                                                <td>
                                                    {{ $loop->iteration + (($fees->currentPage() - 1) * $fees->perPage()) }}
                                                </td>

                                                <td>
                                                    <div class="fw-bold">
                                                        {{ $studentClass ? $studentClass->class_name : '-' }}
                                                    </div>

                                                    <div class="small text-muted">
                                                        Grade:
                                                        {{ $studentClass && $studentClass->grade ? $studentClass->grade->grade_name : 'N/A' }}
                                                    </div>

                                                    <div class="small text-muted">
                                                        Subject:
                                                        {{ $studentClass && $studentClass->subject ? $studentClass->subject->subject_name : 'N/A' }}
                                                    </div>

                                                    <div class="small text-muted">
                                                        Teacher:
                                                        {{ $studentClass && $studentClass->teacher ? $studentClass->teacher->full_name : 'N/A' }}
                                                    </div>

                                                    <span class="badge {{ $classIsActive ? 'bg-success' : 'bg-secondary' }} custom-badge mt-2">
                                                        {{ $classIsActive ? 'Class Active' : 'Class Inactive' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <div class="fw-semibold">
                                                        {{ $category ? $category->category_name : '-' }}
                                                    </div>

                                                    <small class="text-muted">
                                                        {{ $category ? $category->code : '' }}
                                                    </small>

                                                    @if(!$categoryIsActive)
                                                        <br>
                                                        <span class="badge bg-secondary custom-badge mt-2">
                                                            Category Inactive
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="fw-bold">
                                                        {{ number_format($fee->fee, 2) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="badge {{ $fee->is_active ? 'bg-success' : 'bg-secondary' }} custom-badge">
                                                        {{ $fee->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1 flex-wrap">

                                                        @if($canSchedule)
                                                                                    <a href="{{ route('admin.class-schedules.create', [
                                                                'student_class_id' => $fee->student_class_id,
                                                                'class_category_id' => $fee->class_category_id
                                                            ]) }}" class="action-btn schedule-btn" title="Create Schedule">
                                                                                        <i class="bi bi-plus-circle-fill"></i>
                                                                                    </a>
                                                        @else
                                                            <button type="button" class="action-btn disabled-btn" disabled
                                                                title="Schedule not allowed">
                                                                <i class="bi bi-plus-circle-fill"></i>
                                                            </button>
                                                        @endif

                                                        <a href="{{ route('admin.class-schedules.index', [
                                'student_class_id' => $fee->student_class_id,
                                'class_category_id' => $fee->class_category_id
                            ]) }}" class="action-btn view-btn" title="View Schedules">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </a>
                                                    </div>
                                                </td>

                                                <td class="text-end">
                                                    <div class="action-buttons">

                                                        <a href="{{ route('admin.class-category-fees.show', $fee) }}"
                                                            class="action-btn view-btn" title="View">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </a>

                                                        @if($classIsActive)
                                                            <a href="{{ route('admin.class-category-fees.edit', $fee) }}"
                                                                class="action-btn edit-btn" title="Edit">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </a>
                                                        @else
                                                            <button type="button" class="action-btn disabled-btn" disabled title="Class inactive">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </button>
                                                        @endif

                                                        @if($classIsActive)
                                                            <form method="POST" action="{{ route('admin.class-category-fees.toggleActive', $fee) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('PATCH')

                                                                <button type="submit"
                                                                    class="action-btn {{ $fee->is_active ? 'inactive-btn' : 'active-btn' }}"
                                                                    title="{{ $fee->is_active ? 'Deactivate' : 'Activate' }}">
                                                                    <i class="bi {{ $fee->is_active ? 'bi-pause-fill' : 'bi-check-lg' }}"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" class="action-btn disabled-btn" disabled title="Class inactive">
                                                                <i class="bi bi-pause-fill"></i>
                                                            </button>
                                                        @endif

                                                        @if($classIsActive)
                                                            <form method="POST" action="{{ route('admin.class-category-fees.destroy', $fee) }}"
                                                                class="d-inline" onsubmit="return confirm('Delete this fee?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit" class="action-btn delete-btn" title="Delete">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button type="button" class="action-btn disabled-btn" disabled title="Class inactive">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </button>
                                                        @endif

                                                    </div>
                                                </td>
                                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="empty-state">
                                        <i class="bi bi-cash-coin"></i>
                                        <h5>No Class Category Fees Found</h5>
                                        <p>Try adjusting the search filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            <div class="mt-4">
                {{ $fees->withQueryString()->links() }}
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <style>
        .fees-page {
            animation: fadeIn 0.4s ease;
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
            word-break: break-word;
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

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
            vertical-align: middle;
            white-space: nowrap;
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

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
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
            transition: .2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .05);
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .view-btn {
            background: #eff6ff;
            color: #2563eb;
        }

        .edit-btn {
            background: #fef3c7;
            color: #d97706;
        }

        .schedule-btn {
            background: #ecfdf5;
            color: #10b981;
        }

        .active-btn {
            background: #dcfce7;
            color: #16a34a;
        }

        .inactive-btn {
            background: #fef3c7;
            color: #d97706;
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        .disabled-btn {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
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

        .table-responsive {
            border-radius: 20px;
            overflow: auto;
        }

        .pagination {
            gap: .35rem;
        }

        .page-link {
            border: none;
            min-width: 42px;
            height: 42px;
            border-radius: 12px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .page-link:hover {
            background: #eff6ff;
            color: #2563eb;
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