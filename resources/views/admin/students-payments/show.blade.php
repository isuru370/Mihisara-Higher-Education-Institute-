@extends('layouts.app')

@section('title', 'Payment Details - ' . config('app.name', 'EDU NEXORA'))
@section('page-title', 'Payment Details')

@section('content')

    <div class="payment-details-page">
        <div class="row g-4">
            {{-- Payment Details Card --}}
            <div class="col-lg-8">
                <div class="details-card">
                    <div class="details-header">
                        <div class="header-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div>
                            <h4>Payment Information</h4>
                            <p>Complete payment transaction details</p>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('admin.receipts.index') }}" class="btn-back">
                                <i class="bi bi-arrow-left"></i> Back to Receipts
                            </a>
                        </div>
                    </div>

                    <div class="details-body">
                        <div class="info-grid">
                            {{-- Receipt Number --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-receipt-cutoff"></i> Receipt Number
                                </div>
                                <div class="info-value highlight">
                                    {{ $payment->receipt_number ?? 'N/A' }}
                                </div>
                            </div>

                            {{-- Amount --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-cash-stack"></i> Amount
                                </div>
                                <div class="info-value amount-value">
                                    Rs. {{ number_format($payment->amount, 2) }}
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-qr-code"></i> Payment Method
                                </div>
                                <div class="info-value">
                                    @php
                                        $methodColors = [
                                            'qr' => 'method-qr',
                                            'manual' => 'method-manual',
                                        ];
                                        $methodClass = $methodColors[$payment->mark_method] ?? 'method-default';
                                    @endphp
                                    <span class="method-badge {{ $methodClass }}">
                                        <i class="bi bi-{{ $payment->mark_method == 'qr' ? 'qr-code' : 'pencil' }}"></i>
                                        {{ ucfirst($payment->mark_method) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Date & Time --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-calendar-clock"></i> Date & Time
                                </div>
                                <div class="info-value">
                                    {{ $payment->created_at->format('Y-m-d') }}
                                    <span class="time">{{ $payment->created_at->format('h:i A') }}</span>
                                </div>
                            </div>

                            {{-- Collected By --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-person-check"></i> Collected By
                                </div>
                                <div class="info-value">
                                    <i class="bi bi-person-circle"></i>
                                    {{ $payment->collectedBy->name ?? 'System' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Student Details Card --}}
                <div class="details-card mt-4">
                    <div class="details-header">
                        <div class="header-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h4>Student Information</h4>
                            <p>Student personal and academic details</p>
                        </div>
                    </div>

                    <div class="details-body">
                        <div class="info-grid">
                            {{-- Student Name --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-person"></i> Student Name
                                </div>
                                <div class="info-value">
                                    {{ $payment->student->initial_name ?? 'N/A' }}
                                </div>
                            </div>

                            {{-- Student ID --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-qr-code"></i> Student ID
                                </div>
                                <div class="info-value">
                                    {{ $payment->student->custom_id ?? 'N/A' }}
                                </div>
                            </div>

                            {{-- Temporary QR --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-qr-code-scan"></i> Temporary QR
                                </div>
                                <div class="info-value">
                                    @if($payment->student->temporary_qr_code)
                                        <span class="badge-active"><i class="bi bi-check-circle"></i> Active</span>
                                    @else
                                        <span class="badge-inactive"><i class="bi bi-x-circle"></i> Inactive</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Permanent QR Active --}}
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-shield-check"></i> Permanent QR
                                </div>
                                <div class="info-value">
                                    @if($payment->student->permanent_qr_active)
                                        <span class="badge-active"><i class="bi bi-check-circle"></i> Active</span>
                                    @else
                                        <span class="badge-inactive"><i class="bi bi-x-circle"></i> Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <style>
        .payment-details-page {
            animation: fadeInUp 0.4s ease;
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

        /* Details Card */
        .details-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .details-header {
            padding: 1.2rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-icon i {
            font-size: 1.2rem;
            color: white;
        }

        .details-header h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }

        .details-header p {
            margin: 0;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 2px;
        }

        .header-actions {
            margin-left: auto;
        }

        .btn-back {
            background: #f1f5f9;
            color: #475569;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
            color: #475569;
        }

        .details-body {
            padding: 1.5rem;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 1rem;
        }

        .info-label {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #1e293b;
        }

        .info-value.highlight {
            font-size: 1.2rem;
            font-weight: 700;
            color: #4f46e5;
        }

        .info-value.amount-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: #10b981;
        }

        .info-value .time {
            font-size: 0.8rem;
            color: #64748b;
            margin-left: 0.5rem;
        }

        /* Method Badge */
        .method-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .method-qr {
            background: #e0e7ff;
            color: #4338ca;
        }

        .method-manual {
            background: #fef3c7;
            color: #d97706;
        }

        .method-default {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Badges */
        .badge-active {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: #d1fae5;
            color: #059669;
            padding: 0.25rem 0.7rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-inactive {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: #fee2e2;
            color: #dc2626;
            padding: 0.25rem 0.7rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }



        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #64748b;
        }

        .detail-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
        }

        .detail-value.category {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: #f3e8ff;
            color: #6b21a5;
            padding: 0.2rem 0.6rem;
            border-radius: 6px;
        }

        .detail-value.amount {
            color: #10b981;
            font-size: 1rem;
        }

        /* Action Links */
        .action-buttons-vertical {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .action-link {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.7rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .action-link.print {
            background: #fef3c7;
            color: #d97706;
        }

        .action-link.print:hover {
            background: #d97706;
            color: white;
            transform: translateY(-2px);
        }

        .action-link.download {
            background: #d1fae5;
            color: #059669;
        }

        .action-link.download:hover {
            background: #059669;
            color: white;
            transform: translateY(-2px);
        }

        .action-link.back {
            background: #f1f5f9;
            color: #475569;
        }

        .action-link.back:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        /* QR Code */
        .qr-image {
            width: 150px;
            height: 150px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }

        .qr-label {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.5rem;
            text-align: center;
        }

        .qr-placeholder {
            text-align: center;
            padding: 1rem;
        }

        .qr-placeholder i {
            font-size: 3rem;
            color: #10b981;
        }

        .qr-placeholder p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.5rem;
        }

        /* Print Styles */
        @media print {

            .navbar,
            .mobile-menu,
            .sidebar-card .action-link,
            .btn-back,
            .header-actions,
            .filter-card {
                display: none !important;
            }

            .details-card,
            .sidebar-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .payment-details-page {
                padding: 0;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .details-header {
                flex-wrap: wrap;
            }

            .header-actions {
                margin-left: 0;
                width: 100%;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush