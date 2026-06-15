{{-- resources/views/admin/system-users/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit System User')
@section('page-title', 'Edit System User')

@section('content')
    @php
        $isEdit = true;
    @endphp

    <div class="system-user-form-page">

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="form-hero-card">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="hero-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <h3>Edit System User</h3>
                            <p>
                                Update profile, contact, login, and status details for this user.
                            </p>
                        </div>

                        <div class="hero-badge edit">
                            Editing
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="quick-summary-card">
                    <h6 class="mb-3">Current User</h6>
                    <div class="summary-item">
                        <i class="bi bi-person-badge"></i>
                        <span>{{ $systemUser->full_name }}</span>
                    </div>
                    <div class="summary-item">
                        <i class="bi bi-envelope"></i>
                        <span>{{ $systemUser->user?->email ?? 'No email' }}</span>
                    </div>
                    <div class="summary-item">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ $systemUser->is_active ? 'Active user' : 'Inactive user' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-form-card">

            <div class="main-form-header">
                <div>
                    <h4>User Details</h4>
                    <p>Update the fields below and save the changes</p>
                </div>
            </div>

            <form action="{{ route('admin.system-users.update', $systemUser) }}" method="POST">
                @csrf
                @method('PUT')

                @include('admin.system-users.partials.form', ['systemUser' => $systemUser])

                <div class="form-footer">
                    <div class="d-flex gap-2 ms-auto">
                        <a href="{{ route('admin.system-users.index') }}" class="btn btn-light border custom-btn">
                            <i class="bi bi-arrow-left"></i>
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary custom-btn">
                            <i class="bi bi-check2-circle"></i>
                            {{ isset($systemUser) ? 'Update User' : 'Save User' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .system-user-form-page {
            animation: fadeIn 0.4s ease;
        }

        .form-hero-card,
        .quick-summary-card,
        .main-form-card {
            background: #fff;
            border: 1px solid #eef2f7;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .form-hero-card {
            padding: 1.5rem;
            min-height: 100%;
        }

        .hero-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1rem;
        }

        .form-hero-card h3 {
            margin: 0 0 .5rem 0;
            font-weight: 800;
        }

        .form-hero-card p {
            margin: 0;
            color: #64748b;
        }

        .hero-badge {
            padding: .55rem .9rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .82rem;
            white-space: nowrap;
        }

        .hero-badge.edit {
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
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
            padding: .75rem 0;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }

        .summary-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .summary-item i {
            color: #f59e0b;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .main-form-card {
            padding: 1.5rem;
        }

        .main-form-header {
            margin-bottom: 1.25rem;
        }

        .main-form-header h4 {
            margin: 0;
            font-weight: 800;
        }

        .main-form-header p {
            margin: .25rem 0 0 0;
            color: #64748b;
        }

        .form-section {
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 22px;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: .9rem;
            font-weight: 800;
            color: #334155;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
        }

        .soft-input,
        .soft-select,
        .soft-textarea {
            border-radius: 14px !important;
            border: 1px solid #dbe4ef !important;
            min-height: 48px;
            box-shadow: none !important;
            background: #fff;
        }

        .soft-textarea {
            min-height: 110px;
            resize: vertical;
        }

        .soft-input:focus,
        .soft-select:focus,
        .soft-textarea:focus {
            border-color: #93c5fd !important;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .08) !important;
        }

        .readonly-box {
            background: #f1f5f9;
            color: #64748b;
        }

        .form-footer {
            margin-top: 1.25rem;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .72rem 1.2rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        @media (max-width: 768px) {

            .main-form-card,
            .form-hero-card,
            .quick-summary-card {
                border-radius: 22px;
            }

            .form-footer {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .form-footer a,
            .form-footer button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush