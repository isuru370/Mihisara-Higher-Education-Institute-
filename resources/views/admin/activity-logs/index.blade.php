@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')

    <div class="activity-logs-page">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div>
                        <h3>{{ $logs->total() }}</h3>
                        <p>Total Activities</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <div>
                        <h3>{{ $logs->where('action', 'created')->count() }}</h3>
                        <p>Created</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon primary">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h3>{{ $logs->where('action', 'updated')->count() }}</h3>
                        <p>Updated</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon red">
                        <i class="bi bi-trash"></i>
                    </div>
                    <div>
                        <h3>{{ $logs->where('action', 'deleted')->count() + $logs->where('action', 'force_deleted')->count() }}
                        </h3>
                        <p>Deleted</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="logs-card">
            <div class="logs-card-header">
                <div>
                    <h4>
                        <i class="bi bi-clock-history me-2"></i>
                        Activity Logs
                    </h4>
                    <p>Track and monitor all system activities and changes</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-refresh" onclick="window.location.reload()">
                        <i class="bi bi-arrow-repeat me-2"></i> Refresh
                    </button>
                </div>
            </div>

            {{-- Search & Filter Section --}}
            <div class="filter-section">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-lg-3">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search by user, table or record ID..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <select name="action" class="form-select custom-input">
                                <option value="">All Actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <select name="table_name" class="form-select custom-input">
                                <option value="">All Tables</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table }}" {{ request('table_name') == $table ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $table)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <select name="user_id" class="form-select custom-input">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <input type="date" name="date_from" class="form-control custom-input" placeholder="From Date"
                                value="{{ request('date_from') }}">
                        </div>

                        <div class="col-lg-2">
                            <input type="date" name="date_to" class="form-control custom-input" placeholder="To Date"
                                value="{{ request('date_to') }}">
                        </div>

                        <div class="col-lg-12">
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i> Apply Filters
                                </button>
                                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-light border">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reset
                                </a>
                                <a href="{{ route('admin.activity-logs.export', request()->query()) }}"
                                    class="btn btn-success">
                                    <i class="bi bi-download me-1"></i> Export CSV
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#clearLogsModal">
                                    <i class="bi bi-trash me-1"></i> Clear Old
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="logs-card-body">
                <div class="table-responsive">
                    <table class="logs-table">
                        <thead>
                            <tr>
                                <th style="width: 160px;">Date & Time</th>
                                <th style="width: 180px;">User</th>
                                <th style="width: 110px;">Action</th>
                                <th>Table</th>
                                <th style="width: 80px;">Record ID</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <div class="date-time-cell">
                                            <div class="date">
                                                <i class="bi bi-calendar3"></i>
                                                <span>{{ $log->created_at?->format('Y-m-d') }}</span>
                                            </div>
                                            <div class="time">
                                                <i class="bi bi-clock"></i>
                                                <span>{{ $log->created_at?->format('h:i A') }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="user-name">
                                                    {{ $log->user?->name ?? 'System' }}
                                                </div>
                                                <small class="text-muted">
                                                    ID: {{ $log->user_id ?? '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        @php
                                            $actionColors = [
                                                'created' => ['class' => 'action-created', 'icon' => 'bi-plus-circle', 'label' => 'CREATED'],
                                                'updated' => ['class' => 'action-updated', 'icon' => 'bi-pencil-square', 'label' => 'UPDATED'],
                                                'deleted' => ['class' => 'action-deleted', 'icon' => 'bi-trash', 'label' => 'DELETED'],
                                                'force_deleted' => ['class' => 'action-force-deleted', 'icon' => 'bi-trash3', 'label' => 'FORCE DELETED'],
                                            ];
                                            $action = strtolower($log->action);
                                            $actionStyle = $actionColors[$action] ?? ['class' => 'action-default', 'icon' => 'bi-record-circle', 'label' => strtoupper($action)];
                                        @endphp
                                        <span class="action-badge {{ $actionStyle['class'] }}">
                                            <i class="bi {{ $actionStyle['icon'] }} me-1"></i>
                                            {{ $actionStyle['label'] }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="table-name">
                                            <i class="bi bi-table me-1"></i>
                                            {{ $log->table_name }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="record-id">
                                            #{{ $log->record_id }}
                                        </span>
                                    </td>

                                    <td>
                                        <button type="button" class="btn-view" data-bs-toggle="modal"
                                            data-bs-target="#logDetailsModal" data-id="{{ $log->id }}"
                                            data-date="{{ $log->created_at?->format('Y-m-d h:i A') }}"
                                            data-user="{{ $log->user?->name ?? 'System' }}"
                                            data-userid="{{ $log->user_id ?? '-' }}" data-action="{{ $log->action }}"
                                            data-table="{{ $log->table_name }}" data-record="{{ $log->record_id }}"
                                            data-old='@json($log->old_values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)'
                                            data-new='@json($log->new_values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)'>
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>
                                            <h5>No Activity Logs Found</h5>
                                            <p>Try adjusting your search filters</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </tr>
                </div>

                @if($logs->hasPages())
                    <div class="logs-pagination">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Clear Logs Modal --}}
    <div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        Clear Old Logs
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.activity-logs.clear') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Delete activity logs older than:</p>
                        <select name="days" class="form-select">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                            <option value="180">180 days</option>
                            <option value="365">1 year</option>
                        </select>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            This action cannot be undone.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Clear Logs</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal for Log Details - Terminal/Code Viewer Style --}}
    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">
                            <i class="bi bi-code-square me-2"></i>
                            Activity Log Details
                        </h5>
                        <small class="text-muted" id="modalLogDate"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">User</span>
                                    <span class="info-value" id="modalUser"></span>
                                    <span class="info-sub">User ID: <span id="modalUserId"></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-card">
                                <div class="info-icon action-info-icon">
                                    <i class="bi bi-lightning-charge"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Action Details</span>
                                    <span class="info-value" id="modalAction"></span>
                                    <span class="info-sub">
                                        Table: <span id="modalTable"></span> |
                                        Record ID: <span id="modalRecord"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="code-card">
                                <div class="code-card-header">
                                    <div class="code-header-left">
                                        <i class="bi bi-archive"></i>
                                        <span>Old Values</span>
                                        <span class="json-badge">JSON</span>
                                    </div>
                                    <div class="code-header-right">
                                        <button class="copy-btn" data-target="modalOldValues">
                                            <i class="bi bi-clipboard"></i>
                                            Copy
                                        </button>
                                        <span class="line-count"></span>
                                    </div>
                                </div>
                                <div class="code-container">
                                    <pre class="code-content" id="modalOldValues">-</pre>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="code-card">
                                <div class="code-card-header">
                                    <div class="code-header-left">
                                        <i class="bi bi-file-text"></i>
                                        <span>New Values</span>
                                        <span class="json-badge json-badge-new">JSON</span>
                                    </div>
                                    <div class="code-header-right">
                                        <button class="copy-btn" data-target="modalNewValues">
                                            <i class="bi bi-clipboard"></i>
                                            Copy
                                        </button>
                                        <span class="line-count"></span>
                                    </div>
                                </div>
                                <div class="code-container">
                                    <pre class="code-content" id="modalNewValues">-</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .activity-logs-page {
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats Cards */
        .stats-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f7;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.08);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.8rem;
        }

        .stats-icon.blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .stats-icon.green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .stats-icon.primary {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .stats-icon.red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .stats-card h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stats-card p {
            margin: 0;
            color: #64748b;
            font-weight: 500;
        }

        /* Logs Card */
        .logs-card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .logs-card-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logs-card-header h4 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .logs-card-header p {
            margin: 0;
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            gap: 0.8rem;
        }

        .btn-refresh {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-refresh:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        /* Filter Section */
        .filter-section {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
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
            font-size: 1rem;
        }

        .custom-input {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0;
            min-height: 44px;
            padding-left: 42px;
            transition: all 0.2s ease;
        }

        .custom-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        select.custom-input {
            padding-left: 42px;
            cursor: pointer;
        }

        .filter-actions {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .filter-actions .btn {
            padding: 0.5rem 1.2rem;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Table Styles */
        .logs-card-body {
            padding: 0;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table thead th {
            background: #f8fafc;
            padding: 1rem 1.2rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .logs-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }

        .logs-table tbody tr:hover {
            background: #f8fafc;
        }

        .logs-table tbody td {
            padding: 1rem 1.2rem;
            font-size: 0.85rem;
            color: #334155;
            vertical-align: middle;
        }

        /* Date Time Cell */
        .date-time-cell {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date,
        .time {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .date i,
        .time i {
            color: #64748b;
            font-size: 0.8rem;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 8px 18px rgba(79, 70, 229, 0.2);
        }

        .user-name {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.2rem;
        }

        /* Action Badges */
        .action-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            gap: 0.3rem;
        }

        .action-created {
            background: #d1fae5;
            color: #059669;
        }

        .action-updated {
            background: #e0e7ff;
            color: #4338ca;
        }

        .action-deleted {
            background: #fee2e2;
            color: #dc2626;
        }

        .action-force-deleted {
            background: #fef3c7;
            color: #d97706;
        }

        .action-default {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Table Name */
        .table-name {
            font-family: monospace;
            font-size: 0.8rem;
            background: #f1f5f9;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            display: inline-block;
        }

        /* Record ID */
        .record-id {
            font-family: monospace;
            font-size: 0.8rem;
            font-weight: 600;
            color: #4f46e5;
            background: #eff6ff;
            padding: 0.3rem 0.6rem;
            border-radius: 8px;
            display: inline-block;
        }

        /* View Button */
        .btn-view {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            padding: 0.4rem 1rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #94a3b8;
        }

        /* Pagination */
        .logs-pagination {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .logs-pagination .pagination {
            justify-content: center;
            margin: 0;
        }

        .logs-pagination .page-link {
            border-radius: 10px;
            margin: 0 2px;
            border: 1px solid #e2e8f0;
            color: #475569;
            transition: all 0.2s ease;
        }

        .logs-pagination .page-link:hover {
            background: #eff6ff;
            border-color: #4f46e5;
            color: #1e40af;
            transform: translateY(-2px);
        }

        .logs-pagination .active .page-link {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-color: #4f46e5;
            color: white;
        }

        /* Info Cards */
        .info-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .action-info-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .info-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
        }

        .info-sub {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        /* Code Card Styles - Terminal/Code Viewer */
        .code-card {
            background: #1e1e2e;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .code-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .code-card-header {
            background: #181825;
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #313244;
        }

        .code-header-left {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #cdd6f4;
        }

        .code-header-left i {
            color: #89b4fa;
            font-size: 1rem;
        }

        .json-badge {
            background: #89b41e;
            color: #1e1e2e;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .json-badge-new {
            background: #f9e2af;
            color: #1e1e2e;
        }

        .code-header-right {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .copy-btn {
            background: #313244;
            color: #cdd6f4;
            border: none;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .copy-btn:hover {
            background: #45475a;
            transform: translateY(-1px);
        }

        .copy-btn.copied {
            background: #89b41e;
            color: #1e1e2e;
        }

        .line-count {
            font-size: 0.6rem;
            color: #6c7086;
            font-family: monospace;
        }

        .code-container {
            position: relative;
            max-height: 400px;
            overflow: auto;
        }

        .code-content {
            margin: 0;
            padding: 1rem;
            font-family: 'Fira Code', 'Monaco', 'Menlo', 'Cascadia Code', 'Consolas', monospace;
            font-size: 0.7rem;
            line-height: 1.5;
            color: #cdd6f4;
            background: #1e1e2e;
            white-space: pre-wrap;
            word-break: break-word;
            tab-size: 2;
        }

        /* Syntax highlighting for JSON */
        .code-content .json-key {
            color: #89b4fa;
        }

        .code-content .json-string {
            color: #a6e3a1;
        }

        .code-content .json-number {
            color: #fab387;
        }

        .code-content .json-boolean {
            color: #cba6f7;
        }

        .code-content .json-null {
            color: #f38ba8;
        }

        /* Custom Scrollbar for Code Container */
        .code-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .code-container::-webkit-scrollbar-track {
            background: #181825;
        }

        .code-container::-webkit-scrollbar-thumb {
            background: #45475a;
            border-radius: 4px;
        }

        .code-container::-webkit-scrollbar-thumb:hover {
            background: #6c7086;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 24px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 2px solid #e2e8f0;
            padding: 1.2rem 1.5rem;
        }

        .modal-header .modal-title {
            font-weight: 700;
            color: #1e293b;
        }

        .modal-body {
            padding: 1.5rem;
            background: #f8fafc;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            background: #f8fafc;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .logs-card-header {
                padding: 1rem;
                flex-direction: column;
                align-items: stretch;
            }

            .filter-section {
                padding: 1rem;
            }

            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .stats-card h3 {
                font-size: 1.3rem;
            }

            .logs-table {
                font-size: 0.75rem;
            }

            .logs-table tbody td {
                padding: 0.75rem;
            }

            .user-avatar {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }

            .btn-view {
                padding: 0.3rem 0.8rem;
                font-size: 0.7rem;
            }

            .filter-actions {
                flex-direction: column;
            }

            .filter-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('logDetailsModal');

            // Syntax highlighting for JSON
            function syntaxHighlight(json) {
                if (json === 'No old values available' || json === 'No new values available' || json === '-') {
                    return json;
                }

                try {
                    let obj = typeof json === 'string' ? JSON.parse(json) : json;
                    let jsonStr = JSON.stringify(obj, null, 2);

                    jsonStr = jsonStr.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

                    return jsonStr.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                        let cls = 'json-number';
                        if (/^"/.test(match)) {
                            if (/:$/.test(match)) {
                                cls = 'json-key';
                                match = match.replace(/:/g, '');
                                return '<span class="' + cls + '">' + match + '</span>:';
                            } else {
                                cls = 'json-string';
                            }
                        } else if (/true|false/.test(match)) {
                            cls = 'json-boolean';
                        } else if (/null/.test(match)) {
                            cls = 'json-null';
                        }
                        return '<span class="' + cls + '">' + match + '</span>';
                    });
                } catch (e) {
                    return json;
                }
            }

            // Copy to clipboard function
            function copyToClipboard(text, button) {
                navigator.clipboard.writeText(text).then(function () {
                    const originalHtml = button.innerHTML;
                    button.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                    button.classList.add('copied');

                    setTimeout(function () {
                        button.innerHTML = originalHtml;
                        button.classList.remove('copied');
                    }, 2000);
                }).catch(function () {
                    button.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Failed!';
                    setTimeout(function () {
                        button.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                        button.classList.remove('copied');
                    }, 2000);
                });
            }

            // Count lines function
            function countLines(text) {
                if (!text || text === '-') return 0;
                return text.split(/\r\n|\r|\n/).length;
            }

            // Update line count display
            function updateLineCount(element, targetId) {
                const text = element.textContent;
                const lines = countLines(text);
                const btn = document.querySelector(`.copy-btn[data-target="${targetId}"]`);
                if (btn) {
                    const header = btn.closest('.code-card-header');
                    const lineCountSpan = header.querySelector('.line-count');
                    if (lineCountSpan) {
                        lineCountSpan.textContent = lines > 0 ? `${lines} lines` : '';
                    }
                }
            }

            if (modal) {
                modal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;

                    const date = button.getAttribute('data-date') || '-';
                    const user = button.getAttribute('data-user') || '-';
                    const userId = button.getAttribute('data-userid') || '-';
                    let action = button.getAttribute('data-action') || '-';
                    const table = button.getAttribute('data-table') || '-';
                    const record = button.getAttribute('data-record') || '-';

                    let oldValues = button.getAttribute('data-old') || '{}';
                    let newValues = button.getAttribute('data-new') || '{}';

                    // Format action display
                    switch (action.toLowerCase()) {
                        case 'created':
                            action = 'CREATED';
                            break;
                        case 'updated':
                            action = 'UPDATED';
                            break;
                        case 'deleted':
                            action = 'DELETED';
                            break;
                        case 'force_deleted':
                            action = 'FORCE DELETED';
                            break;
                        default:
                            action = action.toUpperCase();
                    }

                    // Parse and format old values
                    try {
                        const oldParsed = JSON.parse(oldValues);
                        if (Object.keys(oldParsed).length === 0 || !oldParsed) {
                            oldValues = 'No old values available';
                        } else {
                            oldValues = JSON.stringify(oldParsed, null, 2);
                        }
                    } catch (e) {
                        oldValues = oldValues || 'No old values available';
                    }

                    // Parse and format new values
                    try {
                        const newParsed = JSON.parse(newValues);
                        if (Object.keys(newParsed).length === 0 || !newParsed) {
                            newValues = 'No new values available';
                        } else {
                            newValues = JSON.stringify(newParsed, null, 2);
                        }
                    } catch (e) {
                        newValues = newValues || 'No new values available';
                    }

                    // Set modal header
                    document.getElementById('modalLogDate').textContent = date;
                    document.getElementById('modalUser').textContent = user;
                    document.getElementById('modalUserId').textContent = userId;
                    document.getElementById('modalAction').textContent = action;
                    document.getElementById('modalTable').textContent = table;
                    document.getElementById('modalRecord').textContent = record;

                    // Set and highlight old values
                    const oldElement = document.getElementById('modalOldValues');
                    if (oldValues !== 'No old values available') {
                        oldElement.innerHTML = syntaxHighlight(oldValues);
                    } else {
                        oldElement.textContent = oldValues;
                    }
                    updateLineCount(oldElement, 'modalOldValues');

                    // Set and highlight new values
                    const newElement = document.getElementById('modalNewValues');
                    if (newValues !== 'No new values available') {
                        newElement.innerHTML = syntaxHighlight(newValues);
                    } else {
                        newElement.textContent = newValues;
                    }
                    updateLineCount(newElement, 'modalNewValues');
                });

                // Copy button functionality
                document.querySelectorAll('.copy-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const targetId = this.getAttribute('data-target');
                        const targetElement = document.getElementById(targetId);
                        let textToCopy = targetElement.textContent;

                        if (textToCopy === 'No old values available' || textToCopy === 'No new values available') {
                            textToCopy = '';
                        }

                        copyToClipboard(textToCopy, this);
                    });
                });
            }
        });
    </script>
@endpush