@extends('layouts.app')

@section('title', 'Generate Student ID')
@section('page-title', 'Generate Student ID')

@section('breadcrumb')
    <li class="breadcrumb-item active">Generate Student ID</li>
@endsection

@push('styles')
    <style>
        @font-face {
            font-family: 'Monbaiti';
            src: url('{{ asset('fonts/monbaiti.ttf') }}') format('truetype');
        }

        :root {
            --id-blue: #0f4bb5;
            --id-blue-dark: #123a8a;
            --id-teal: #2ab5a5;
            --id-green: #17a34a;
            --id-muted: #64748b;
        }

        .student-id-page {
            animation: fadeIn 0.35s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 30px;
            padding: 1.75rem;
            background: linear-gradient(135deg, #0f172a 0%, #0f4bb5 52%, #0f766e 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        }

        .hero-card::before,
        .hero-card::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-card::before {
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, 0.08);
            top: -120px;
            right: -90px;
        }

        .hero-card::after {
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.06);
            bottom: -70px;
            left: -70px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.95rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.08);
            font-weight: 700;
            font-size: 0.84rem;
            backdrop-filter: blur(8px);
        }

        .premium-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #edf2f7;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .search-card {
            padding: 1.4rem;
        }

        .custom-input {
            height: 52px;
            border-radius: 16px;
            border: 1px solid #dbe3ee;
            box-shadow: none;
        }

        .custom-input:focus {
            border-color: var(--id-blue);
            box-shadow: 0 0 0 4px rgba(15, 75, 181, 0.08);
        }

        .custom-btn {
            border-radius: 16px;
            height: 50px;
            padding: 0 1.2rem;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .bulk-actions {
            background: #fff;
            padding: 1.2rem 1.3rem;
            border-radius: 26px;
            border: 1px solid #edf2f7;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }

        .student-card {
            position: relative;
            transition: 0.25s ease;
        }

        .student-card:hover {
            transform: translateY(-4px);
        }

        .student-card.selected .premium-student-card {
            border-color: var(--id-blue);
            box-shadow: 0 18px 35px rgba(15, 75, 181, 0.12);
        }

        .premium-student-card {
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid #edf2f7;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
            transition: 0.2s ease;
            height: 100%;
        }

        .student-top {
            padding: 0.95rem 1.1rem;
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
        }

        .student-id-badge {
            background: rgba(15, 75, 181, 0.08);
            color: var(--id-blue);
            border-radius: 999px;
            padding: 0.42rem 0.9rem;
            font-weight: 800;
            font-size: 0.82rem;
            letter-spacing: 0.4px;
        }

        .id-card-preview-wrap {
            background: #dde6f0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 12px 12px 0 12px;
        }

        .id-scale-box {
            width: 100%;
            overflow: hidden;
            display: flex;
            justify-content: center;
        }

        .student-id-card {
            width: 85.725mm;
            height: 53.975mm;
            border-radius: 3mm;
            position: relative;
            overflow: hidden;
            background: #f5ede8;
            box-shadow: 0 3mm 8mm rgba(2, 6, 23, 0.18);
            font-family: Arial, Helvetica, sans-serif;
            flex-shrink: 0;
        }

        .card-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .card-header {
            position: absolute;
            top: 2.5mm;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 2;
        }

        .card-inst-name {
            font-size: 3.8mm;
            font-weight: 900;
            letter-spacing: 0.15mm;
            line-height: 1.1;
            background: linear-gradient(90deg, #0f2d7a 0%, #1d4ed8 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .card-inst-sub {
            font-size: 2.5mm;
            font-weight: 800;
            letter-spacing: 1mm;
            margin-top: 0.8mm;
            text-transform: uppercase;
            background: linear-gradient(90deg, #0f2d7a 0%, #1d4ed8 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .card-photo {
            position: absolute;
            top: 14mm;
            left: 4mm;
            width: 19mm;
            height: 24mm;
            background: #c8d4e0;
            overflow: hidden;
            z-index: 2;
            box-shadow: 0 0.5mm 2mm rgba(0, 0, 0, 0.2);
        }

        .card-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .card-qr {
            position: absolute;
            top: 15mm;
            right: 4mm;
            width: 21mm;
            height: 21mm;
            border: 0.4mm solid #1d4ed8;
            border-radius: 2mm;
            padding: 1mm;
            box-shadow: 0 0.5mm 2mm rgba(0, 0, 0, 0.14);
            z-index: 2;
        }

        .card-qr img {
            width: 100%;
            height: 100%;
            display: block;
            border-radius: 1mm;
        }

        .card-details {
            position: absolute;
            left: 4mm;
            top: 41mm;
            z-index: 2;
        }

        .card-info-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.2mm;
            font-size: 3mm;
            color: #03050a;
            line-height: 1.35;
        }

        .card-info-value {
            font-weight: 500;
            max-width: 50mm;
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.2;
        }

        .card-details .card-info-row:last-child .card-info-value {
            font-size: 2.2mm;
            line-height: 1.25;
        }

        .card-details .card-info-row:first-child .card-info-value {
            font-size: 2.8mm;
            font-weight: 600;
        }

        .card-id-pill {
            position: absolute;
            right: 1.3mm;
            bottom: 7.7mm;
            color: #ffffff;
            padding: 1.5mm 4.5mm;
            font-size: 3.2mm;
            font-weight: 900;
            letter-spacing: 0.3mm;
            white-space: nowrap;
            z-index: 2;
            border-radius: 2mm;
        }

        .html2canvas-capture .card-inst-name,
        .html2canvas-capture .card-inst-sub {
            background: none !important;
            -webkit-background-clip: unset !important;
            background-clip: unset !important;
            color: #1d4ed8 !important;
        }

        .download-btn {
            width: 100%;
            height: 48px;
            border-radius: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--id-blue), #3b82f6);
            border: none;
            transition: all 0.3s ease;
        }

        .download-btn:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 75, 181, 0.3);
        }

        .pagination .page-link {
            border-radius: 14px !important;
            margin: 0 4px;
            border: none;
            color: #0f172a;
            min-width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
        }

        .pagination .active .page-link {
            background: var(--id-blue);
            color: #fff;
        }

        @media print {
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            .sidebar,
            .top-navbar,
            .hero-card,
            .search-card,
            .bulk-actions,
            .download-btn,
            .pagination,
            .student-top,
            .student-action-area,
            .form-check,
            .student-id-badge,
            .container-fluid,
            .row>.col-xl-4,
            .premium-student-card {
                display: none !important;
            }

            .student-card {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .student-id-card {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 auto !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .id-card-preview-wrap,
            .id-scale-box {
                padding: 0 !important;
                margin: 0 !important;
                background: none !important;
                height: auto !important;
            }

            body {
                margin: 0;
                padding: 0;
            }
        }

        .badge {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .badge.bg-warning {
            background: #f59e0b !important;
            color: #000;
        }

        .badge.bg-success {
            background: #10b981 !important;
        }

        .badge.bg-info {
            background: #0ea5e9 !important;
        }

        .badge.bg-danger {
            background: #ef4444 !important;
        }

        .student-select:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-secondary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .student-card-badge {
            font-size: 0.7rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 student-id-page">

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @include('admin.student-id-cards.components.hero-section', ['totalStudents' => $students->total()])
        @include('admin.student-id-cards.components.search-form')
        @include('admin.student-id-cards.components.bulk-actions')

        @if($students->count() > 0)
            <div class="row" id="studentsGrid">
                @foreach($students as $student)
                    @include('admin.student-id-cards.components.student-card', ['student' => $student])
                @endforeach
            </div>
        @else
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm rounded-4 text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3 d-block text-muted"></i>
                    <h5>No Students Found</h5>
                    <p class="mb-0">{{ request('search') ? 'No matching students found.' : 'Please add students first.' }}</p>
                </div>
            </div>
        @endif

        @include('admin.student-id-cards.components.pagination', ['students' => $students])

    </div>
@endsection

@push('scripts')
    @include('admin.student-id-cards.components.scripts')
@endpush