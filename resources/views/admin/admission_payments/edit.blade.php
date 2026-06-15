@extends('layouts.app')

@section('title', 'Edit Admission Payment')
@section('page-title', 'Edit Admission Payment')

@section('content')

@php
    $currentStatus = $admissionPayment->status ?? 'pending';
@endphp

<div class="admission-payment-page">

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="page-hero-card">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <div>
                        <div class="hero-icon edit">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h3>Edit Admission Payment</h3>
                        <p>Update payment details, status, and note information.</p>
                    </div>

                    <div class="hero-badge edit">
                        Editing
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="quick-summary-card">
                <h6 class="mb-3">Current Payment</h6>

                <div class="summary-item">
                    <i class="bi bi-receipt"></i>
                    <span>{{ $admissionPayment->receipt_number ?? 'N/A' }}</span>
                </div>

                <div class="summary-item">
                    <i class="bi bi-person"></i>
                    <span>{{ $admissionPayment->student->initial_name ?? '-' }}</span>
                </div>

                <div class="summary-item">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ ucfirst($currentStatus) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="main-card">
        <div class="main-card-header">
            <div>
                <h4>Payment Details</h4>
                <p>Update the selected payment and save the changes</p>
            </div>
        </div>

        <form action="{{ route('admin.admission-payments.update', $admissionPayment) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.admission_payments.partials.form', ['admissionPayment' => $admissionPayment])

            <div class="form-footer">
                <div class="btn-group-inline">
                    <a href="{{ route('admin.admission-payments.index') }}" class="btn btn-light border custom-btn">
                        <i class="bi bi-arrow-left"></i>
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-warning custom-btn">
                        <i class="bi bi-check2-circle"></i>
                        Update Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    .admission-payment-page {
        animation: fadeIn 0.4s ease;
    }

    .page-hero-card,
    .quick-summary-card,
    .main-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 28px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .page-hero-card {
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

    .hero-icon.edit {
        background: linear-gradient(135deg, #f59e0b, #ef4444);
    }

    .page-hero-card h3 {
        margin: 0 0 .5rem 0;
        font-weight: 800;
    }

    .page-hero-card p {
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

    .main-card {
        padding: 1.5rem;
    }

    .main-card-header {
        margin-bottom: 1.25rem;
    }

    .main-card-header h4 {
        margin: 0;
        font-weight: 800;
    }

    .main-card-header p {
        margin: .25rem 0 0 0;
        color: #64748b;
    }

    .form-footer {
        margin-top: 1.5rem;
        display: flex;
        justify-content: flex-end;
    }

    .btn-group-inline {
        display: flex;
        gap: .75rem;
    }

    .custom-btn {
        border-radius: 14px;
        padding: .8rem 1.4rem;
        font-weight: 700;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
    }

    @media (max-width: 768px) {
        .page-hero-card,
        .quick-summary-card,
        .main-card {
            border-radius: 22px;
        }

        .btn-group-inline {
            width: 100%;
            flex-direction: column;
        }

        .btn-group-inline .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush