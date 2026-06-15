@extends('layouts.app')

@section('title', 'Create Teacher')
@section('page-title', 'Create Teacher')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .teacher-page {
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

        .section-card {
            padding: 1.25rem;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .custom-input,
        .form-select,
        .form-control {
            border-radius: 14px;
            min-height: 48px;
            border: 1px solid #e2e8f0;
            box-shadow: none;
        }

        .custom-input:focus,
        .form-select:focus,
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        textarea.form-control {
            min-height: 110px;
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

        .select2-container .select2-selection--single {
            height: 48px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 46px;
            padding-left: 14px;
            padding-right: 30px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px;
            right: 10px;
        }

        .select2-container {
            width: 100% !important;
        }

        .teacher-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .8rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 600;
            font-size: .85rem;
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

    <div class="teacher-page">

        <div class="hero-card">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Create Teacher</h4>
                    <p class="mb-0 text-muted">Add a new teacher profile and account details.</p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>
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

            <form method="POST" action="{{ route('admin.teachers.store') }}">
                @csrf

                @include('admin.teachers.partials.form', [
                    'teacher' => null,
                    'buttonText' => 'Save Teacher'
                ])
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.select2-bank-branch').select2({
                placeholder: 'Search Bank or Branch',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush