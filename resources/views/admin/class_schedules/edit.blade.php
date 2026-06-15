@extends('layouts.app')

@section('title', 'Edit Class Schedule')
@section('page-title', 'Edit Class Schedule')

@section('content')
    @php
        $className = optional(optional($classSchedule)->studentClass)->class_name ?: '-';
        $categoryName = optional(optional($classSchedule)->category)->category_name ?: '-';
    @endphp

    <div class="schedule-page">

        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <div class="eyebrow mb-2">
                        Edit Schedule
                    </div>

                    <h3 class="fw-bold mb-1">
                        Edit Class Schedule
                    </h3>

                    <p class="text-muted mb-0">
                        {{ $className }} / {{ $categoryName }}
                    </p>
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
                'classSchedule' => $classSchedule,
                'scheduleType' => 'single',
                'selectedClass' => $classSchedule->studentClass ?? null,
                'selectedCategory' => $classSchedule->classCategoryFee->category ?? null,
                'selectedCategoryFeeId' => $classSchedule->class_category_fee_id,
                'buttonText' => 'Update Schedule'
            ])
            </div>
        </div>
@endsection

@push('styles')
    <style>
        .schedule-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
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
            gap: .7rem;
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
        }
    </style>
@endpush