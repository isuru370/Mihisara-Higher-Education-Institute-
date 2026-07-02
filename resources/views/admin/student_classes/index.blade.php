@extends('layouts.app')

@section('title', 'Classes')
@section('page-title', 'Classes')

@section('content')

    <div class="classes-page">

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
                        <i class="bi bi-play-circle-fill"></i>
                    </div>

                    <div>

                        <h3>
                            {{ \App\Models\StudentClass::where('is_active', 1)->count() }}
                        </h3>

                        <p>Active Classes</p>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-md-6">

                <div class="stats-card">

                    <div class="stats-icon orange">
                        <i class="bi bi-collection-play-fill"></i>
                    </div>

                    <div>

                        <h3>
                            {{ \App\Models\StudentClass::where('is_ongoing', 1)->count() }}
                        </h3>

                        <p>Ongoing Classes</p>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-md-6">

                <div class="stats-card">

                    <div class="stats-icon red">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>

                    <div>

                        <h3>
                            {{ \App\Models\StudentClass::where('is_active', 0)->count() }}
                        </h3>

                        <p>Inactive Classes</p>

                    </div>

                </div>

            </div>

        </div>

        <!-- MAIN CARD -->

        <div class="main-card">

            <!-- HEADER -->

            <div class="main-card-header">

                <div>

                    <h4>Classes Management</h4>

                    <p>Manage student classes, teachers & payment configs</p>

                </div>

                <div class="header-buttons">

                    <a href="{{ route('admin.student-classes.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg"></i> Add Class
                    </a>

                    <a href="{{ route('admin.grades.index') }}" class="btn btn-outline-primary custom-btn">
                        <i class="fas fa-graduation-cap me-1"></i>
                        Grade
                    </a>

                    <a href="{{ route('admin.class-categories.index') }}" class="btn btn-outline-primary custom-btn">
                        Categories
                    </a>

                    <a href="{{ route('admin.class-halls.index') }}" class="btn btn-outline-warning custom-btn">
                        Halls
                    </a>

                    <a href="{{ route('admin.student-classes.exportExcel') }}" class="btn btn-success custom-btn">
                        Excel
                    </a>

                    <a href="{{ route('admin.student-classes.exportPdf') }}" class="btn btn-danger custom-btn">
                        PDF
                    </a>

                </div>

            </div>

            <!-- SEARCH -->

            <div class="search-card">

                <form method="GET" action="{{ route('admin.student-classes.index') }}">

                    <div class="row g-3">

                        <div class="col-lg-5">

                            <div class="search-input-wrapper">

                                <i class="bi bi-search"></i>

                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search class / teacher / subject / grade" value="{{ request('search') }}">

                            </div>

                        </div>

                        <div class="col-lg-3">

                            <select name="class_type" class="form-select custom-input">

                                <option value="">All Types</option>
                                <option value="online">Online</option>
                                <option value="offline">Offline</option>
                                <option value="hybrid">Hybrid</option>

                            </select>

                        </div>

                        <div class="col-lg-2">

                            <button class="btn btn-primary w-100 custom-btn">
                                Search
                            </button>

                        </div>

                        <div class="col-lg-2">

                            <a href="{{ route('admin.student-classes.index') }}"
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
                            <th>Class</th>
                            <th>Teacher</th>
                            <th>Grade / Subject</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($classes as $class)
                            <tr>

                                <td>
                                    {{ $loop->iteration + ($classes->currentPage() - 1) * $classes->perPage() }}
                                </td>

                                <!-- CLASS -->

                                <td>

                                    <div class="fw-bold">
                                        {{ $class->class_name }}
                                    </div>

                                    <small class="text-muted">
                                        {{ ucfirst($class->class_type) }} / {{ $class->medium }}
                                    </small>

                                </td>

                                <!-- TEACHER -->

                                <td>

                                    <div class="fw-semibold">
                                        {{ $class->teacher->full_name ?? 'N/A' }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $class->teacher->custom_id ?? '' }}
                                    </small>

                                </td>

                                <!-- GRADE -->

                                <td>

                                    <div class="fw-semibold">
                                        {{ $class->grade->grade_name ?? 'N/A' }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $class->subject->subject_name ?? 'N/A' }}
                                    </small>

                                </td>

                                <!-- PAYMENT -->

                                <td>

                                    @if ($class->paymentConfig)
                                        <div class="small">T: {{ $class->paymentConfig->teacher_percentage }}%</div>
                                        <div class="small">O: {{ $class->paymentConfig->organizer_percentage }}%</div>
                                        <div class="small">I: {{ $class->paymentConfig->institution_percentage }}%</div>
                                    @else
                                        <span class="badge bg-light text-dark border">Not Set</span>
                                    @endif

                                </td>

                                <!-- STATUS -->

                                <td>

                                    @if ($class->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif

                                    <br>

                                    @if ($class->is_ongoing)
                                        <span class="badge bg-primary mt-1">Ongoing</span>
                                    @else
                                        <span class="badge bg-light text-dark border mt-1">Stopped</span>
                                    @endif

                                </td>

                                <!-- ACTIONS -->

                                <td class="text-end">

                                    <div class="action-buttons">

                                        <a href="{{ route('admin.student-classes.show', $class) }}"
                                            class="action-btn view-btn">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        <a href="{{ route('admin.student-classes.edit', $class) }}"
                                            class="action-btn edit-btn">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        @if ($class->is_active)
                                            <a href="{{ route('admin.class-category-fees.create', ['student_class_id' => $class->id]) }}"
                                                class="action-btn toggle-btn">
                                                <i class="bi bi-cash-stack"></i>
                                            </a>
                                        @else
                                            <button class="action-btn toggle-btn" disabled>
                                                <i class="bi bi-cash-stack"></i>
                                            </button>
                                        @endif

                                        <form method="POST"
                                            action="{{ route('admin.student-classes.toggleActive', $class) }}">

                                            @csrf
                                            @method('PATCH')

                                            <button class="action-btn toggle-btn">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>

                                        </form>

                                        <form method="POST" action="{{ route('admin.student-classes.destroy', $class) }}">

                                            @csrf
                                            @method('DELETE')

                                            <button class="action-btn delete-btn">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    No classes found
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-4">
                {{ $classes->links() }}
            </div>

        </div>

    </div>

@endsection

@push('styles')
    <style>
        .classes-page {
            animation: fadeIn .4s ease;
        }

        /* STATS */

        .stats-card {

            background: #fff;

            border-radius: 24px;

            padding: 1.5rem;

            display: flex;
            gap: 1rem;
            align-items: center;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, .04);

            border:
                1px solid #eef2f7;
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

        /* MAIN CARD */

        .main-card {

            background: #fff;

            border-radius: 28px;

            padding: 1.5rem;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, .05);
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

        /* BUTTONS */

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

        /* SEARCH */

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

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, .10);
        }

        /* TABLE */

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
        }

        /* BADGES */

        .badge {

            border-radius: 10px;

            padding: .5rem .7rem;

            font-size: .75rem;
        }

        /* ACTIONS */

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

            display: flex;
            align-items: center;
            justify-content: center;

            text-decoration: none;

            transition: .2s ease;
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

        .toggle-btn {

            background: #ecfdf5;
            color: #10b981;
        }

        .delete-btn {

            background: #fef2f2;
            color: #ef4444;
        }

        /* EMPTY STATE */

        .empty-state i {

            font-size: 3rem;

            color: #cbd5e1;

            margin-bottom: 1rem;
        }

        .empty-state h5 {

            font-weight: 700;
        }

        /* MOBILE */

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
