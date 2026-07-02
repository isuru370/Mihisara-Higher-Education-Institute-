@extends('layouts.app')

@section('title', 'FCM Token Details')
@section('page-title', 'FCM Token Details')

@section('content')

<div class="fcm-token-details-page">

    {{-- MAIN CARD --}}
    <div class="main-card">

        {{-- HEADER --}}
        <div class="main-card-header">
            <div>
                <h4>
                    <i class="bi bi-phone text-primary me-2"></i>
                    FCM Token Details
                </h4>
                <p>View device token information and manage status</p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.fcm-tokens.index') }}" class="btn btn-secondary custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back to List
                </a>

                @if($token->is_active)
                    <form action="{{ route('admin.fcm-tokens.deactivate', $token->id) }}"
                          method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning custom-btn"
                                onclick="return confirm('Deactivate this token?')">
                            <i class="bi bi-pause-circle-fill"></i>
                            Deactivate
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.fcm-tokens.activate', $token->id) }}"
                          method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success custom-btn"
                                onclick="return confirm('Activate this token?')">
                            <i class="bi bi-play-circle-fill"></i>
                            Activate
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.fcm-tokens.destroy', $token->id) }}"
                      method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger custom-btn"
                            onclick="return confirm('Delete this token permanently?')">
                        <i class="bi bi-trash-fill"></i>
                        Delete
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

        <div class="row g-4">

            {{-- LEFT COLUMN --}}
            <div class="col-lg-8">
                <div class="detail-card">
                    <h5 class="detail-title">
                        <i class="bi bi-key text-primary me-2"></i>
                        Token
                    </h5>
                    <div class="detail-content">
                        <code class="token-full">{{ $token->token }}</code>
                        <br>
                        <button class="btn btn-sm btn-primary mt-2 copy-btn"
                                data-token="{{ $token->token }}"
                                onclick="copyToken(this)">
                            <i class="bi bi-clipboard"></i> Copy Full Token
                        </button>
                    </div>
                </div>

                <div class="detail-card">
                    <h5 class="detail-title">
                        <i class="bi bi-person-fill text-primary me-2"></i>
                        Student Information
                    </h5>
                    <div class="detail-content">
                        <div class="student-info">
                            <div class="student-avatar-lg" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                {{ strtoupper(substr($token->student->initial_name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="student-details">
                                <h5 class="mb-1">{{ $token->student->initial_name ?? 'Unknown Student' }}</h5>
                                <p class="text-muted mb-1">
                                    <i class="bi bi-person-badge me-1"></i>
                                    ID: {{ $token->student->custom_id ?? 'N/A' }}
                                </p>
                                <a href="{{ route('admin.students.show', $token->student_id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View Student
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
                                <td class="value">#{{ $token->id }}</td>
                            </tr>
                            <tr>
                                <td class="label">Device Type</td>
                                <td class="value">
                                    <i class="bi {{ $token->device_icon }} me-1"></i>
                                    {{ $token->device_type_label }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Device Name</td>
                                <td class="value">{{ $token->device_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">App Version</td>
                                <td class="value">{{ $token->app_version ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Status</td>
                                <td class="value">
                                    <span class="badge custom-badge bg-{{ $token->status_color }}">
                                        <i class="bi {{ $token->is_active ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                        {{ $token->status_label }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Last Login</td>
                                <td class="value">
                                    {{ $token->last_login_at?->format('Y-m-d H:i:s') ?? 'Never' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Created At</td>
                                <td class="value">{{ $token->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td class="label">Updated At</td>
                                <td class="value">{{ $token->updated_at->format('Y-m-d H:i:s') }}</td>
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
        .fcm-token-details-page {
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
            text-decoration: none;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .btn-secondary.custom-btn {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary.custom-btn:hover {
            background: #e2e8f0;
        }

        .btn-warning.custom-btn {
            background: #f59e0b;
            color: #fff;
        }

        .btn-success.custom-btn {
            background: #10b981;
            color: #fff;
        }

        .btn-danger.custom-btn {
            background: #ef4444;
            color: #fff;
        }

        .custom-alert {
            border-radius: 16px;
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

        .token-full {
            background: #1e293b;
            color: #e2e8f0;
            padding: 0.75rem;
            border-radius: 8px;
            display: block;
            font-size: .85rem;
            word-break: break-all;
            overflow-wrap: break-word;
        }

        .copy-btn {
            margin-top: 0.5rem;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .student-avatar-lg {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .student-details {
            flex: 1;
        }

        .student-details h5 {
            margin: 0;
            font-weight: 700;
        }

        .student-details p {
            margin: 0;
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

        .bg-success {
            background-color: #10b981 !important;
            color: #fff;
        }

        .bg-danger {
            background-color: #ef4444 !important;
            color: #fff;
        }

        .bg-warning {
            background-color: #f59e0b !important;
            color: #fff;
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
                font-size: .8rem;
                padding: .5rem .8rem;
            }

            .student-info {
                flex-direction: column;
                text-align: center;
            }

            .detail-table td {
                padding: .4rem 0;
            }

            .detail-table .label {
                min-width: 80px;
                font-size: .85rem;
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