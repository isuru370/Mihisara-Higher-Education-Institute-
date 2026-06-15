@extends('layouts.app')

@section('title', 'Create Class Category Fee')
@section('page-title', 'Create Class Category Fee')

@push('styles')
    <style>
        .fee-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card,
        .section-card {
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
    <div class="fee-page">

        <div class="hero-card">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Create Class Category Fee</h4>
                    <p class="mb-0 text-muted">Add a new fee record for a class and category.</p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.class-category-fees.index') }}" class="btn btn-outline-secondary custom-btn">
                        Back
                    </a>

                    <button type="button" class="btn btn-outline-primary custom-btn" disabled aria-disabled="true">
                        Future Button
                    </button>
                </div>
            </div>
        </div>

        <div class="main-card">

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm rounded-4">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('admin.class_category_fees.partials.form', [
                'classCategoryFee' => null,
                'classes' => $classes,
                'categories' => $categories,
                'selectedClassId' => $selectedClassId ?? null,
                'buttonText' => 'Save Fee'
            ])

        </div>
    </div>
@endsection