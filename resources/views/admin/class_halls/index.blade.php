@extends('layouts.app')

@section('title', 'Class Halls')
@section('page-title', 'Class Halls')

@section('content')

    <div class="halls-page">

        <!-- STATS -->
        <div class="row g-4 mb-4">

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <h3>{{ $halls->total() }}</h3>
                        <p>Total Halls</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ \App\Models\ClassHall::where('is_active', 1)->count() }}</h3>
                        <p>Active Halls</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <h3>{{ \App\Models\ClassHall::where('hall_price', 0)->count() }}</h3>
                        <p>Free Halls</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon red">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ \App\Models\ClassHall::where('is_active', 0)->count() }}</h3>
                        <p>Inactive Halls</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <!-- HEADER -->
            <div class="main-card-header">

                <div>
                    <h4>Class Halls Management</h4>
                    <p>Manage physical and online halls used for classes</p>
                </div>

                <div class="header-buttons">

                    <a href="{{ route('admin.class-halls.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg"></i>
                        Add Hall
                    </a>

                    <a href="{{ route('admin.student-classes.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        View Classes
                    </a>

                    <!-- Future feature -->
                    <button class="btn btn-light border custom-btn" disabled>
                        <i class="bi bi-graph-up-arrow"></i>
                        Analytics
                    </button>

                </div>

            </div>

            <!-- SEARCH -->
            <div class="search-card">

                <form method="GET" action="{{ route('admin.class-halls.index') }}">
                    <div class="row g-3">

                        <div class="col-lg-6">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search hall name / code" value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <select name="is_active" class="form-select custom-input">
                                <option value="">All Status</option>
                                <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>Active</option>
                                <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-1">
                            <button class="btn btn-primary w-100 custom-btn" type="submit">
                                Search
                            </button>
                        </div>

                        <div class="col-lg-2">
                            <a href="{{ route('admin.class-halls.index') }}" class="btn btn-light border w-100 custom-btn">
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
                            <th>Hall</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($halls as $hall)
                            <tr>
                                <td>
                                    {{ $loop->iteration + ($halls->currentPage() - 1) * $halls->perPage() }}
                                </td>

                                <!-- HALL -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="hall-avatar">
                                            {{ strtoupper(substr($hall->hall_name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <div class="hall-name">
                                                {{ $hall->hall_name }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $hall->code }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- TYPE -->
                                <td>
                                    <span class="fw-semibold">
                                        {{ $hall->hall_type ?? '-' }}
                                    </span>
                                </td>

                                <!-- PRICE -->
                                <td>
                                    @if ((float) $hall->hall_price == 0)
                                        <span class="badge bg-success custom-badge">Free</span>
                                    @else
                                        <span class="fw-bold">
                                            {{ number_format($hall->hall_price, 2) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- STATUS -->
                                <td>
                                    @if($hall->is_active)
                                        <span class="badge bg-success custom-badge">Active</span>
                                    @else
                                        <span class="badge bg-secondary custom-badge">Inactive</span>
                                    @endif
                                </td>

                                <!-- ACTIONS -->
                                <td class="text-end">
                                    <div class="action-buttons">

                                        <a href="{{ route('admin.class-halls.show', $hall) }}" class="action-btn view-btn">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        <a href="{{ route('admin.class-halls.edit', $hall) }}" class="action-btn edit-btn">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <form method="POST" action="{{ route('admin.class-halls.toggleActive', $hall) }}"
                                            onsubmit="return confirm('Change active status?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-btn toggle-btn">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.class-halls.destroy', $hall) }}"
                                            onsubmit="return confirm('Delete this hall?')">
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
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="empty-state">
                                        <i class="bi bi-building-x"></i>
                                        <h5>No Class Halls Found</h5>
                                        <p>Try adjusting the search filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            <div class="mt-4">
                {{ $halls->links() }}
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <style>
        .halls-page {
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

        .hall-avatar {
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
            box-shadow: 0 8px 18px rgba(79, 70, 229, .20);
        }

        .hall-name {
            font-weight: 700;
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

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
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