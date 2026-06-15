@extends('layouts.app')

@section('title', 'System Users')
@section('page-title', 'System Users')

@section('content')

    @php
        $totalUsers = $systemUsers
            ->where('email', '!=', 'admin@nexorait.lk')
            ->count();

        $activeUsers = $systemUsers
            ->where('is_active', 1)
            ->where('email', '!=', 'admin@nexorait.lk')
            ->count();

        $inactiveUsers = $systemUsers
            ->where('is_active', 0)
            ->where('email', '!=', 'admin@nexorait.lk')
            ->count();

        $linkedUsers = $systemUsers
            ->where('email', '!=', 'admin@nexorait.lk')
            ->filter(fn($u) => !empty($u->user?->name))
            ->count();
    @endphp

    <div class="system-users-page">

        {{-- STATS --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $totalUsers }}</h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $activeUsers }}</h3>
                        <p>Active Users</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <div>
                        <h3>{{ $linkedUsers }}</h3>
                        <p>Linked Accounts</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon red">
                        <i class="bi bi-pause-circle-fill"></i>
                    </div>
                    <div>
                        <h3>{{ $inactiveUsers }}</h3>
                        <p>Inactive Users</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN CARD --}}
        <div class="main-card">

            {{-- HEADER --}}
            <div class="main-card-header">
                <div>
                    <h4>System Users Management</h4>
                    <p>Manage user accounts, status, and linked profile details</p>
                </div>

                <div class="header-buttons">
                    <a href="{{ route('admin.system-users.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg"></i>
                        Add User
                    </a>

                    <a href="{{ route('admin.system-users.export.excel') }}" class="btn btn-success custom-btn">
                        <i class="bi bi-file-earmark-excel-fill"></i>
                        Excel
                    </a>

                    <a href="{{ route('admin.system-users.export.pdf') }}" class="btn btn-danger custom-btn">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                        PDF
                    </a>


                </div>
            </div>

            {{-- ALERT --}}
            @if(session('success'))
                <div class="alert alert-success custom-alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- SEARCH / FILTER --}}
            <div class="search-card">
                <form method="GET" action="{{ route('admin.system-users.index') }}">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-5">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search name / mobile / custom ID / user..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <select name="is_active" class="form-select custom-input">
                                <option value="">All Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <button class="btn btn-primary w-100 custom-btn" type="submit">
                                <i class="bi bi-funnel-fill"></i>
                                Filter
                            </button>
                        </div>

                        <div class="col-lg-2">
                            <a href="{{ route('admin.system-users.index') }}" class="btn btn-light border w-100 custom-btn">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Mobile</th>
                            <th>Custom ID</th>
                            <th>Linked Account</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($systemUsers as $systemUser)
                            @continue(strtolower($systemUser->user?->email ?? '') === 'admin@nexorait.lk')

                            <tr>
                                <td>
                                    {{ $loop->iteration + ($systemUsers->currentPage() - 1) * $systemUsers->perPage() }}
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="user-avatar">
                                            {{ strtoupper(substr($systemUser->full_name ?? 'U', 0, 1)) }}
                                        </div>

                                        <div>
                                            <div class="user-name">
                                                {{ $systemUser->full_name }}
                                            </div>
                                            <small class="text-muted">
                                                ID: {{ $systemUser->id }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $systemUser->mobile }}</div>
                                </td>

                                <td>
                                    <span class="badge custom-badge bg-light text-dark border">
                                        {{ $systemUser->custom_id }}
                                    </span>
                                </td>

                                <td>
                                    @if($systemUser->user)
                                        <div class="fw-semibold">{{ $systemUser->user?->name }}</div>
                                        <small class="text-muted">{{ $systemUser->user?->email }}</small>
                                    @else
                                        <span class="text-muted">No linked account</span>
                                    @endif
                                </td>

                                <td>
                                    @if($systemUser->is_active)
                                        <span class="badge bg-success custom-badge">Active</span>
                                    @else
                                        <span class="badge bg-secondary custom-badge">Inactive</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.system-users.show', $systemUser) }}"
                                            class="action-btn view-btn" title="View">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        <a href="{{ route('admin.system-users.edit', $systemUser) }}"
                                            class="action-btn edit-btn" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <a href="{{ route('admin.user-permissions.index', $systemUser->id) }}"
                                            class="action-btn permissions-btn" title="permissions">
                                            <i class="bi bi-key-fill"></i>
                                        </a>

                                        <form action="{{ route('admin.system-users.destroy', $systemUser) }}" method="POST"
                                            onsubmit="return confirm('Delete this user?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="action-btn delete-btn" title="Delete">
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
                                        <i class="bi bi-people"></i>
                                        <h5>No Users Found</h5>
                                        <p>Try adjusting search filters or add a new user</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4">
                {{ $systemUsers->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .system-users-page {
            animation: fadeIn 0.4s ease;
        }

        .stats-card {
            background: #fff;
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f7;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            flex-shrink: 0;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            gap: 1rem;
            flex-wrap: wrap;
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
            padding: .72rem 1.2rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
        }

        .custom-alert {
            border-radius: 16px;
            border: 1px solid #bbf7d0;
        }

        .search-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.25rem;
            border: 1px solid #eef2f7;
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
            pointer-events: none;
        }

        .custom-input {
            min-height: 48px;
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            padding-left: 42px;
            box-shadow: none !important;
        }

        select.custom-input {
            padding-left: 16px;
        }

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            padding: 1rem;
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

        .user-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .user-name {
            font-weight: 700;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
            font-weight: 600;
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

        .delete-btn {
            background: #fef2f2;
            color: #ef4444;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .empty-state h5 {
            font-weight: 700;
            margin-bottom: .35rem;
        }

        .empty-state p {
            margin: 0;
            color: #64748b;
        }

        @media (max-width: 768px) {
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
                justify-content: center;
            }

            .action-buttons {
                justify-content: flex-start;
            }
        }
    </style>
@endpush