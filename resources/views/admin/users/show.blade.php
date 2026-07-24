@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Profile')

@section('content')

<div class="user-profile-page">

    <div class="main-card">

        <!-- HEADER -->
        <div class="main-card-header">

            <div>
                <h4>
                    <i class="bi bi-person-fill text-primary me-2"></i>
                    User Profile
                </h4>
                <p class="text-muted mb-0">
                    View user details and activity
                </p>
            </div>

            <div class="header-buttons">
                @if(!$user->trashed())
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning custom-btn">
                        <i class="bi bi-pencil-fill"></i>
                        Edit User
                    </a>
                @endif
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back to List
                </a>
            </div>

        </div>

        <!-- ALERTS -->
        @if($user->trashed())
            <div class="alert alert-warning custom-alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                This user is currently <strong>deleted</strong> and cannot access the system.
                <div class="mt-2">
                    <form method="POST" action="{{ route('admin.users.restore', $user->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm custom-btn">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Restore User
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.forceDelete', $user->id) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm custom-btn" onclick="return confirm('Permanently delete this user?')">
                            <i class="bi bi-trash3-fill"></i>
                            Permanently Delete
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- USER PROFILE -->
        <div class="profile-header">

            <div class="row align-items-center">

                <div class="col-md-3 text-center text-md-start">

                    <div class="profile-avatar-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>

                </div>

                <div class="col-md-6">

                    <h2 class="mb-1">{{ $user->name }}</h2>

                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge custom-badge bg-light text-dark border">
                            ID: #{{ $user->id }}
                        </span>
                        @if($user->trashed())
                            <span class="badge custom-badge bg-danger">Deleted</span>
                        @elseif($user->is_active)
                            <span class="badge custom-badge bg-success">Active</span>
                        @else
                            <span class="badge custom-badge bg-secondary">Inactive</span>
                        @endif
                        <span class="badge custom-badge bg-primary">
                            {{ $user->userType->name ?? 'N/A' }}
                        </span>
                    </div>

                    <p class="text-muted mb-1">
                        <i class="bi bi-envelope-fill me-2"></i>
                        {{ $user->email }}
                    </p>

                    <p class="text-muted mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        Joined: {{ $user->created_at->format('F d, Y H:i:s') }}
                    </p>

                </div>

                <div class="col-md-3">

                    <div class="quick-stats">
                        <div class="quick-stat-item">
                            <span class="quick-stat-number">{{ $stats['total_payments'] ?? 0 }}</span>
                            <span class="quick-stat-label">Payments</span>
                        </div>
                        <div class="quick-stat-item">
                            <span class="quick-stat-number">LKR {{ number_format($stats['total_amount'] ?? 0, 2) }}</span>
                            <span class="quick-stat-label">Total Amount</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- TABS -->
        <ul class="nav nav-tabs custom-tabs mt-4" id="userTabs" role="tablist">

            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button">
                    <i class="bi bi-info-circle-fill me-1"></i> Details
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button">
                    <i class="bi bi-credit-card-fill me-1"></i> Payments
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                    <i class="bi bi-clock-history me-1"></i> Activity
                </button>
            </li>

        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content mt-4">

            <!-- DETAILS TAB -->
            <div class="tab-pane fade show active" id="details">

                <div class="row g-4">

                    <div class="col-md-6">

                        <div class="info-card">
                            <h6 class="info-card-title">
                                <i class="bi bi-person-fill me-2"></i>
                                Personal Information
                            </h6>
                            <div class="info-row">
                                <span class="info-label">Full Name</span>
                                <span class="info-value">{{ $user->name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $user->email }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">User Type</span>
                                <span class="info-value">{{ $user->userType->name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Code</span>
                                <span class="info-value">{{ $user->userType->code ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status</span>
                                <span class="info-value">
                                    @if($user->trashed())
                                        <span class="badge bg-danger">Deleted</span>
                                    @elseif($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="info-card">
                            <h6 class="info-card-title">
                                <i class="bi bi-link-45deg me-2"></i>
                                Linked Information
                            </h6>
                            <div class="info-row">
                                <span class="info-label">Linked Teacher</span>
                                <span class="info-value">
                                    @if($user->teacher)
                                        <a href="{{ route('admin.teachers.show', $user->teacher) }}" class="text-decoration-none">
                                            {{ $user->teacher->full_name }}
                                            <span class="text-muted">({{ $user->teacher->custom_id ?? 'No ID' }})</span>
                                        </a>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Created</span>
                                <span class="info-value">{{ $user->created_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Last Updated</span>
                                <span class="info-value">{{ $user->updated_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">User ID</span>
                                <span class="info-value">#{{ $user->id }}</span>
                            </div>
                            @if($user->deleted_at)
                                <div class="info-row">
                                    <span class="info-label">Deleted At</span>
                                    <span class="info-value text-danger">{{ $user->deleted_at->format('Y-m-d H:i:s') }}</span>
                                </div>
                            @endif
                        </div>

                    </div>

                </div>

            </div>

            <!-- PAYMENTS TAB -->
            <div class="tab-pane fade" id="payments">

                @if($user->payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->payments as $payment)
                                    <tr>
                                        <td>#{{ $payment->id }}</td>
                                        <td>LKR {{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->payment_type ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">Completed</span>
                                        </td>
                                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <p class="text-muted">Showing last 10 payments</p>
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-credit-card" style="font-size: 3rem;"></i>
                        <h5>No Payments Found</h5>
                        <p>This user has not made any payments yet.</p>
                    </div>
                @endif

            </div>

            <!-- ACTIVITY TAB -->
            <div class="tab-pane fade" id="activity">

                <div class="text-center py-5 text-muted">
                    <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                    <h5>Activity Log</h5>
                    <p>User activity tracking coming soon.</p>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    .user-profile-page {
        animation: fadeIn 0.4s ease;
    }

    .main-card {
        background: white;
        border-radius: 28px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
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

    .custom-btn {
        border-radius: 14px;
        padding: 0.7rem 1.2rem;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .custom-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .custom-alert {
        border-radius: 16px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    /* PROFILE HEADER */
    .profile-header {
        background: #f8fafc;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 1rem;
        border: 1px solid #eef2f7;
    }

    .profile-avatar-lg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 3rem;
        margin: 0 auto;
    }

    .quick-stats {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .quick-stat-item {
        background: white;
        padding: 0.8rem 1.2rem;
        border-radius: 14px;
        border: 1px solid #eef2f7;
        text-align: center;
        min-width: 120px;
    }

    .quick-stat-number {
        display: block;
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e293b;
    }

    .quick-stat-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .custom-badge {
        border-radius: 10px;
        padding: 0.5rem 0.7rem;
        font-size: 0.75rem;
    }

    /* TABS */
    .custom-tabs {
        border-bottom: 2px solid #eef2f7;
    }

    .custom-tabs .nav-link {
        border: none;
        color: #64748b;
        font-weight: 600;
        padding: 0.8rem 1.5rem;
        border-radius: 0;
        transition: all 0.2s ease;
    }

    .custom-tabs .nav-link:hover {
        color: #1e293b;
    }

    .custom-tabs .nav-link.active {
        color: #4f46e5;
        border-bottom: 3px solid #4f46e5;
        background: transparent;
    }

    .custom-tabs .nav-link i {
        font-size: 1.1rem;
    }

    /* INFO CARDS */
    .info-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid #eef2f7;
    }

    .info-card-title {
        font-weight: 700;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 2px solid #eef2f7;
        color: #1e293b;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #64748b;
        font-weight: 500;
    }

    .info-value {
        font-weight: 600;
        color: #1e293b;
        text-align: right;
    }

    /* TABLE */
    .custom-table thead th {
        border: none;
        background: #f8fafc;
        color: #475569;
        font-size: 0.82rem;
        text-transform: uppercase;
        padding: 0.8rem 1rem;
    }

    .custom-table tbody td {
        padding: 0.8rem 1rem;
        border-color: #f1f5f9;
    }

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
            text-align: center;
            justify-content: center;
        }

        .profile-header .row {
            gap: 1.5rem;
        }

        .profile-avatar-lg {
            width: 80px;
            height: 80px;
            font-size: 2rem;
        }

        .quick-stats {
            flex-direction: row;
            justify-content: center;
        }

        .quick-stat-item {
            min-width: 100px;
            padding: 0.6rem 1rem;
        }

        .custom-tabs .nav-link {
            padding: 0.6rem 0.8rem;
            font-size: 0.85rem;
        }

        .info-row {
            flex-direction: column;
            gap: 0.2rem;
        }

        .info-value {
            text-align: left;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush