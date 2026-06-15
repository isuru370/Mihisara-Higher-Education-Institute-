@extends('layouts.app')

@section('title', 'Settings - ' . config('app.name', 'EDU NEXORA'))
@section('page-title', 'Settings')

@section('content')

    <div class="settings-page">

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

        {{-- Backup Management Section --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="bi bi-database me-2 text-primary"></i>
                        Database Backup Management
                    </h3>
                    <p class="section-subtitle">Manage your database backups securely</p>
                </div>
            </div>
        </div>

        {{-- Backup Cards Row --}}
        <div class="row g-4 mb-5">

            {{-- Export Backup Card --}}
            <div class="col-md-6">
                <div class="backup-card export-card">
                    <div class="backup-card-icon export-icon">
                        <i class="bi bi-download"></i>
                    </div>
                    <div class="backup-card-content">
                        <h4>Export Backup</h4>
                        <p>Create and download a complete database backup instantly. The backup will include all your data
                            in a compressed ZIP format.</p>
                        <a href="{{ route('admin.setting.backup.export') }}" class="btn btn-export">
                            <i class="bi bi-download me-2"></i>
                            Export Database
                        </a>
                    </div>
                </div>
            </div>

            {{-- Import Backup Card --}}
            <div class="col-md-6">
                <div class="backup-card import-card">
                    <div class="backup-card-icon import-icon">
                        <i class="bi bi-upload"></i>
                    </div>
                    <div class="backup-card-content">
                        <h4>Import Backup</h4>
                        <p>Restore your database from a previously created backup ZIP file. This will replace your current
                            data.</p>

                        <form action="{{ route('admin.setting.backup.import') }}" method="POST"
                            enctype="multipart/form-data" id="importForm">
                            @csrf

                            <div class="file-upload-wrapper">
                                <div class="file-upload-area" onclick="document.getElementById('backup_file').click()">
                                    <i class="bi bi-cloud-upload"></i>
                                    <p>Click to choose backup file</p>
                                    <small>ZIP files only (Max 50MB)</small>
                                    <input type="file" name="backup_file" id="backup_file" class="d-none" accept=".zip"
                                        required>
                                </div>
                                <div id="selectedFileName" class="selected-file-name mt-2" style="display: none;">
                                    <i class="bi bi-file-zip"></i>
                                    <span></span>
                                </div>
                            </div>

                            @error('backup_file')
                                <div class="text-danger small mt-2">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror

                            <button type="submit" class="btn btn-import mt-3" onclick="return confirmImport()">
                                <i class="bi bi-upload me-2"></i>
                                Import Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        {{-- Backup Activity Logs Section --}}
        <div class="row">
            <div class="col-12">
                <div class="logs-card">

                    <div class="logs-header">
                        <div>
                            <h4>
                                <i class="bi bi-clock-history me-2"></i>
                                Backup Activity Logs
                            </h4>
                            <p>Track all backup and restore operations</p>
                        </div>
                        <div class="logs-stats">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ \App\Models\BackupLog::where('status', 'success')->count() }} Success
                            </span>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ \App\Models\BackupLog::where('status', 'failed')->count() }} Failed
                            </span>
                        </div>
                    </div>

                    <div class="logs-body">

                        @php
                            $logs = \App\Models\BackupLog::latest()->paginate(20);
                        @endphp

                        @if($logs->count() > 0)
                            <div class="table-responsive">
                                <table class="logs-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Status</th>
                                            <th>File Name</th>
                                            <th>IP Address</th>
                                            <th>Message</th>
                                            <th>Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
                                            <tr>
                                                <td>{{ $log->id }}</td>
                                                <td>
                                                    <div class="user-info">
                                                        <div class="user-avatar">
                                                            {{ strtoupper(substr(optional($log->user)->name ?? 'S', 0, 1)) }}
                                                        </div>
                                                        <span>{{ optional($log->user)->name ?? 'System' }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="action-badge action-{{ strtolower($log->action) }}">
                                                        <i
                                                            class="bi bi-{{ $log->action == 'export' ? 'download' : 'upload' }} me-1"></i>
                                                        {{ strtoupper($log->action) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($log->status == 'success')
                                                        <span class="status-badge status-success">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            Success
                                                        </span>
                                                    @else
                                                        <span class="status-badge status-failed">
                                                            <i class="bi bi-x-circle me-1"></i>
                                                            Failed
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="file-name" title="{{ $log->file_name }}">
                                                        <i class="bi bi-file-zip me-1"></i>
                                                        {{ Str::limit($log->file_name, 30) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="ip-address">
                                                        <i class="bi bi-wifi me-1"></i>
                                                        {{ $log->ip_address ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="message-text" title="{{ $log->message }}">
                                                        {{ Str::limit($log->message, 40) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="date-time">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        {{ $log->created_at?->format('Y-m-d') }}
                                                        <br>
                                                        <small>{{ $log->created_at?->format('H:i:s') }}</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center empty-logs">
                                                    <div class="empty-state">
                                                        <i class="bi bi-database"></i>
                                                        <h5>No Backup Logs Found</h5>
                                                        <p>Backup activities will appear here</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="logs-pagination">
                                {{ $logs->links() }}
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="bi bi-database"></i>
                                <h5>No Backup Logs Found</h5>
                                <p>Backup activities will appear here</p>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>

    </div>

@endsection

@push('styles')
    <style>
        .settings-page {
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

        /* Section Header */
        .section-header {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Backup Cards */
        .backup-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .backup-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .export-card {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
        }

        .import-card {
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        }

        .backup-card-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .export-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .import-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .backup-card-content h4 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }

        .backup-card-content p {
            color: #64748b;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        /* Buttons */
        .btn-export {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-export:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-import {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-import:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
            color: white;
        }

        /* File Upload */
        .file-upload-wrapper {
            width: 100%;
        }

        .file-upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .file-upload-area:hover {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .file-upload-area i {
            font-size: 2rem;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }

        .file-upload-area p {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
        }

        .file-upload-area small {
            color: #64748b;
            font-size: 0.7rem;
        }

        .selected-file-name {
            background: #f1f5f9;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-size: 0.8rem;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Logs Card */
        .logs-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .logs-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .logs-header h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .logs-header p {
            margin: 0;
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .logs-stats {
            display: flex;
            gap: 0.5rem;
        }

        .logs-stats .badge {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 12px;
        }

        .logs-body {
            padding: 0;
        }

        /* Logs Table */
        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table thead th {
            background: #f8fafc;
            padding: 1rem 1rem;
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
            padding: 1rem;
            font-size: 0.8rem;
            color: #334155;
            vertical-align: middle;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Action Badge */
        .action-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .action-export {
            background: #d1fae5;
            color: #059669;
        }

        .action-import {
            background: #dbeafe;
            color: #2563eb;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-success {
            background: #d1fae5;
            color: #059669;
        }

        .status-failed {
            background: #fee2e2;
            color: #dc2626;
        }

        /* File Name */
        .file-name {
            font-family: monospace;
            font-size: 0.7rem;
            background: #f1f5f9;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* IP Address */
        .ip-address {
            font-family: monospace;
            font-size: 0.7rem;
            background: #f1f5f9;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* Message Text */
        .message-text {
            max-width: 200px;
            font-size: 0.7rem;
            color: #64748b;
        }

        /* Date Time */
        .date-time {
            font-size: 0.7rem;
            line-height: 1.4;
        }

        .date-time small {
            color: #94a3b8;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .empty-logs {
            text-align: center;
            padding: 3rem;
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
            border-radius: 8px;
            margin: 0 2px;
            border: 1px solid #e2e8f0;
            color: #475569;
            transition: all 0.2s ease;
        }

        .logs-pagination .page-link:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }

        .logs-pagination .active .page-link {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-color: #3b82f6;
            color: white;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1rem 1.25rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .backup-card {
                padding: 1.5rem;
            }

            .logs-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .logs-table {
                font-size: 0.7rem;
            }

            .logs-table tbody td {
                padding: 0.75rem;
            }

            .message-text {
                max-width: 150px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // File upload handler
        document.getElementById('backup_file')?.addEventListener('change', function (e) {
            const fileName = e.target.files[0]?.name;
            const selectedDiv = document.getElementById('selectedFileName');
            const fileNameSpan = selectedDiv.querySelector('span');

            if (fileName) {
                fileNameSpan.textContent = fileName;
                selectedDiv.style.display = 'flex';
            } else {
                selectedDiv.style.display = 'none';
            }
        });

        // Confirm import function
        function confirmImport() {
            const fileInput = document.getElementById('backup_file');
            if (!fileInput.files.length) {
                alert('Please select a backup file first.');
                return false;
            }
            return confirm('⚠️ WARNING: This will replace your current database data!\n\nAre you sure you want to restore this backup?');
        }
    </script>
@endpush