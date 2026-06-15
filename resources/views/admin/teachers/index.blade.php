@extends('layouts.app')

@section('title', 'Teachers')
@section('page-title', 'Teachers')

@section('content')

    <div class="teachers-page">

        <!-- STATS -->

        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">

                <div class="stats-card">

                    <div class="stats-icon blue">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>

                    <div>

                        <h3>{{ $teachers->total() }}</h3>

                        <p>Total Teachers</p>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-md-6">

                <div class="stats-card">

                    <div class="stats-icon green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div>

                        <h3>
                            {{ \App\Models\Teacher::where('is_active', 1)->count() }}
                        </h3>

                        <p>Active Teachers</p>

                    </div>

                </div>

            </div>

            <div class="col-xl-3 col-md-6">

                <div class="stats-card">

                    <div class="stats-icon orange">
                        <i class="bi bi-bank2"></i>
                    </div>

                    <div>

                        <h3>
                            {{ \App\Models\Teacher::whereNotNull('bank_branch_id')->count() }}
                        </h3>

                        <p>Bank Linked</p>

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
                            {{ \App\Models\Teacher::where('is_active', 0)->count() }}
                        </h3>

                        <p>Inactive Teachers</p>

                    </div>

                </div>

            </div>

        </div>

        <!-- MAIN CARD -->

        <div class="main-card">

            <!-- HEADER -->

            <div class="main-card-header">

                <div>

                    <h4>
                        Teachers Management
                    </h4>

                    <p>
                        Manage teacher records, status and banking details
                    </p>

                </div>

                <!-- BUTTONS -->

                <div class="header-buttons">

                    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary custom-btn">

                        <i class="bi bi-plus-lg"></i>

                        Add Teacher

                    </a>

                    <a href="{{ route('admin.teachers.exportExcel') }}" class="btn btn-success custom-btn">

                        <i class="bi bi-file-earmark-excel-fill"></i>

                        Excel

                    </a>

                    <a href="{{ route('admin.teachers.exportPdf') }}" class="btn btn-danger custom-btn">

                        <i class="bi bi-file-earmark-pdf-fill"></i>

                        PDF

                    </a>

                    <!-- FUTURE FEATURE -->

                    <button class="btn btn-light border custom-btn" disabled>

                        <i class="bi bi-graph-up-arrow"></i>

                        Analytics

                    </button>

                </div>

            </div>

            <!-- SEARCH -->

            <div class="search-card">

                <form method="GET" action="{{ route('admin.teachers.index') }}">

                    <div class="row g-3">

                        <div class="col-lg-5">

                            <div class="search-input-wrapper">

                                <i class="bi bi-search"></i>

                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search name / mobile / NIC / email / custom ID..."
                                    value="{{ request('search') }}">

                            </div>

                        </div>

                        <div class="col-lg-3">

                            <select name="is_active" class="form-select custom-input">

                                <option value="">
                                    All Status
                                </option>

                                <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>

                                    Active

                                </option>

                                <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>

                                    Inactive

                                </option>

                            </select>

                        </div>

                        <div class="col-lg-2">

                            <button class="btn btn-primary w-100 custom-btn" type="submit">

                                <i class="bi bi-search"></i>

                                Search

                            </button>

                        </div>

                        <div class="col-lg-2">

                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-light border w-100 custom-btn">

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

                            <th>Teacher</th>

                            <th>Contact</th>

                            <th>NIC</th>

                            <th>Bank Details</th>

                            <th>Status</th>

                            <th class="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($teachers as $teacher)

                            <tr>

                                <td>
                                    {{ $loop->iteration + ($teachers->currentPage() - 1) * $teachers->perPage() }}
                                </td>

                                <!-- TEACHER -->

                                <td>

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="teacher-avatar">

                                            {{ strtoupper(substr($teacher->full_name, 0, 1)) }}

                                        </div>

                                        <div>

                                            <div class="teacher-name">

                                                {{ $teacher->full_name }}

                                            </div>

                                            <small class="text-muted">

                                                {{ $teacher->initials }}

                                            </small>

                                            <br>

                                            <span class="badge custom-badge bg-light text-dark border mt-2">

                                                {{ $teacher->custom_id }}

                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <!-- CONTACT -->

                                <td>

                                    <div class="fw-semibold">

                                        {{ $teacher->mobile }}

                                    </div>

                                    <small class="text-muted">

                                        {{ $teacher->email }}

                                    </small>

                                </td>

                                <!-- NIC -->

                                <td>

                                    <span class="fw-semibold">

                                        {{ $teacher->nic }}

                                    </span>

                                </td>

                                <!-- BANK -->

                                <td>

                                    <div class="fw-semibold">

                                        {{ $teacher->bankBranch->bank->bank_name ?? 'N/A' }}

                                    </div>

                                    <small class="text-muted">

                                        {{ $teacher->bankBranch->branch_name ?? 'No branch selected' }}

                                    </small>

                                </td>

                                <!-- STATUS -->

                                <td>

                                    @if($teacher->is_active)

                                        <span class="badge bg-success custom-badge">

                                            Active

                                        </span>

                                    @else

                                        <span class="badge bg-secondary custom-badge">

                                            Inactive

                                        </span>

                                    @endif

                                </td>

                                <!-- ACTIONS -->

                                <td class="text-end">

                                    <div class="action-buttons">

                                        <!-- VIEW -->

                                        <a href="{{ route('admin.teachers.show', $teacher) }}" class="action-btn view-btn">

                                            <i class="bi bi-eye-fill"></i>

                                        </a>

                                        <!-- EDIT -->

                                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="action-btn edit-btn">

                                            <i class="bi bi-pencil-fill"></i>

                                        </a>

                                        <!-- TOGGLE -->

                                        <form method="POST" action="{{ route('admin.teachers.toggleActive', $teacher) }}"
                                            onsubmit="return confirm('Change status?')">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="action-btn toggle-btn">

                                                <i class="bi bi-arrow-repeat"></i>

                                            </button>

                                        </form>

                                        <!-- FUTURE FEATURE -->

                                        <a href="{{ url('admin/teacher-salaries/' . $teacher->id . '/' . now()->year . '/' . now()->month) }}"
                                            class="action-btn salary-btn" title="Teacher Salary Report">

                                            <i class="bi bi-cash-stack"></i>

                                        </a>

                                        <!-- DELETE -->

                                        <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}"
                                            onsubmit="return confirm('Delete this teacher?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="action-btn delete-btn">

                                                <i class="bi bi-trash-fill"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="7" class="text-center py-5 text-muted">

                                    <div class="empty-state">

                                        <i class="bi bi-person-x"></i>

                                        <h5>
                                            No Teachers Found
                                        </h5>

                                        <p>
                                            Try adjusting search filters
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- PAGINATION -->

            <div class="mt-4">

                {{ $teachers->links() }}

            </div>

        </div>

    </div>

