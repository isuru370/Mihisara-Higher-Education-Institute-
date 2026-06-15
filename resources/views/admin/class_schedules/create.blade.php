@extends('layouts.app')

@section('title', 'Create Schedule')
@section('page-title', 'Create Schedule')

@section('content')
    @php
        $scheduleType = $scheduleType ?? request('type', 'recurring');

        $pageTitle = $scheduleType === 'single'
            ? 'Create Single Day Schedule'
            : 'Create Recurring Schedule';

        $pageDescription = $scheduleType === 'single'
            ? 'Add one class schedule date'
            : 'Generate schedules between start and end date';

        $buttonText = $scheduleType === 'single'
            ? 'Save Single Schedule'
            : 'Save Recurring Schedule';
    @endphp

    <div class="schedule-page">
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <div class="eyebrow mb-2">Class Scheduling</div>
                    <h3 class="fw-bold mb-1">{{ $pageTitle }}</h3>
                    <p class="text-muted mb-0">{{ $pageDescription }}</p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.class-schedules.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card">
            @include('admin.class_schedules.partials.form', [
    'classSchedule' => null,
    'scheduleType' => $scheduleType,
    'selectedClass' => $selectedClass ?? null,
    'selectedCategory' => $selectedCategory ?? null,
    'selectedClassId' => $selectedClassId ?? null,
    'selectedCategoryFeeId' => $selectedCategoryFeeId ?? null,
    'buttonText' => $buttonText
])
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .schedule-page {
            animation: fadeIn .35s ease;
        }

        .hero-card,
        .main-card {
            background: #fff;
            border: 1px solid #e8eef5;
            border-radius: 28px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, .06);
            padding: 1.5rem;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-actions {
            display: flex;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #2563eb;
            background: rgba(37, 99, 235, .08);
            border: 1px solid rgba(37, 99, 235, .12);
            border-radius: 999px;
            padding: .35rem .7rem;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .75rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions {
                width: 100%;
            }

            .hero-actions .btn {
                flex: 1;
            }

            .hero-card,
            .main-card {
                border-radius: 22px;
                padding: 1rem;
            }
        }
    </style>
@endpush
