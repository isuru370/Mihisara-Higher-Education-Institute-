@extends('layouts.app')

@section('title', 'Edit Class')
@section('page-title', 'Edit Class')

@push('styles')
    <style>
        .class-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .hero-card {
            padding: 1.35rem 1.5rem;
            margin-bottom: 1.5rem;
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

        .main-card {
            padding: 1.5rem;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            border: none;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
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

@section('content')
    <div class="class-page">

        <div class="hero-card">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Edit Class</h4>
                    <p class="mb-0 text-muted">{{ $studentClass->class_name }}</p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.student-classes.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card">
            @include('admin.student_classes.partials.form', [
                'studentClass' => $studentClass,
                'paymentConfig' => $paymentConfig,
                'buttonText' => 'Update Class'
            ])
        </div>

    </div>
@endsection