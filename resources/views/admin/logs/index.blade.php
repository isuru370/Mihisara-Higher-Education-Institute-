@extends('layouts.app')

@section('title', 'Laravel Logs')
@section('page-title', 'Laravel Logs')

@section('content')

    <div class="logs-page">

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

        @if(isset($error))
            <div class="alert alert-warning alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Stats Cards --}}
        <div class="row g-4 mb-4" id="logStats">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div>
                        <h3 id="fileSize">-</h3>
                        <p>File Size</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon purple">
                        <i class="bi bi-list-ol"></i>
                    </div>
                    <div>
                        <h3 id="totalLines">-</h3>
                        <p>Total Lines</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon red">
                        <i class="bi bi-bug"></i>
                    </div>
                    <div>
                        <h3 id="errorCount">-</h3>
                        <p>Errors</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h3 id="warningCount">-</h3>
                        <p>Warnings</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="logs-card">
            <div class="logs-card-header">
                <div>
                    <h4>
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Laravel Application Logs
                    </h4>
                    <p>System errors, exceptions and warnings</p>
                </div>
                <div class="header-actions">
                    <div class="search-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="text" id="logSearch" placeholder="Search logs..." class="search-input">
                    </div>
                    <button class="btn btn-success" id="downloadBtn">
                        <i class="bi bi-download me-1"></i> Download
                    </button>
                    <button class="btn btn-danger" id="clearBtn" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                        <i class="bi bi-trash me-1"></i> Clear Logs
                    </button>
                </div>
            </div>

            <div class="logs-card-body">
                {{-- Terminal Style --}}
                <div class="terminal-container">
                    <div class="terminal-header">
                        <span class="dot red"></span>
                        <span class="dot yellow"></span>
                        <span class="dot green"></span>
                        <span class="terminal-title">laravel.log</span>
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="all">All</button>
                            <button class="filter-btn" data-filter="error">Errors</button>
                            <button class="filter-btn" data-filter="warning">Warnings</button>
                            <button class="filter-btn" data-filter="exception">Exceptions</button>
                        </div>
                    </div>
                    <pre class="terminal-body" id="logContent">{{ $content }}</pre>
                </div>
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
                        Clear Logs
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to clear all Laravel logs?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-1"></i>
                        This action cannot be undone. The log file will be completely emptied.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.logs.laravel.clear') }}" method="POST" id="clearForm">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Yes, Clear Logs
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .logs-page {
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

        .stats-icon.purple {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .stats-icon.red {
            background: linear-gradient(135deg, #ef4444, #f87171);
        }

        .stats-icon.orange {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
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
            align-items: center;
            flex-wrap: wrap;
        }

        .search-wrapper {
            position: relative;
        }

        .search-wrapper i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #64748b;
        }

        .search-input {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            min-width: 250px;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-success,
        .btn-danger {
            border-radius: 12px;
            padding: 0.5rem 1.2rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-success:hover,
        .btn-danger:hover {
            transform: translateY(-2px);
        }

        /* Terminal Styles */
        .terminal-container {
            background: #0a0c10;
        }

        .terminal-header {
            background: #1a1d24;
            padding: 12px 20px;
            border-bottom: 1px solid #2d313a;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .red {
            background: #ff5f56;
        }

        .yellow {
            background: #ffbd2e;
        }

        .green {
            background: #27c93f;
        }

        .terminal-title {
            color: #8b949e;
            font-size: 0.8rem;
            font-family: monospace;
            margin-left: 0.5rem;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            margin-left: auto;
        }

        .filter-btn {
            background: transparent;
            border: 1px solid #2d313a;
            color: #8b949e;
            padding: 0.3rem 0.8rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            background: #2d313a;
            color: #ffffff;
        }

        .filter-btn.active {
            background: #4f46e5;
            border-color: #4f46e5;
            color: white;
        }

        .terminal-body {
            margin: 0;
            padding: 20px;
            background: #0a0c10;
            color: #e6edf3;
            font-size: 12px;
            line-height: 1.6;
            font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
            max-height: 600px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Log Line Colors */
        .log-line {
            display: block;
            padding: 4px 0;
            border-left: 3px solid transparent;
            padding-left: 10px;
            margin-bottom: 2px;
            font-family: monospace;
            font-size: 11px;
        }

        .log-line:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .log-error {
            color: #f85149;
            border-left-color: #f85149;
            background: rgba(248, 81, 73, 0.05);
        }

        .log-warning {
            color: #d29922;
            border-left-color: #d29922;
            background: rgba(210, 153, 34, 0.05);
        }

        .log-exception {
            color: #be8fff;
            border-left-color: #be8fff;
            background: rgba(190, 143, 255, 0.05);
        }

        .log-info {
            color: #79c0ff;
            border-left-color: #79c0ff;
        }

        .log-debug {
            color: #8b949e;
            border-left-color: #8b949e;
        }

        .log-default {
            color: #e6edf3;
        }

        /* Custom Scrollbar */
        .terminal-body::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .terminal-body::-webkit-scrollbar-track {
            background: #1a1d24;
        }

        .terminal-body::-webkit-scrollbar-thumb {
            background: #2d313a;
            border-radius: 4px;
        }

        .terminal-body::-webkit-scrollbar-thumb:hover {
            background: #3d4250;
        }

        /* Modal */
        .modal-content {
            border-radius: 24px;
            border: none;
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
                flex-direction: column;
                align-items: stretch;
                padding: 1rem;
            }

            .header-actions {
                flex-direction: column;
            }

            .search-input {
                width: 100%;
            }

            .filter-buttons {
                margin-left: 0;
                width: 100%;
                justify-content: center;
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
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logContent = document.getElementById('logContent');
            const searchInput = document.getElementById('logSearch');
            const downloadBtn = document.getElementById('downloadBtn');
            let currentFilter = 'all';

            // Original log content as HTML with colored lines
            let originalHtml = '';

            // Parse log lines and add colors
            function parseLogLines(content) {
                if (!content || content === '') {
                    return '<div class="text-muted text-center py-5">No log entries found. The log file is empty.</div>';
                }

                const lines = content.split('\n');
                let html = '';

                for (let line of lines) {
                    if (line.trim() === '') continue;

                    let logClass = 'log-default';
                    const lowerLine = line.toLowerCase();

                    if (lowerLine.includes('[error]') || lowerLine.includes('error')) {
                        logClass = 'log-error';
                    } else if (lowerLine.includes('[warning]') || lowerLine.includes('warning')) {
                        logClass = 'log-warning';
                    } else if (lowerLine.includes('exception')) {
                        logClass = 'log-exception';
                    } else if (lowerLine.includes('[info]')) {
                        logClass = 'log-info';
                    } else if (lowerLine.includes('[debug]')) {
                        logClass = 'log-debug';
                    }

                    // Escape HTML special characters
                    let escapedLine = line
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');

                    // Highlight timestamps
                    escapedLine = escapedLine.replace(/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/, '<span style="color: #58a6ff;">$1</span>');

                    html += `<div class="log-line ${logClass}" data-raw="${encodeURIComponent(line)}">${escapedLine}</div>`;
                }

                return html;
            }

            // Filter logs based on search and type
            function filterLogs() {
                if (!originalHtml) return;

                const searchTerm = searchInput.value.toLowerCase();
                const lines = document.querySelectorAll('.log-line');

                lines.forEach(line => {
                    const rawLine = decodeURIComponent(line.getAttribute('data-raw') || '').toLowerCase();
                    const logClass = line.className;

                    let showByType = true;
                    if (currentFilter === 'error') {
                        showByType = logClass.includes('log-error') || logClass.includes('log-exception');
                    } else if (currentFilter === 'warning') {
                        showByType = logClass.includes('log-warning');
                    } else if (currentFilter === 'exception') {
                        showByType = logClass.includes('log-exception');
                    }

                    let showBySearch = true;
                    if (searchTerm) {
                        showBySearch = rawLine.includes(searchTerm);
                    }

                    line.style.display = (showByType && showBySearch) ? 'block' : 'none';
                });
            }

            // Initialize log display
            if (logContent) {
                const rawContent = logContent.textContent || '';
                originalHtml = parseLogLines(rawContent);
                logContent.innerHTML = originalHtml;
            }

            // Search functionality
            if (searchInput) {
                searchInput.addEventListener('input', filterLogs);
            }

            // Filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.getAttribute('data-filter');
                    filterLogs();
                });
            });

            // Download button
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    window.location.href = '{{ route("admin.logs.laravel.download") }}';
                });
            }

            // Load stats via AJAX
            function loadStats() {
                fetch('{{ route("admin.logs.laravel.stats") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.size !== undefined) {
                            document.getElementById('fileSize').textContent = data.size + ' KB';
                            document.getElementById('totalLines').textContent = data.lines.toLocaleString();
                            document.getElementById('errorCount').textContent = data.errors.toLocaleString();
                            document.getElementById('warningCount').textContent = data.warnings.toLocaleString();
                        }
                    })
                    .catch(error => console.error('Error loading stats:', error));
            }

            // Load stats every 30 seconds
            loadStats();
            setInterval(loadStats, 30000);

            // Auto-refresh log content every minute (optional)
            let autoRefresh = true;
            let refreshInterval;

            function startAutoRefresh() {
                if (refreshInterval) clearInterval(refreshInterval);
                refreshInterval = setInterval(function () {
                    if (autoRefresh && !document.hidden) {
                        fetch(window.location.href)
                            .then(response => response.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newContent = doc.getElementById('logContent')?.textContent;
                                if (newContent && newContent !== logContent?.textContent) {
                                    originalHtml = parseLogLines(newContent);
                                    if (logContent) logContent.innerHTML = originalHtml;
                                    filterLogs();
                                    loadStats();
                                }
                            })
                            .catch(error => console.error('Error refreshing logs:', error));
                    }
                }, 30000);
            }

            // Start auto-refresh
            startAutoRefresh();

            // Stop auto-refresh when page is hidden
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    autoRefresh = false;
                } else {
                    autoRefresh = true;
                    loadStats();
                }
            });
        });
    </script>
@endpush