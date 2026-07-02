@extends('layouts.app')

@section('title', 'Bulk Send Notification')
@section('page-title', 'Bulk Send Notification')

@section('content')

    <div class="notification-create-page">

        {{-- MAIN CARD --}}
        <div class="main-card">

            {{-- HEADER --}}
            <div class="main-card-header">
                <div>
                    <h4>
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        Bulk Send Notification
                    </h4>
                    <p>Send a notification to multiple students at once</p>
                </div>

                <div class="header-buttons">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary custom-btn">
                        <i class="bi bi-arrow-left"></i>
                        Back to List
                    </a>
                </div>
            </div>

            {{-- ALERT --}}
            @if (session('error'))
                <div class="alert alert-danger custom-alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            {{-- FORM --}}
            <form action="{{ route('admin.notifications.bulk') }}" method="POST">
                @csrf

                <div class="row g-4">

                    {{-- STUDENTS --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-people-fill text-primary me-1"></i>
                                Select Students <span class="text-danger">*</span>
                            </label>
                            <select name="student_ids[]"
                                class="form-select custom-input @error('student_ids') is-invalid @enderror" multiple
                                required style="height: 200px;">
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}"
                                        {{ in_array($student->id, old('student_ids', [])) ? 'selected' : '' }}>
                                        {{ $student->initial_name }} ({{ $student->custom_id ?? 'No ID' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple students</small>
                            @error('student_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- TITLE --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-fonts text-primary me-1"></i>
                                Title <span class="text-danger">*</span>
                            </label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-pencil"></i>
                                <input type="text" name="title"
                                    class="form-control custom-input @error('title') is-invalid @enderror"
                                    value="{{ old('title') }}" maxlength="150" placeholder="Enter notification title..."
                                    required>
                            </div>
                            <small class="text-muted">Maximum 150 characters</small>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-textarea text-primary me-1"></i>
                                Body <span class="text-danger">*</span>
                            </label>
                            <textarea name="body" class="form-control custom-textarea @error('body') is-invalid @enderror" rows="6"
                                maxlength="1000" placeholder="Enter notification message..." required>{{ old('body') }}</textarea>
                            <small class="text-muted">Maximum 1000 characters</small>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- TYPE & SCHEDULE --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-tag-fill text-primary me-1"></i>
                                Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" class="form-select custom-input @error('type') is-invalid @enderror"
                                required>
                                @foreach ($types as $type)
                                    <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- SUBMIT --}}
                    <div class="col-12">
                        <div class="form-actions">
                            <button type="reset" class="btn btn-light border custom-btn">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-primary custom-btn">
                                <i class="bi bi-send-fill"></i>
                                Send to All Selected Students
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        /* Same styles as create.blade.php */
        .notification-create-page {
            animation: fadeIn 0.4s ease;
        }

        .main-card {
            background: #fff;
            border-radius: 28px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .main-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .main-card-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .main-card-header p {
            margin: 0;
            color: #64748b;
        }

        .header-buttons {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .72rem 1.2rem;
            font-weight: 600;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            transition: .3s ease;
            font-size: .9rem;
            text-decoration: none;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .btn-primary.custom-btn {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
        }

        .btn-secondary.custom-btn {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary.custom-btn:hover {
            background: #e2e8f0;
        }

        .btn-light.custom-btn {
            background: #fff;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-light.custom-btn:hover {
            background: #f8fafc;
        }

        .custom-alert {
            border-radius: 16px;
            border: 1px solid #fecaca;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: .5rem;
            display: block;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-input-wrapper i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
            z-index: 1;
        }

        .custom-input {
            min-height: 48px;
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            padding-left: 42px;
            box-shadow: none !important;
            transition: .3s ease;
            width: 100%;
            background: #fff;
        }

        .custom-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        select.custom-input[multiple] {
            padding-left: 16px;
            padding-top: 10px;
        }

        select.custom-input[multiple] option {
            padding: 8px 12px;
            border-radius: 8px;
        }

        select.custom-input[multiple] option:checked {
            background: #2563eb;
            color: #fff;
        }

        select.custom-input {
            padding-left: 16px;
            padding-right: 40px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
        }

        .custom-textarea {
            min-height: 48px;
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            box-shadow: none !important;
            transition: .3s ease;
            width: 100%;
            background: #fff;
            resize: vertical;
        }

        .custom-textarea:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        .is-invalid {
            border-color: #ef4444 !important;
        }

        .is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .invalid-feedback {
            color: #ef4444;
            font-size: .85rem;
            margin-top: .25rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            padding-top: .5rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .main-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-buttons {
                width: 100%;
            }

            .header-buttons a {
                flex: 1;
                justify-content: center;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .custom-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush
