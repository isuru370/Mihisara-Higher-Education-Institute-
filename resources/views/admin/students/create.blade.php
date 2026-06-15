@extends('layouts.app')

@section('title', 'Create Student')
@section('page-title', 'Create Student')

@push('styles')
    <style>
        .student-page {
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

        .alert {
            border-radius: 18px;
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

        .section-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .04);
        }

        .section-title {
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: #0f172a;
        }

        .custom-input {
            border-radius: 14px;
            min-height: 48px;
            border: 1px solid #dbe3ec;
            box-shadow: none;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        /* IMAGE FIX */
        .image-preview-wrapper {
            width: 110px;
            height: 110px;
            min-width: 110px;
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 2px dashed #cbd5e1;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
            background: white;
        }

        /* MOBILE */
        @media (max-width: 768px) {
            .image-preview-wrapper {
                width: 90px;
                height: 90px;
                min-width: 90px;
            }
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
    <div class="student-page">

        <div class="hero-card">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Create New Student</h4>
                    <p class="mb-0 text-muted">Required fields are marked with <span class="text-danger">*</span></p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card">

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('portal_username') && session('portal_password'))
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-person-check-fill me-2"></i>
                    <strong>Student Portal Login Created</strong><br>
                    <strong>Username:</strong> {{ session('portal_username') }}<br>
                    <strong>Password:</strong> {{ session('portal_password') }}
                    <small class="text-danger d-block mt-2">
                        <i class="bi bi-exclamation-circle"></i> Save this password. It will not be shown again.
                    </small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
                @csrf

                @include('admin.students.partials.form', [
                    'student' => null,
                    'grades' => $grades,
                    'admissions' => $admissions,
                    'buttonText' => 'Save Student',
                    'isEdit' => false
                ])
                    </form>

                </div>
            </div>
@endsection