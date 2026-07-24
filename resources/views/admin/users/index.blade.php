@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')

<div class="users-page">

    <!-- STATS -->
    <div class="row g-4 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
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
                    <h3>{{ $stats['active'] ?? 0 }}</h3>
                    <p>Active Users</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon orange">
                    <i class="bi bi-pause-circle-fill"></i>
                </div>
                <div>
                    <h3>{{ $stats['inactive'] ?? 0 }}</h3>
                    <p>Inactive Users</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon red">
                    <i class="bi bi-trash-fill"></i>
                </div>
                <div>
                    <h3>{{ $stats['deleted'] ?? 0 }}</h3>
                    <p>Deleted Users</p>
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
                    <i class="bi bi-people-fill me-2"></i>
                    User Management
                </h4>
                <p class="text-muted mb-0">
                    Manage system users, roles and permissions
                </p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary custom-btn">
                    <i class="bi bi-plus-lg"></i>
                    Add User
                </a>
                <a href="{{ route('admin.users.exportExcel') }}" class="btn btn-success custom-btn">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    Excel
                </a>
                <a href="{{ route('admin.users.exportPdf') }}" class="btn btn-danger custom-btn">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    PDF
                </a>
            </div>

        </div>

        <!-- SEARCH & FILTER -->
        <div class="search-card">

            <form method="GET" action="{{ route('admin.users.index') }}">

                <div class="row g-3">

                    <div class="col-lg-4">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control custom-input"
                                placeholder="Search name / email / ID..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <select name="user_type" class="form-select custom-input">
                            <option value="">All User Types</option>
                            @foreach($userTypes as $type)
                                <option value="{{ $type->id }}" {{ request('user_type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <select name="status" class="form-select custom-input">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <button class="btn btn-primary w-100 custom-btn" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>

                    <div class="col-lg-1">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light border w-100 custom-btn">
                            Reset
                        </a>
                    </div>

                </div>

            </form>

        </div>

        <!-- BULK ACTIONS -->
        <div class="bulk-actions mb-3">
            <form id="bulkActionForm" action="{{ route('admin.users.bulkAction') }}" method="POST">
                @csrf
                <div class="row align-items-center g-2">
                    <div class="col-auto">
                        <span class="text-muted">Bulk Actions:</span>
                    </div>
                    <div class="col-auto">
                        <select name="action" class="form-select form-select-sm" required>
                            <option value="">Select Action</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="delete">Delete</option>
                            <option value="restore">Restore</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure?')">
                            Apply
                        </button>
                    </div>
                    <div class="col-auto">
                        <span id="selectedCount" class="text-muted">0 selected</span>
                    </div>
                </div>
            </form>
        </div>

        <!-- TABLE -->
        <div class="table-responsive">

            <table class="table custom-table align-middle">

                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>User Type</th>
                        <th>Linked To</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($users as $user)
                        <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">

                            <td>
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                    class="form-check-input user-checkbox">
                            </td>

                            <td>
                                {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                            </td>

                            <!-- USER -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">
                                            {{ $user->name }}
                                        </div>
                                        <small class="text-muted">
                                            ID: #{{ $user->id }}
                                        </small>
                                        @if($user->trashed())
                                            <span class="badge bg-danger ms-2">Deleted</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- EMAIL -->
                            <td>
                                <div class="fw-semibold">
                                    {{ $user->email }}
                                </div>
                                <small class="text-muted">
                                    {{ $user->created_at->format('Y-m-d') }}
                                </small>
                            </td>

                            <!-- USER TYPE -->
                            <td>
                                <span class="badge custom-badge bg-primary">
                                    {{ $user->userType->name ?? 'N/A' }}
                                </span>
                                @if($user->userType)
                                    <br>
                                    <small class="text-muted">
                                        {{ $user->userType->code ?? '' }}
                                    </small>
                                @endif
                            </td>

                            <!-- LINKED TO - Check both Teacher and System User -->
                            <td>
                                @if($user->teacher)
                                    <span class="badge custom-badge bg-success">
                                        <i class="bi bi-person-badge-fill me-1"></i>
                                        Teacher
                                    </span>
                                    <br>
                                    <small>{{ $user->teacher->full_name ?? '' }}</small>
                                    @if($user->teacher->custom_id)
                                        <br>
                                        <small class="text-muted">ID: {{ $user->teacher->custom_id }}</small>
                                    @endif
                                @elseif($user->systemUser)
                                    <span class="badge custom-badge bg-info">
                                        <i class="bi bi-person-badge-fill me-1"></i>
                                        System User
                                    </span>
                                    <br>
                                    <small>{{ $user->systemUser->full_name ?? '' }}</small>
                                    @if($user->systemUser->custom_id)
                                        <br>
                                        <small class="text-muted">ID: {{ $user->systemUser->custom_id }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>

                            <!-- STATUS -->
                            <td>
                                @if($user->trashed())
                                    <span class="badge bg-danger custom-badge">
                                        Deleted
                                    </span>
                                @elseif($user->is_active)
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
                                    <a href="{{ route('admin.users.show', $user) }}" class="action-btn view-btn"
                                        title="View User">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    @if(!$user->trashed())
                                        <!-- EDIT -->
                                        <a href="{{ route('admin.users.edit', $user) }}" class="action-btn edit-btn"
                                            title="Edit User">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <!-- TOGGLE ACTIVE -->
                                        <form method="POST" action="{{ route('admin.users.toggleActive', $user) }}"
                                            onsubmit="return confirm('Change user status?')" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-btn toggle-btn" title="Toggle Status">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>

                                        <!-- CHANGE PASSWORD -->
                                        <button type="button" class="action-btn password-btn" title="Change Password"
                                            data-bs-toggle="modal" data-bs-target="#passwordModal{{ $user->id }}">
                                            <i class="bi bi-key-fill"></i>
                                        </button>

                                        <!-- DELETE -->
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Delete this user?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    @else
                                        <!-- RESTORE -->
                                        <form method="POST" action="{{ route('admin.users.restore', $user->id) }}"
                                            onsubmit="return confirm('Restore this user?')" class="d-inline">
                                            @csrf
                                            <button type="submit" class="action-btn restore-btn" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>

                                        <!-- FORCE DELETE -->
                                        <form method="POST" action="{{ route('admin.users.forceDelete', $user->id) }}"
                                            onsubmit="return confirm('Permanently delete this user? This cannot be undone!')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn force-delete-btn" title="Permanently Delete">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    @endif

                                </div>

                            </td>

                        </tr>

                        <!-- PASSWORD MODAL -->
                        @if(!$user->trashed())
                            <div class="modal fade" id="passwordModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.users.changePassword', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Change Password - {{ $user->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">New Password</label>
                                                    <input type="password" name="password" class="form-control" required minlength="8">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Confirm Password</label>
                                                    <input type="password" name="password_confirmation" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @empty

                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <h5>No Users Found</h5>
                                    <p>Try adjusting search filters or create a new user</p>
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-lg"></i> Add User
                                    </a>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- PAGINATION -->
        <div class="mt-4">
            {{ $users->links() }}
        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    .users-page {
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
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eef2f7;
        transition: all 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
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
        flex-shrink: 0;
    }

    .stats-icon.blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .stats-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
    .stats-icon.orange { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .stats-icon.red { background: linear-gradient(135deg, #ef4444, #f87171); }

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
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .custom-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
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

    /* BULK ACTIONS */
    .bulk-actions {
        background: #f8fafc;
        border-radius: 14px;
        padding: 0.8rem 1rem;
        margin-bottom: 1rem;
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
    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .user-name {
        font-weight: 600;
    }

    /* BADGES */
    .custom-badge {
        border-radius: 10px;
        padding: 0.5rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* ACTIONS */
    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .view-btn { background: #eff6ff; color: #2563eb; }
    .edit-btn { background: #fef3c7; color: #d97706; }
    .toggle-btn { background: #ecfdf5; color: #10b981; }
    .password-btn { background: #f3e8ff; color: #7c3aed; }
    .delete-btn { background: #fef2f2; color: #ef4444; }
    .restore-btn { background: #ecfdf5; color: #10b981; }
    .force-delete-btn { background: #fef2f2; color: #dc2626; }

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
            text-align: center;
            justify-content: center;
        }

        .bulk-actions .row {
            flex-direction: column;
            align-items: stretch !important;
            gap: 0.5rem;
        }

        .bulk-actions .col-auto {
            width: 100%;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select All checkbox
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const selectedCount = document.getElementById('selectedCount');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateSelectedCount();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const checked = document.querySelectorAll('.user-checkbox:checked').length;
            if (selectedCount) {
                selectedCount.textContent = checked + ' selected';
            }
        }

        // Bulk action form
        const bulkForm = document.getElementById('bulkActionForm');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                const checked = document.querySelectorAll('.user-checkbox:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one user.');
                    return false;
                }

                const action = document.querySelector('select[name="action"]');
                if (!action.value) {
                    e.preventDefault();
                    alert('Please select an action.');
                    return false;
                }

                // Add user IDs to form
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = cb.value;
                    bulkForm.appendChild(input);
                });
            });
        }

        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const closeBtn = alert.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.click();
                }
            }, 5000);
        });
    });
</script>
@endpush