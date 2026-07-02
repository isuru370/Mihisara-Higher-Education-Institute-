@extends('layouts.app')

@section('title', 'FCM Tokens')
@section('page-title', 'FCM Device Tokens')

@section('content')

<div class="fcm-tokens-page">

    {{-- STATS --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4">
            <div class="stats-card">
                <div class="stats-icon purple">
                    <i class="bi bi-device"></i>
                </div>
                <div>
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Tokens</p>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="stats-card">
                <div class="stats-icon green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Active</p>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="stats-card">
                <div class="stats-icon red">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div>
                    <h3>{{ $stats['inactive'] }}</h3>
                    <p>Inactive</p>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-phone"></i>
                </div>
                <div>
                    <h3>{{ $stats['android'] }}</h3>
                    <p>Android</p>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="stats-card">
                <div class="stats-icon teal">
                    <i class="bi bi-apple"></i>
                </div>
                <div>
                    <h3>{{ $stats['ios'] }}</h3>
                    <p>iOS</p>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4">
            <div class="stats-card">
                <div class="stats-icon orange">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <h3>{{ $stats['unique_students'] }}</h3>
                    <p>Students</p>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN CARD --}}
    <div class="main-card">

        {{-- HEADER --}}
        <div class="main-card-header">
            <div>
                <h4>FCM Token Management</h4>
                <p>Manage device tokens for push notifications</p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.fcm-tokens.export', request()->query()) }}" class="btn btn-success custom-btn">
                    <i class="bi bi-file-earmark-excel-fill"></i>
                    Export
                </a>

                <form action="{{ route('admin.fcm-tokens.delete-inactive') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger custom-btn"
                            onclick="return confirm('Delete all inactive tokens?')">
                        <i class="bi bi-trash-fill"></i>
                        Delete Inactive
                    </button>
                </form>
            </div>
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="alert alert-success custom-alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning custom-alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('warning') }}
            </div>
        @endif

        {{-- SEARCH / FILTER --}}
        <div class="search-card">
            <form method="GET" action="{{ route('admin.fcm-tokens.index') }}">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-4">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" name="search" class="form-control custom-input"
                                placeholder="Search by token, device, student..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <select name="status" class="form-select custom-input">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <select name="device_type" class="form-select custom-input">
                            <option value="">All Devices</option>
                            <option value="android" {{ request('device_type') == 'android' ? 'selected' : '' }}>Android</option>
                            <option value="ios" {{ request('device_type') == 'ios' ? 'selected' : '' }}>iOS</option>
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <button class="btn btn-primary w-100 custom-btn" type="submit">
                            <i class="bi bi-funnel-fill"></i>
                            Filter
                        </button>
                    </div>

                    <div class="col-lg-2">
                        <a href="{{ route('admin.fcm-tokens.index') }}" class="btn btn-light border w-100 custom-btn">
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
                        <th>Student</th>
                        <th>Device</th>
                        <th>Token</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tokens as $token)
                        <tr>
                            <td>
                                {{ $loop->iteration + ($tokens->currentPage() - 1) * $tokens->perPage() }}
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                        {{ strtoupper(substr($token->student->initial_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">
                                            {{ $token->student->initial_name ?? 'Unknown' }}
                                        </div>
                                        <small class="text-muted">
                                            ID: {{ $token->student->custom_id ?? $token->student_id }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div>
                                    <i class="bi {{ $token->device_icon }} me-1"></i>
                                    <span class="fw-semibold">{{ $token->device_type_label }}</span>
                                    @if($token->device_name)
                                        <br>
                                        <small class="text-muted">{{ $token->device_name }}</small>
                                    @endif
                                    @if($token->app_version)
                                        <br>
                                        <small class="text-muted">v{{ $token->app_version }}</small>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div>
                                    <code class="bg-light p-1 rounded">{{ $token->masked_token }}</code>
                                    <br>
                                    <button class="btn btn-sm btn-outline-secondary mt-1 copy-btn"
                                            data-token="{{ $token->token }}"
                                            onclick="copyToken(this)">
                                        <i class="bi bi-clipboard"></i> Copy
                                    </button>
                                </div>
                            </td>

                            <td>
                                <span class="badge custom-badge bg-{{ $token->status_color }}">
                                    <i class="bi {{ $token->is_active ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                    {{ $token->status_label }}
                                </span>
                            </td>

                            <td>
                                {{ $token->last_login_at?->format('Y-m-d') ?? 'Never' }}
                                <br>
                                <small class="text-muted">{{ $token->last_login_at?->format('H:i') ?? '' }}</small>
                            </td>

                            <td>
                                {{ $token->created_at->format('Y-m-d') }}
                                <br>
                                <small class="text-muted">{{ $token->created_at->format('H:i') }}</small>
                            </td>

                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.fcm-tokens.show', $token->id) }}"
                                        class="action-btn view-btn" title="View">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    @if($token->is_active)
                                        <form action="{{ route('admin.fcm-tokens.deactivate', $token->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="action-btn cancel-btn"
                                                    onclick="return confirm('Deactivate this token?')" title="Deactivate">
                                                <i class="bi bi-pause-circle-fill"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.fcm-tokens.activate', $token->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="action-btn retry-btn"
                                                    onclick="return confirm('Activate this token?')" title="Activate">
                                                <i class="bi bi-play-circle-fill"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.fcm-tokens.destroy', $token->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete-btn"
                                                onclick="return confirm('Delete this token?')" title="Delete">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="empty-state">
                                    <i class="bi bi-device"></i>
                                    <h5>No FCM Tokens Found</h5>
                                    <p>Try adjusting search filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $tokens->links() }}
        </div>
    </div>
</div>

@endsection

@push('styles')
    <style>
        .fcm-tokens-page {
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
            transition: .3s ease;
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
            color: #fff;
            flex-shrink: 0;
        }

        .blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .green { background: linear-gradient(135deg, #10b981, #34d399); }
        .orange { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .red { background: linear-gradient(135deg, #ef4444, #f87171); }
        .purple { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
        .teal { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }

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
            transition: .3s ease;
            font-size: .9rem;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .custom-alert {
            border-radius: 16px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
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
            transition: .3s ease;
        }

        .custom-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1) !important;
        }

        select.custom-input {
            padding-left: 16px;
            padding-right: 40px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
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
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
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
            display: inline-flex;
            align-items: center;
            gap: .3rem;
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
            cursor: pointer;
            font-size: .9rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .view-btn { background: #eff6ff; color: #2563eb; }
        .delete-btn { background: #fef2f2; color: #ef4444; }
        .cancel-btn { background: #fef3c7; color: #d97706; }
        .retry-btn { background: #d1fae5; color: #065f46; }

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

        .copy-btn {
            font-size: .75rem;
            padding: .15rem .5rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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

            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 48px;
                height: 48px;
                font-size: 1.2rem;
            }

            .stats-card h3 {
                font-size: 1.2rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function copyToken(button) {
            const token = button.dataset.token;
            navigator.clipboard.writeText(token).then(() => {
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="bi bi-check"></i> Copied!';
                setTimeout(() => {
                    button.innerHTML = originalHtml;
                }, 2000);
            });
        }
    </script>
@endpush