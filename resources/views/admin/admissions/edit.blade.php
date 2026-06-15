@extends('layouts.app')

@section('title', 'Edit Admission')
@section('page-title', 'Edit Admission')

@push('styles')
    <style>
        .edit-admission-page {
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

        /* Form Card */
        .form-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
            transition: all .25s ease;
        }

        .form-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px rgba(15, 23, 42, .1);
        }

        .form-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .form-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-title i {
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

        /* Form Body */
        .form-body {
            padding: 1.5rem;
        }

        /* Form Elements */
        .form-label-custom {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-label-custom i {
            margin-right: 0.5rem;
            color: #2563eb;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control-custom {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
            background: #fff;
        }

        .form-control-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .form-control-custom[readonly] {
            background: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
        }

        textarea.form-control-custom {
            resize: vertical;
            min-height: 100px;
        }

        /* Amount Input with Currency Symbol */
        .currency-input {
            position: relative;
        }

        .currency-symbol {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-weight: 600;
            color: #64748b;
            pointer-events: none;
            z-index: 1;
        }

        .currency-input .form-control-custom {
            padding-left: 2.5rem;
        }

        /* Checkbox Toggle Switch */
        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid #eef2f7;
        }

        .toggle-label {
            font-weight: 600;
            color: #1e293b;
        }

        .toggle-description {
            font-size: 0.7rem;
            color: #64748b;
            margin-left: auto;
        }

        /* Custom Checkbox */
        .custom-checkbox {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }

        .custom-checkbox input {
            width: 18px;
            height: 18px;
            margin-right: 0.5rem;
            cursor: pointer;
            accent-color: #2563eb;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eef2f7;
        }

        .btn-update {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            color: white;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(245, 158, 11, 0.25);
        }

        .btn-cancel {
            background: #64748b;
            border: none;
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-cancel:hover {
            background: #475569;
            transform: translateY(-2px);
            color: white;
        }

        .btn-view {
            background: #0ea5e9;
            border: none;
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-view:hover {
            background: #0284c7;
            transform: translateY(-2px);
            color: white;
        }

        /* Error Message */
        .error-message {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #ef4444;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Info Box */
        .info-box {
            background: #eff6ff;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            border-left: 4px solid #2563eb;
        }

        .info-box i {
            font-size: 1.2rem;
            color: #2563eb;
        }

        .info-box-text {
            font-size: 0.8rem;
            color: #1e40af;
            line-height: 1.4;
        }

        /* Warning Box */
        .warning-box {
            background: #fef3c7;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            border-left: 4px solid #f59e0b;
        }

        .warning-box i {
            font-size: 1.2rem;
            color: #d97706;
        }

        .warning-box-text {
            font-size: 0.8rem;
            color: #92400e;
            line-height: 1.4;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }
            .form-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .btn-back {
                width: 100%;
                justify-content: center;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn-update, .btn-cancel, .btn-view {
                width: 100%;
                justify-content: center;
            }
            .toggle-switch {
                flex-wrap: wrap;
            }
            .toggle-description {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="edit-admission-page">

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="hero-badge mb-3">
                        <i class="bi bi-pencil-square-fill"></i>
                        Edit Record
                    </div>
                    <h2 class="fw-bold mb-2">Edit Admission</h2>
                    <p class="mb-0 text-light-soft">
                        Update admission fee structure details
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

        {{-- FORM CARD --}}
        <div class="form-card">
            <div class="form-header">
                <h5 class="form-title">
                    <i class="bi bi-journal-text"></i>
                    Edit Admission Information
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.admissions.show', $admission) }}" class="btn-view">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    <a href="{{ route('admin.admissions.index') }}" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="form-body">
                {{-- Info Box --}}
                <div class="info-box">
                    <i class="bi bi-info-circle-fill"></i>
                    <div class="info-box-text">
                        Update the admission details below. Fields marked with <span class="text-danger">*</span> are required.
                    </div>
                </div>

                {{-- Warning Box for Inactive Status --}}
                @if(!$admission->is_active)
                    <div class="warning-box">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div class="warning-box-text">
                            This admission is currently <strong>Inactive</strong>. Inactive admissions will not appear in student enrollment options.
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.admissions.update', $admission) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Admission Name --}}
                    <div class="input-group-custom">
                        <label class="form-label-custom">
                            <i class="bi bi-tag-fill"></i> Admission Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               class="form-control-custom @error('name') is-invalid @enderror"
                               value="{{ old('name', $admission->name) }}"
                               placeholder="Enter admission name (e.g., Primary Admission, Secondary Admission)">
                        @error('name')
                            <div class="error-message">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div class="input-group-custom">
                        <label class="form-label-custom">
                            <i class="bi bi-cash-stack"></i> Amount <span class="text-danger">*</span>
                        </label>
                        <div class="currency-input">
                            <span class="currency-symbol">Rs.</span>
                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   class="form-control-custom @error('amount') is-invalid @enderror"
                                   value="{{ old('amount', $admission->amount) }}"
                                   placeholder="0.00">
                        </div>
                        @error('amount')
                            <div class="error-message">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Note --}}
                    <div class="input-group-custom">
                        <label class="form-label-custom">
                            <i class="bi bi-chat-text-fill"></i> Note (Optional)
                        </label>
                        <textarea name="note"
                                  rows="4"
                                  class="form-control-custom @error('note') is-invalid @enderror"
                                  placeholder="Enter any additional notes or description about this admission...">{{ old('note', $admission->note) }}</textarea>
                        @error('note')
                            <div class="error-message">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Status Toggle --}}
                    <div class="toggle-switch">
                        <div class="custom-checkbox">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   id="isActive"
                                   {{ old('is_active', $admission->is_active) ? 'checked' : '' }}>
                            <label for="isActive" class="toggle-label">
                                <i class="bi bi-flag-fill"></i> Active
                            </label>
                        </div>
                        <div class="toggle-description">
                            When active, this admission will be available for student enrollment
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="action-buttons">
                        <button type="submit" class="btn-update">
                            <i class="bi bi-check-lg"></i> Update Admission
                        </button>
                        <a href="{{ route('admin.admissions.index') }}" class="btn-cancel">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- FOOTER NOTE --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-shield-check"></i> 
                Last updated: {{ $admission->updated_at->format('d M Y, h:i A') }} | 
                Created: {{ $admission->created_at->format('d M Y, h:i A') }}
            </small>
        </div>

    </div>
@endsection