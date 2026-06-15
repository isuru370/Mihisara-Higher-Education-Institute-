@extends('layouts.app')

@section('title', 'System User Permissions')
@section('page-title', 'System User Permissions')

@section('content')
    @php
        $groupedPages = $allPages->groupBy('module');
    @endphp

    <div class="permissions-page">

        {{-- HERO CARD --}}
        <div class="hero-card mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="mb-3">
                        <span class="hero-badge">
                            <i class="bi bi-shield-lock-fill"></i>
                            Permission Management
                        </span>
                    </div>
                    <h2 class="fw-bold mb-2">User Permissions</h2>
                    <p class="mb-0 text-light-soft">
                        Manage access controls and permissions for the selected system user.
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-people-fill"></i>
                        {{ $systemUser->full_name }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- USER SUMMARY CARD --}}
        <div class="user-summary-card mb-4">
            <div class="user-summary-header">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-person-badge-fill me-2"></i>User Information
                </h6>
                <a href="{{ route('admin.system-users.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
            <div class="user-summary-body">
                <div class="summary-item">
                    <div class="summary-label">Full Name</div>
                    <div class="summary-value">{{ $systemUser->full_name }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Email Address</div>
                    <div class="summary-value">{{ $systemUser->user?->email ?? '-' }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">User Type</div>
                    <div class="summary-value">
                        <span class="user-type-badge">{{ $user?->userType?->name ?? '-' }}</span>
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Mobile Number</div>
                    <div class="summary-value">{{ $systemUser->mobile }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Status</div>
                    <div class="summary-value">
                        @if($systemUser->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Last Login</div>
                    <div class="summary-value">
                        {{ $systemUser->last_login_at ? \Carbon\Carbon::parse($systemUser->last_login_at)->format('d M Y h:i A') : '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="alert alert-success custom-alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger custom-alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- PERMISSIONS FORM --}}
        <form method="POST" action="{{ route('admin.user-permissions.store', $systemUser->id) }}">
            @csrf

            <div class="permissions-card">
                <div class="permissions-card-header">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-table me-2"></i>Permission Matrix
                        </h5>
                        <p class="text-muted mb-0 small">Configure access rights for each module</p>
                    </div>
                    <div class="permission-legend">
                        <span class="legend-item"><span class="legend-color view"></span> View</span>
                        <span class="legend-item"><span class="legend-color create"></span> Create</span>
                        <span class="legend-item"><span class="legend-color update"></span> Update</span>
                        <span class="legend-item"><span class="legend-color delete"></span> Delete</span>
                        <span class="legend-item"><span class="legend-color active"></span> Active</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="permissions-table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Module / Page</th>
                                <th style="width: 25%;">Route Name</th>
                                <th class="text-center" style="width: 9%;">View</th>
                                <th class="text-center" style="width: 9%;">Create</th>
                                <th class="text-center" style="width: 9%;">Update</th>
                                <th class="text-center" style="width: 9%;">Delete</th>
                                <th class="text-center" style="width: 9%;">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedPages as $module => $pages)
                                <tr class="module-row">
                                    <td colspan="7">
                                        <div class="module-title">
                                            <i class="bi bi-folder-fill me-2"></i>
                                            {{ ucwords(str_replace('_', ' ', $module)) }}
                                        </div>
                                    </td>
                                </tr>

                                @foreach($pages as $page)
                                    @php
                                        $permission = $permissions[$page->id] ?? null;
                                    @endphp

                                    <tr class="page-row">
                                        <td>
                                            <div class="page-name">
                                                <i class="bi bi-file-text-fill me-2"></i>
                                                {{ $page->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <code class="route-badge">{{ $page->route_name }}</code>
                                        </td>
                                        <td class="text-center">
                                            <input type="hidden" name="permissions[{{ $page->id }}][page_id]"
                                                value="{{ $page->id }}">
                                            <label class="checkbox-label">
                                                <input type="checkbox" class="perm-check"
                                                    name="permissions[{{ $page->id }}][can_view]" value="1" {{ old("permissions.$page->id.can_view", $permission?->can_view) ? 'checked' : '' }}>
                                                <span class="checkmark view-check"></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class="checkbox-label">
                                                <input type="checkbox" class="perm-check"
                                                    name="permissions[{{ $page->id }}][can_create]" value="1" {{ old("permissions.$page->id.can_create", $permission?->can_create) ? 'checked' : '' }}>
                                                <span class="checkmark create-check"></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class="checkbox-label">
                                                <input type="checkbox" class="perm-check"
                                                    name="permissions[{{ $page->id }}][can_update]" value="1" {{ old("permissions.$page->id.can_update", $permission?->can_update) ? 'checked' : '' }}>
                                                <span class="checkmark update-check"></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class="checkbox-label">
                                                <input type="checkbox" class="perm-check"
                                                    name="permissions[{{ $page->id }}][can_delete]" value="1" {{ old("permissions.$page->id.can_delete", $permission?->can_delete) ? 'checked' : '' }}>
                                                <span class="checkmark delete-check"></span>
                                            </label>
                                        </td>
                                        <td class="text-center">
                                            <label class="checkbox-label">
                                                <input type="checkbox" class="perm-check"
                                                    name="permissions[{{ $page->id }}][is_active]" value="1" {{ old("permissions.$page->id.is_active", $permission?->is_active ?? true) ? 'checked' : '' }}>
                                                <span class="checkmark active-check"></span>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="permissions-card-footer">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.system-users.index') }}" class="btn-cancel">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn-save">
                            <i class="bi bi-shield-check me-1"></i> Save Permissions
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <style>
        .permissions-page {
            animation: fadeIn .4s ease;
        }

        /* Hero Card */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            padding: 2rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8, #1e40af);
            box-shadow: 0 20px 45px rgba(37, 99, 235, .25);
            color: #fff;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            top: -100px;
            right: -80px;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            bottom: -60px;
            left: -40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(10px);
            font-size: .85rem;
            font-weight: 600;
        }

        .text-light-soft {
            color: rgba(255, 255, 255, .78);
        }

        /* User Summary Card */
        .user-summary-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .user-summary-header {
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .btn-back {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.4rem 1rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-back:hover {
            background: #475569;
            transform: translateY(-1px);
            color: white;
        }

        .user-summary-body {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }

        .summary-item {
            padding: 0.5rem;
        }

        .summary-label {
            display: block;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .summary-value {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.9rem;
        }

        .user-type-badge {
            display: inline-block;
            background: linear-gradient(135deg, #dbeafe, #eff6ff);
            color: #1e40af;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Permissions Card */
        .permissions-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .permissions-card-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .permission-legend {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            color: #64748b;
        }

        .legend-color {
            width: 14px;
            height: 14px;
            border-radius: 4px;
        }

        .legend-color.view {
            background: #2563eb;
        }

        .legend-color.create {
            background: #10b981;
        }

        .legend-color.update {
            background: #f59e0b;
        }

        .legend-color.delete {
            background: #ef4444;
        }

        .legend-color.active {
            background: #8b5cf6;
        }

        /* Permissions Table */
        .permissions-table {
            width: 100%;
            border-collapse: collapse;
        }

        .permissions-table thead th {
            background: #f8fafc;
            padding: 1rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .permissions-table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .module-row td {
            background: #eff6ff;
            padding: 0.75rem 1rem;
        }

        .module-title {
            color: #1d4ed8;
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .page-row:hover {
            background: #f8fafc;
        }

        .page-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
        }

        .route-badge {
            display: inline-block;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.3rem 0.6rem;
            font-size: 0.7rem;
            color: #64748b;
            font-family: monospace;
        }

        /* Custom Checkbox */
        .checkbox-label {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .perm-check {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: relative;
            display: inline-block;
            height: 20px;
            width: 20px;
            background: #f1f5f9;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .checkmark:hover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .perm-check:checked+.checkmark {
            background: #2563eb;
            border-color: #2563eb;
        }

        .perm-check:checked+.checkmark::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        /* Different colors for different permission types */
        .perm-check:checked+.view-check {
            background: #2563eb;
            border-color: #2563eb;
        }

        .perm-check:checked+.create-check {
            background: #10b981;
            border-color: #10b981;
        }

        .perm-check:checked+.update-check {
            background: #f59e0b;
            border-color: #f59e0b;
        }

        .perm-check:checked+.delete-check {
            background: #ef4444;
            border-color: #ef4444;
        }

        .perm-check:checked+.active-check {
            background: #8b5cf6;
            border-color: #8b5cf6;
        }

        /* Footer */
        .permissions-card-footer {
            padding: 1.25rem 1.5rem;
            background: #f8fafc;
            border-top: 1px solid #eef2f7;
        }

        .btn-save {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .btn-cancel {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-cancel:hover {
            background: #475569;
            transform: translateY(-2px);
            color: white;
        }

        /* Custom Alert */
        .custom-alert {
            border-radius: 16px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .user-summary-body {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }

            .user-summary-body {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .permissions-card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .permissions-table thead th {
                font-size: 0.6rem;
                padding: 0.5rem;
            }

            .permissions-table tbody td {
                padding: 0.5rem;
            }

            .route-badge {
                font-size: 0.6rem;
                padding: 0.2rem 0.4rem;
            }

            .page-name {
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .user-summary-body {
                grid-template-columns: 1fr;
            }

            .permission-legend {
                justify-content: flex-start;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.custom-alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
@endpush