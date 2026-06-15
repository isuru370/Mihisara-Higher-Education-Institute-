@extends('layouts.app')

@section('title', 'View System User')
@section('page-title', 'View System User')

@section('content')

<div class="system-user-view-page">

    <div class="row g-4 mb-4">

        <div class="col-lg-8">
            <div class="view-hero-card">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="hero-icon">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <h3>System User Details</h3>
                        <p>
                            View profile, contact, access, and status information for this user.
                        </p>
                    </div>

                    <div>
                        @if($systemUser->is_active)
                            <span class="hero-badge active">
                                <i class="bi bi-check-circle-fill"></i>
                                Active
                            </span>
                        @else
                            <span class="hero-badge inactive">
                                <i class="bi bi-pause-circle-fill"></i>
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="quick-summary-card">
                <h6 class="mb-3">Quick Summary</h6>

                <div class="summary-item">
                    <i class="bi bi-hash"></i>
                    <span>{{ $systemUser->custom_id }}</span>
                </div>

                <div class="summary-item">
                    <i class="bi bi-person"></i>
                    <span>{{ $systemUser->full_name }}</span>
                </div>

                <div class="summary-item">
                    <i class="bi bi-envelope"></i>
                    <span>{{ $systemUser->user?->email ?? 'No email linked' }}</span>
                </div>

                <div class="summary-item">
                    <i class="bi bi-telephone"></i>
                    <span>{{ $systemUser->mobile }}</span>
                </div>
            </div>
        </div>

    </div>

    <div class="main-view-card">

        <div class="main-view-header">
            <div>
                <h4>User Profile</h4>
                <p>Detailed information of the selected system user</p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.system-users.edit', $systemUser) }}" class="btn btn-warning custom-btn">
                    <i class="bi bi-pencil-square"></i>
                    Edit User
                </a>

                <a href="{{ route('admin.system-users.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back
                </a>
            </div>
        </div>

        <div class="info-grid">

            <div class="info-box">
                <span class="info-label">Custom ID</span>
                <div class="info-value">{{ $systemUser->custom_id }}</div>
            </div>

            <div class="info-box">
                <span class="info-label">Full Name</span>
                <div class="info-value">{{ $systemUser->full_name }}</div>
            </div>

            <div class="info-box">
                <span class="info-label">Mobile</span>
                <div class="info-value">{{ $systemUser->mobile }}</div>
            </div>

            <div class="info-box">
                <span class="info-label">NIC</span>
                <div class="info-value">{{ $systemUser->nic }}</div>
            </div>

            <div class="info-box">
                <span class="info-label">Birthday</span>
                <div class="info-value">
                    {{ optional($systemUser->bday)->format('Y-m-d') ?? 'N/A' }}
                </div>
            </div>

            <div class="info-box">
                <span class="info-label">Gender</span>
                <div class="info-value">
                    {{ ucfirst($systemUser->gender ?? 'N/A') }}
                </div>
            </div>

            <div class="info-box">
                <span class="info-label">Linked User</span>
                <div class="info-value">
                    {{ $systemUser->user?->name ?? 'Not linked' }}
                </div>
            </div>

            <div class="info-box">
                <span class="info-label">Email</span>
                <div class="info-value">
                    {{ $systemUser->user?->email ?? 'Not available' }}
                </div>
            </div>

            <div class="info-box full-width">
                <span class="info-label">Address</span>
                <div class="info-value">
                    {{ trim(($systemUser->address1 ?? '') . ' ' . ($systemUser->address2 ?? '') . ' ' . ($systemUser->address3 ?? '')) ?: 'N/A' }}
                </div>
            </div>

            <div class="info-box full-width">
                <span class="info-label">Note</span>
                <div class="info-value note-box">
                    {{ $systemUser->note ?: 'No note available' }}
                </div>
            </div>

        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    .system-user-view-page {
        animation: fadeIn 0.4s ease;
    }

    .view-hero-card,
    .quick-summary-card,
    .main-view-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 28px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .view-hero-card {
        padding: 1.5rem;
        min-height: 100%;
    }

    .hero-icon {
        width: 58px;
        height: 58px;
        border-radius: 18px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        margin-bottom: 1rem;
    }

    .view-hero-card h3 {
        margin: 0 0 .5rem 0;
        font-weight: 800;
    }

    .view-hero-card p {
        margin: 0;
        color: #64748b;
    }

    .hero-badge {
        padding: .6rem .95rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: .82rem;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        white-space: nowrap;
    }

    .hero-badge.active {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #bbf7d0;
    }

    .hero-badge.inactive {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .quick-summary-card {
        padding: 1.5rem;
        height: 100%;
    }

    .quick-summary-card h6 {
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .8rem 0;
        border-bottom: 1px solid #f1f5f9;
        color: #475569;
    }

    .summary-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .summary-item i {
        color: #2563eb;
        font-size: 1.05rem;
        flex-shrink: 0;
    }

    .main-view-card {
        padding: 1.5rem;
    }

    .main-view-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }

    .main-view-header h4 {
        margin: 0;
        font-weight: 800;
    }

    .main-view-header p {
        margin: .25rem 0 0 0;
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
        font-weight: 700;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .info-box {
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 20px;
        padding: 1rem 1.1rem;
    }

    .info-box.full-width {
        grid-column: 1 / -1;
    }

    .info-label {
        display: block;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        margin-bottom: .45rem;
    }

    .info-value {
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
    }

    .note-box {
        white-space: pre-wrap;
    }

    @media (max-width: 768px) {
        .view-hero-card,
        .quick-summary-card,
        .main-view-card {
            border-radius: 22px;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .main-view-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-buttons {
            width: 100%;
        }

        .header-buttons a {
            flex: 1;
            justify-content: center;
        }
    }
</style>
@endpush