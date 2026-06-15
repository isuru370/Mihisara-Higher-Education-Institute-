@extends('layouts.app')

@section('title', 'Class Category Fee Details')
@section('page-title', 'Class Category Fee Details')

@push('styles')
    <style>
        .fee-details-page {
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

        .title-sub {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
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
            background: linear-gradient(135deg, #f59e0b, #d97706);
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
            box-shadow: 0 8px 18px rgba(245, 158, 11, 0.25);
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

        .info-value.fee {
            font-size: 1.3rem;
            color: #10b981;
            font-family: monospace;
        }

        .info-value.code {
            font-family: monospace;
            background: #f1f5f9;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            display: inline-block;
        }

        /* Badges */
        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
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
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Fee Information Section */
        .fee-section {
            margin-top: 1.5rem;
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid #bbf7d0;
        }

        .fee-title {
            font-size: 0.8rem;
            font-weight: 800;
            color: #166534;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .fee-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        .fee-item {
            background: #fff;
            border-radius: 16px;
            padding: 0.75rem;
            text-align: center;
            border: 1px solid #bbf7d0;
        }

        .fee-item-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .fee-item-value {
            font-size: 1rem;
            font-weight: 800;
            color: #166534;
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

        /* Alert */
        .custom-alert {
            border-radius: 16px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

            .fee-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .action-buttons {
                justify-content: center;
            }
        }

        @media print {

            .hero-card,
            .details-header .btn-back,
            .btn-edit,
            .action-buttons,
            .sidebar,
            .top-navbar {
                display: none !important;
            }

            .details-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .info-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $studentClass = null;
        $category = null;

        if ($classCategoryFee) {
            $studentClass = $classCategoryFee->studentClass;
            $category = $classCategoryFee->category;
        }

        $className = '-';
        $gradeName = 'N/A';
        $subjectName = 'N/A';
        $teacherName = 'N/A';
        $classStatus = false;

        if ($studentClass) {
            if ($studentClass->class_name) {
                $className = $studentClass->class_name;
            }

            if ($studentClass->grade) {
                if ($studentClass->grade->grade_name) {
                    $gradeName = $studentClass->grade->grade_name;
                }
            }

            if ($studentClass->subject) {
                if ($studentClass->subject->subject_name) {
                    $subjectName = $studentClass->subject->subject_name;
                }
            }

            if ($studentClass->teacher) {
                if ($studentClass->teacher->full_name) {
                    $teacherName = $studentClass->teacher->full_name;
                }
            }

            if ($studentClass->is_active) {
                $classStatus = true;
            }
        }

        $categoryName = '-';
        $categoryCode = '-';
        $categoryStatus = false;

        if ($category) {
            if ($category->category_name) {
                $categoryName = $category->category_name;
            }

            if ($category->code) {
                $categoryCode = $category->code;
            }

            if ($category->is_active) {
                $categoryStatus = true;
            }
        }
    @endphp

    <div class="fee-details-page">

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="hero-badge mb-3">
                        <i class="bi bi-cash-stack"></i>
                        Fee Details
                    </div>
                    <h2 class="fw-bold mb-2">Class Category Fee Details</h2>
                    <p class="mb-0 text-light-soft">
                        {{ $className }} / {{ $categoryName }}
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
                <div>
                    <h5 class="details-title">
                        <i class="bi bi-info-circle-fill"></i>
                        Fee Structure Information
                    </h5>
                    <div class="title-sub">Complete details of the class category fee setup</div>
                </div>
                <div class="d-flex gap-2">
                    @if($classStatus && $categoryStatus)
                        <a href="{{ route('admin.class-category-fees.edit', $classCategoryFee) }}" class="btn-edit">
                            <i class="bi bi-pencil-square"></i> Edit Fee
                        </a>
                    @endif
                    <a href="{{ route('admin.class-category-fees.index') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="details-body">
                {{-- Alerts --}}
                @if(session('success'))
                    <div class="alert alert-success custom-alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger custom-alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                {{-- INFO GRID --}}
                <div class="info-grid">
                    {{-- Class Information Card --}}
                    <div class="info-card">
                        <div class="info-label">
                            <i class="bi bi-book-fill"></i>
                            Class Information
                        </div>
                        <div class="info-value mb-2">{{ $className }}</div>
                        <div class="info-value mb-2" style="font-size: 0.85rem; color: #64748b;">
                            <i class="bi bi-bar-chart-steps me-1"></i> {{ $gradeName }}
                        </div>
                        <div class="info-value mb-2" style="font-size: 0.85rem; color: #64748b;">
                            <i class="bi bi-journal-text me-1"></i> {{ $subjectName }}
                        </div>
                        <div class="info-value mb-2" style="font-size: 0.85rem; color: #64748b;">
                            <i class="bi bi-person-badge me-1"></i> {{ $teacherName }}
                        </div>
                        <div class="mt-2">
                            @if($classStatus)
                                <span class="badge-active">
                                    <i class="bi bi-check-circle-fill"></i> Class Active
                                </span>
                            @else
                                <span class="badge-inactive">
                                    <i class="bi bi-x-circle-fill"></i> Class Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Category Information Card --}}
                    <div class="info-card">
                        <div class="info-label">
                            <i class="bi bi-tag-fill"></i>
                            Category Information
                        </div>
                        <div class="info-value mb-2">{{ $categoryName }}</div>
                        <div class="info-value code">
                            <i class="bi bi-upc-scan me-1"></i> {{ $categoryCode }}
                        </div>
                        <div class="mt-2">
                            @if($categoryStatus)
                                <span class="badge-active">
                                    <i class="bi bi-check-circle-fill"></i> Category Active
                                </span>
                            @else
                                <span class="badge-inactive">
                                    <i class="bi bi-x-circle-fill"></i> Category Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FEE INFORMATION SECTION --}}
                <div class="fee-section">
                    <div class="fee-title">
                        <i class="bi bi-cash-stack"></i>
                        Fee Details
                    </div>
                    <div class="fee-grid">
                        <div class="fee-item">
                            <div class="fee-item-label">Fee Amount</div>
                            <div class="fee-item-value">Rs. {{ number_format($classCategoryFee->fee, 2) }}</div>
                        </div>
                        <div class="fee-item">
                            <div class="fee-item-label">Fee Status</div>
                            <div class="fee-item-value">
                                @if($classCategoryFee->is_active)
                                    <span class="badge-active">Active</span>
                                @else
                                    <span class="badge-inactive">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="fee-item">
                            <div class="fee-item-label">Record ID</div>
                            <div class="fee-item-value">#{{ $classCategoryFee->id }}</div>
                        </div>
                    </div>
                    @if($classCategoryFee->note)
                        <div class="mt-3 p-3 bg-white rounded-3" style="border: 1px solid #bbf7d0;">
                            <div class="fee-item-label mb-1">Note / Description</div>
                            <div class="text-dark">{{ $classCategoryFee->note }}</div>
                        </div>
                    @endif
                </div>

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
                            <div class="timeline-event">Fee Record Created</div>
                            <div class="timeline-date">
                                @if($classCategoryFee->created_at)
                                    {{ $classCategoryFee->created_at->format('l, d F Y h:i A') }}
                                @else
                                    Not recorded
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($classCategoryFee->created_at && $classCategoryFee->updated_at && $classCategoryFee->created_at != $classCategoryFee->updated_at)
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-event">Last Updated</div>
                                <div class="timeline-date">
                                    {{ $classCategoryFee->updated_at->format('l, d F Y h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="action-buttons">
                    @if($classStatus && $categoryStatus && $classCategoryFee->is_active)
                        <a href="{{ route('admin.class-category-fees.edit', $classCategoryFee) }}" class="btn-edit">
                            <i class="bi bi-pencil-square"></i> Edit Fee
                        </a>
                    @endif
                    <a href="{{ route('admin.class-category-fees.index') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                Fee ID: #{{ $classCategoryFee->id }} | Last modified:
                {{ $classCategoryFee->updated_at ? $classCategoryFee->updated_at->diffForHumans() : 'N/A' }}
            </small>
        </div>

    </div>
@endsection