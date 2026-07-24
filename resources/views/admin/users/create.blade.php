@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create New User')

@section('content')

<div class="user-form-page">

    <div class="main-card">

        <!-- HEADER -->
        <div class="main-card-header">

            <div>
                <h4>
                    <i class="bi bi-person-plus-fill text-primary me-2"></i>
                    Create New User
                </h4>
                <p class="text-muted mb-0">
                    Add a new system user with appropriate role and permissions
                </p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back to List
                </a>
            </div>

        </div>

        <!-- FORM -->
        <form action="{{ route('admin.users.store') }}" method="POST">

            @csrf

            <div class="row g-4">

                <!-- BASIC INFORMATION -->
                <div class="col-md-6">

                    <div class="form-section">

                        <h6 class="form-section-title">
                            <i class="bi bi-person-fill me-2"></i>
                            Basic Information
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name"
                                class="form-control custom-input @error('name') is-invalid @enderror"
                                placeholder="Enter full name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email"
                                class="form-control custom-input @error('email') is-invalid @enderror"
                                placeholder="Enter email address" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                User Type <span class="text-danger">*</span>
                            </label>
                            <select name="user_type_id"
                                class="form-select custom-input @error('user_type_id') is-invalid @enderror" required>
                                <option value="">Select User Type</option>
                                @foreach ($userTypes as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('user_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                    value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActive">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    Active Account
                                </label>
                            </div>
                            <small class="text-muted">Inactive users cannot log in to the system</small>
                        </div>

                    </div>

                </div>

                <!-- PASSWORD & LINKING -->
                <div class="col-md-6">

                    <div class="form-section">

                        <h6 class="form-section-title">
                            <i class="bi bi-lock-fill me-2"></i>
                            Password & Linking
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Password <span class="text-danger">*</span>
                            </label>
                            <div class="password-input-wrapper">
                                <i class="bi bi-key-fill"></i>
                                <input type="password" name="password" id="password"
                                    class="form-control custom-input @error('password') is-invalid @enderror"
                                    placeholder="Enter password" required minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="bi bi-eye-slash-fill"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 characters with letters and numbers</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Confirm Password <span class="text-danger">*</span>
                            </label>
                            <div class="password-input-wrapper">
                                <i class="bi bi-shield-lock-fill"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control custom-input" placeholder="Confirm password" required>
                                <button type="button" class="password-toggle"
                                    onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye-slash-fill"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-1"></div>
                        </div>

                        <hr>

                        <!-- LINK TO SYSTEM USER -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-badge-fill me-1"></i>
                                Link to System User (Optional)
                            </label>
                            <select name="system_user_id"
                                class="form-select custom-input @error('system_user_id') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach ($systemUsers as $systemUser)
                                    <option value="{{ $systemUser->id }}"
                                        {{ old('system_user_id') == $systemUser->id ? 'selected' : '' }}>
                                        {{ $systemUser->full_name }} ({{ $systemUser->custom_id ?? 'No ID' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('system_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Link this user account to an existing system user</small>
                        </div>

                        <!-- LINK TO TEACHER -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-badge-fill me-1"></i>
                                Link to Teacher (Optional)
                            </label>
                            <select name="teacher_id" class="form-select custom-input @error('teacher_id') is-invalid @enderror">
                                <option value="">None</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                        {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->full_name }} ({{ $teacher->custom_id ?? 'No ID' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Link this user account to an existing teacher</small>
                        </div>

                    </div>

                </div>

                <!-- BUTTONS -->
                <div class="col-12">

                    <hr>

                    <div class="d-flex gap-3 flex-wrap">
                        <button type="submit" class="btn btn-primary custom-btn btn-lg">
                            <i class="bi bi-plus-lg me-2"></i>
                            Create User
                        </button>
                        <button type="reset" class="btn btn-light border custom-btn">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset Form
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary custom-btn">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </a>
                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection

@push('styles')
<style>
    .user-form-page {
        animation: fadeIn 0.4s ease;
    }

    .main-card {
        background: white;
        border-radius: 28px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .main-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .main-card-header h4 {
        margin: 0;
        font-weight: 700;
    }

    .custom-btn {
        border-radius: 14px;
        padding: 0.7rem 1.2rem;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .custom-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .custom-btn.btn-lg {
        padding: 0.8rem 1.5rem;
        font-size: 1.1rem;
    }

    .custom-input {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        min-height: 48px;
    }

    .form-section {
        background: #f8fafc;
        border-radius: 20px;
        padding: 1.5rem;
    }

    .form-section-title {
        font-weight: 700;
        margin-bottom: 1.2rem;
        padding-bottom: 0.8rem;
        border-bottom: 2px solid #eef2f7;
        color: #1e293b;
    }

    .password-input-wrapper {
        position: relative;
    }

    .password-input-wrapper i:first-child {
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        color: #64748b;
        z-index: 2;
    }

    .password-input-wrapper .custom-input {
        padding-left: 42px;
        padding-right: 48px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        padding: 5px;
        cursor: pointer;
        z-index: 2;
    }

    .password-toggle:hover {
        color: #1e293b;
    }

    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    /* Error styling */
    .is-invalid {
        border-color: #ef4444 !important;
    }

    .invalid-feedback {
        font-size: 0.85rem;
        margin-top: 0.3rem;
    }

    @media(max-width: 768px) {
        .main-card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-buttons {
            width: 100%;
        }

        .header-buttons a {
            width: 100%;
            text-align: center;
            justify-content: center;
        }

        .form-section {
            padding: 1rem;
        }

        .custom-btn.btn-lg {
            width: 100%;
        }

        .d-flex.gap-3 {
            flex-direction: column;
        }

        .d-flex.gap-3 .custom-btn,
        .d-flex.gap-3 a {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }

    @media(max-width: 576px) {
        .main-card {
            padding: 1rem;
        }

        .form-section {
            padding: 0.8rem;
        }
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
</style>
@endpush

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        if (!input) return;

        const icon = input.parentElement.querySelector('.password-toggle i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'bi bi-eye-fill';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'bi bi-eye-slash-fill';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const matchMessage = document.getElementById('passwordMatch');

        if (password && confirmPassword && matchMessage) {
            confirmPassword.addEventListener('input', function() {
                if (this.value.length === 0) {
                    matchMessage.innerHTML = '';
                    return;
                }

                if (password.value === this.value) {
                    matchMessage.innerHTML =
                        '<i class="bi bi-check-circle-fill text-success me-1"></i> <span class="text-success fw-semibold">Passwords match!</span>';
                } else {
                    matchMessage.innerHTML =
                        '<i class="bi bi-exclamation-circle-fill text-danger me-1"></i> <span class="text-danger fw-semibold">Passwords do not match</span>';
                }
            });
        }
    });
</script>
@endpush