@extends('layouts.app')

@section('title', 'Admission Details')
@section('page-title', 'Admission Details')

@push('styles')
    <style>
        .admission-details-page {
            animation: fadeIn .4s ease;
        }

        /* Hero Card */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            padding: 2rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8, #1e40af);
            box-shadow: 0 20px 45px rgba(37, 99, 235, .25);
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            top: -100px;
            right: -80px;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            bottom: -60px;
            left: -40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(10px);
            font-size: .85rem;
            font-weight: 600;
        }

        .text-light-soft {
            color: rgba(255, 255, 255, .78);
        }

        /* Details Card */
        .details-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
            transition: all .25s ease;
        }

        .details-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px rgba(15, 23, 42, .1);
        }

        .details-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .details-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .details-title i {
            color: #2563eb;
            font-size: 1.2rem;
        }

        .btn-back {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back:hover {
            background: #475569;
            transform: translateY(-2px);
            color: white;
        }

        .btn-edit {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
            color: white;
        }

        /* Details Body */
        .details-body {
            padding: 1.5rem;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid #eef2f7;
            transition: all 0.2s ease;
        }

        .info-card:hover {
            background: #fff;
            border-color: #2563eb;
            transform: translateY(-2px);
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label i {
            color: #2563eb;
            font-size: 0.9rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        .info-value.amount {
            font-size: 1.3rem;
            color: #10b981;
            font-family: monospace;
        }

        /* Badges */
        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Note Section */
        .note-section {
            margin-top: 1.5rem;
            background: #fef3c7;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            border-left: 4px solid #f59e0b;
        }

        .note-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .note-text {
            font-size: 0.9rem;
            color: #78350f;
            line-height: 1.5;
        }

        /* Action Buttons */
        .action-buttons {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        /* Timeline */
        .timeline {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eef2f7;
        }

        .timeline-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timeline-item {
            display: flex;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .timeline-icon {
            width: 32px;
            height: 32px;
            background: #eff6ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-event {
            font-weight: 600;
            color: #0f172a;
            font-size: 0.85rem;
        }

        .timeline-date {
            font-size: 0.7rem;
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .details-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-back,
            .btn-edit {
                width: 100%;
                justify-content: center;
            }

            .action-buttons {
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="admission-details-page">

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="hero-badge mb-3">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        Admission Information
                    </div>
                    <h2 class="fw-bold mb-2">{{ $admission->name }}</h2>
                    <p class="mb-0 text-light-soft">
                        Complete details of the admission fee structure
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-calendar-event-fill"></i>
                        {{ now()->format('d M Y') }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAILS CARD --}}
        <div class="details-card">
            <div class="details-header">
                <h5 class="details-title">
                    <i class="bi bi-info-circle-fill"></i>
                    Admission Details
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.admissions.index') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.admissions.edit', $admission) }}" class="btn-edit">
                        <i class="bi bi-pencil-square"></i> Edit Admission
                    </a>
                </div>
            </div>

            <div class="details-body">
                {{-- INFO GRID --}}
                <div class="info-grid">
                    {{-- Admission Name --}}
                    <div class="info-card">
                        <div class="info-label">
                            <i class="bi bi-tag-fill"></i>
                            Admission Name
                        </div>
                        <div class="info-value">{{ $admission->name }}</div>
                    </div>

                    {{-- Amount --}}
                    <div class="info-card">
                        <div class="info-label">
                            <i class="bi bi-cash-stack"></i>
                            Amount
                        </div>
                        <div class="info-value amount">
                            Rs. {{ number_format($admission->amount, 2) }}
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="info-card">
                        <div class="info-label">
                            <i class="bi bi-flag-fill"></i>
                            Status
                        </div>
                        <div class="info-value">
                            @if($admission->is_active)
                                <span class="badge-active">
                                    <i class="bi bi-check-circle-fill"></i> Active
                                </span>
                            @else
                                <span class="badge-inactive">
                                    <i class="bi bi-x-circle-fill"></i> Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Created By --}}
                    <div class="info-card">
                        <div class="info-label">
                            <i class="bi bi-person-badge-fill"></i>
                            Created By
                        </div>
                        <div class="info-value">
                            {{ $admission->createdBy->name ?? 'System' }}
                        </div>
                    </div>
                </div>

                {{-- NOTE SECTION --}}
                @if($admission->note)
                    <div class="note-section">
                        <div class="note-label">
                            <i class="bi bi-chat-text-fill"></i>
                            Additional Note
                        </div>
                        <div class="note-text">{{ $admission->note }}</div>
                    </div>
                @endif

                {{-- TIMELINE --}}
                <div class="timeline">
                    <div class="timeline-title">
                        <i class="bi bi-clock-history"></i>
                        Record Timeline
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            <i class="bi bi-plus-circle-fill"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-event">Admission Created</div>
                            <div class="timeline-date">
                                {{ $admission->created_at->format('l, d F Y h:i A') }}
                            </div>
                        </div>
                    </div>
                    @if($admission->created_at != $admission->updated_at)
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-event">Last Updated</div>
                                <div class="timeline-date">
                                    {{ $admission->updated_at->format('l, d F Y h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="action-buttons">
                    <a href="{{ route('admin.admissions.index') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.admissions.edit', $admission) }}" class="btn-edit">
                        <i class="bi bi-pencil-square"></i> Edit Admission
                    </a>
                </div>
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                Admission ID: #{{ $admission->id }} | Last modified: {{ $admission->updated_at->diffForHumans() }}
            </small>
        </div>

    </div>
@endsection