@endsection

@push('styles')

    <style>
        .teachers-page {
            animation: fadeIn 0.4s ease;
        }

        /* STATS */

        .stats-card {

            background: white;

            border-radius: 24px;

            padding: 1.5rem;

            display: flex;
            align-items: center;
            gap: 1rem;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.04);

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

            font-size: 1.5rem;

            color: white;
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

            background: white;

            border-radius: 28px;

            padding: 1.5rem;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.05);
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
            gap: 0.7rem;

            flex-wrap: wrap;
        }

        .custom-btn {

            border-radius: 14px;

            padding: 0.7rem 1.2rem;

            font-weight: 600;

            border: none;
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

        /* TABLE */

        .custom-table thead th {

            border: none;

            background: #f8fafc;

            color: #475569;

            font-size: 0.82rem;

            text-transform: uppercase;

            padding: 1rem;
        }

        .custom-table tbody tr {

            transition: 0.2s ease;
        }

        .custom-table tbody tr:hover {

            background: #f8fafc;
        }

        .custom-table tbody td {

            padding: 1rem;

            border-color: #f1f5f9;
        }

        /* AVATAR */

        .teacher-avatar {

            width: 55px;
            height: 55px;

            border-radius: 50%;

            background: linear-gradient(135deg, #4f46e5, #7c3aed);

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: 700;

            font-size: 1.2rem;
        }

        .teacher-name {

            font-weight: 700;
        }

        /* BADGES */

        .custom-badge {

            border-radius: 10px;

            padding: 0.5rem 0.7rem;

            font-size: 0.75rem;
        }

        /* ACTIONS */

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

            display: flex;
            align-items: center;
            justify-content: center;

            text-decoration: none;

            transition: 0.2s ease;
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

        .salary-btn {

            background: #f3f4f6;
            color: #94a3b8;
        }

        .delete-btn {

            background: #fef2f2;
            color: #ef4444;
        }

        /* EMPTY */

        .empty-state i {

            font-size: 3rem;

            color: #cbd5e1;

            margin-bottom: 1rem;
        }

        .empty-state h5 {

            font-weight: 700;
        }

        /* MOBILE */

        @media(max-width: 768px) {

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