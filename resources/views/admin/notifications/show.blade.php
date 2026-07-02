@extends('layouts.app')

@section('title', 'Notification Details')
@section('page-title', 'Notification Details')

@section('content')

    <div class="notification-details-page">

        {{-- HEADER --}}
        <div class="main-card">
            <div class="main-card-header">
                <div>
                    <h4>
                        <i class="bi bi-envelope-paper-fill text-primary me-2"></i>
                        Notification #{{ $notification->id }}
                    </h4>
                    <p>View full notification details and manage actions</p>
                </div>

                <div class="header-buttons">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary custom-btn">
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                    @if($notification->isPending())
                        <form action="{{ route('admin.notifications.cancel', $notification->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning custom-btn"
                                    onclick="return confirm('Cancel this notification?')">
                                <i class="bi bi-ban-fill"></i>
                                Cancel
                            </button>
                        </form>
                    @endif

                    @if($notification->wasFailed() && $notification->canRetry())
                        <form action="{{ route('admin.notifications.retry', $notification->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success custom-btn">
                                <i class="bi bi-arrow-repeat"></i>
                                Retry
                            </button>
                        </form>
                    @endif

                    @if(is_null($notification->read_at))
                        <form action="{{ route('admin.notifications.mark-read', $notification->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info custom-btn">
                                <i class="bi bi-check2-circle"></i>
                                Mark as Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ALERT --}}
            @if(session('success'))
                <div class="alert alert-success custom-alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="row g-4">
                {{-- LEFT COLUMN --}}
                <div class="col-lg-8">
                    <div class="detail-card">
                        <h5 class="detail-title">
                            <i class="bi bi-fonts text-primary me-2"></i>
                            Title
                        </h5>
                        <p class="detail-content">{{ $notification->title }}</p>
                    </div>

                    <div class="detail-card">
                        <h5 class="detail-title">
                            <i class="bi bi-textarea text-primary me-2"></i>
                            Body
                        </h5>
                        <div class="detail-content body-content">
                            {{ $notification->body }}
                        </div>
                    </div>

                    @if($notification->data)
                        <div class="detail-card">
                            <h5 class="detail-title">
                                <i class="bi bi-database text-primary me-2"></i>
                                Additional Data
                            </h5>
                            <pre class="detail-content code-block">{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endif
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="col-lg-4">
                    <div class="detail-sidebar">
                        <div class="detail-card">
                            <h5 class="detail-title">
                                <i class="bi bi-info-circle text-primary me-2"></i>
                                Details
                            </h5>

                            <table class="detail-table">
                                <tr>
                                    <td class="label">ID</td>
                                    <td class="value">#{{ $notification->id }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Student</td>
                                    <td class="value">
                                        <strong>{{ $notification->student->initial_name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $notification->student->custom_id ?? '' }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Type</td>
                                    <td class="value">
                                        <span class="badge custom-badge {{ $notification->type }}-badge">
                                            <i class="bi {{ $notification->type_icon }} me-1"></i>
                                            {{ $notification->type_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Status</td>
                                    <td class="value">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'sent' => 'success',
                                                'failed' => 'danger',
                                                'cancelled' => 'secondary'
                                            ];
                                            $statusIcons = [
                                                'pending' => 'bi-clock',
                                                'processing' => 'bi-arrow-repeat',
                                                'sent' => 'bi-check-circle',
                                                'failed' => 'bi-x-circle',
                                                'cancelled' => 'bi-ban'
                                            ];
                                        @endphp
                                        <span class="badge custom-badge status-badge bg-{{ $statusColors[$notification->status] ?? 'secondary' }}">
                                            <i class="bi {{ $statusIcons[$notification->status] ?? 'bi-circle' }} me-1"></i>
                                            {{ $notification->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label">Created By</td>
                                    <td class="value">{{ $notification->creator?->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Created At</td>
                                    <td class="value">{{ $notification->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Sent At</td>
                                    <td class="value">{{ $notification->sent_at?->format('Y-m-d H:i:s') ?? 'Not sent' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Read At</td>
                                    <td class="value">{{ $notification->read_at?->format('Y-m-d H:i:s') ?? 'Not read' }}</td>
                                </tr>
                                @if($notification->scheduled_at)
                                    <tr>
                                        <td class="label">Scheduled At</td>
                                        <td class="value">{{ $notification->scheduled_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @endif
                                @if($notification->error_message)
                                    <tr>
                                        <td class="label">Error</td>
                                        <td class="value text-danger">{{ $notification->error_message }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="label">Retry Count</td>
                                    <td class="value">{{ $notification->retry_count }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .notification-details-page {
            animation: fadeIn 0.4s ease;
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
            margin-bottom: 1.5rem;
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
            border: 1px solid #bbf7d0;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .detail-card {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
            border: 1px solid #eef2f7;
        }

        .detail-title {
            font-size: .85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #64748b;
            margin-bottom: .75rem;
        }

        .detail-content {
            font-size: 1rem;
            margin: 0;
            line-height: 1.7;
        }

        .body-content {
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            white-space: pre-wrap;
        }

        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 12px;
            font-size: .85rem;
            overflow-x: auto;
            white-space: pre-wrap;
        }

        .detail-sidebar .detail-card {
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .detail-table {
            width: 100%;
            font-size: .9rem;
        }

        .detail-table tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-table tr:last-child {
            border-bottom: none;
        }

        .detail-table td {
            padding: .6rem 0;
            vertical-align: top;
        }

        .detail-table .label {
            color: #64748b;
            font-weight: 500;
            padding-right: 1rem;
            min-width: 100px;
        }

        .detail-table .value {
            color: #1e293b;
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

        .general-badge { background: #e0e7ff; color: #4338ca; }
        .reminder-badge { background: #fef3c7; color: #d97706; }
        .exam-badge { background: #dbeafe; color: #2563eb; }
        .announcement-badge { background: #d1fae5; color: #065f46; }
        .attendance-badge { background: #ede9fe; color: #6d28d9; }
        .grade-badge { background: #fce4ec; color: #be185d; }
        .payment-badge { background: #d1fae5; color: #065f46; }
        .result-badge { background: #e0e7ff; color: #4338ca; }

        .status-badge {
            min-width: 80px;
            justify-content: center;
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
        }
    </style>
@endpush