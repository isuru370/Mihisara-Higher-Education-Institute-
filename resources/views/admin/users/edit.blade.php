@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')

<div class="user-form-page">

    <div class="main-card">

        <!-- HEADER -->
        <div class="main-card-header">

            <div>
                <h4>
                    <i class="bi bi-pencil-square text-warning me-2"></i>
                    Edit User: {{ $user->name }}
                </h4>
                <p class="text-muted mb-0">
                    Update user details, role and permissions
                </p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info custom-btn">
                    <i class="bi bi-eye-fill"></i>
                    View Profile
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back to List
                </a>
            </div>

        </div>

        <!-- USER STATUS ALERT -->
        @if($user->trashed())
            <div class="alert alert-warning custom-alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                This user is currently <strong>deleted</strong>. You can restore them from the user list.
            </div>
        @endif

        <!-- FORM -->
        <form action="{{ route('admin.users.update', $user) }}" method="POST">

            @csrf
            @method('PUT')

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
                            <input type="text" name="name" class="form-control custom-input @error('name') is-invalid @enderror"
                                placeholder="Enter full name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control custom-input @error('email') is-invalid @enderror"
                                placeholder="Enter email address" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                User Type <span class="text-danger">*</span>
                            </label>
                            <select name="user_type_id" class="form-select custom-input @error('user_type_id') is-invalid @enderror" required>
                                <option value="">Select User Type</option>
                                @foreach($userTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('user_type_id', $user->user_type_id) == $type->id ? 'selected' : '' }}>
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
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1"
                                    {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="isActive">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    Active Account
                                </label>
                            </div>
                            <small class="text-muted">Inactive users cannot log in to the system</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar3 me-1"></i>
                                Created At
                            </label>
                            <p class="text-muted mb-0">{{ $user->created_at->format('Y-m-d H:i:s') }}</p>
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
                                Change Password <span class="text-muted">(Optional)</span>
                            </label>
                            <div class="password-input-wrapper">
                                <i class="bi bi-key-fill"></i>
                                <input type="password" name="password" id="password"
                                    class="form-control custom-input @error('password') is-invalid @enderror"
                                    placeholder="Enter new password (leave blank to keep current)" minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="bi bi-eye-slash-fill"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to keep current password. Minimum 8 characters.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Confirm Password
                            </label>
                            <div class="password-input-wrapper">
                                <i class="bi bi-shield-lock-fill"></i>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control custom-input"
                                    placeholder="Confirm new password">
                                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                    <i class="bi bi-eye-slash-fill"></i>
                                </button>
                            </div>
                            <div id="passwordMatch" class="mt-1"></div>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person-badge-fill me-1"></i>
                                Link to Teacher (Optional)
                            </label>
                            <select name="teacher_id" class="form-select custom-input">
                                <option value="">None</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}"
                                        {{ old('teacher_id', $user->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->full_name }} ({{ $teacher->custom_id ?? 'No ID' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Link this user account to an existing teacher</small>
                            @if($user->teacher)
                                <div class="mt-2">
                                    <span class="badge bg-info">
                                        Currently linked to: {{ $user->teacher->full_name }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- CURRENT STATUS -->
                        <div class="mt-3 p-3 bg-white rounded-3 border">
                            <h6 class="mb-2">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i>
                                Account Status
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li>
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    User Type: <strong>{{ $user->userType->name ?? 'N/A' }}</strong>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Status: <strong>{{ $user->is_active ? 'Active' : 'Inactive' }}</strong>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Last Updated: <strong>{{ $user->updated_at->diffForHumans() }}</strong>
                                </li>
                            </ul>
                        </div>

                    </div>

                </div>

                <!-- BUTTONS -->
                <div class="col-12">

                    <hr>

                    <div class="d-flex gap-3 flex-wrap">
                        <button type="submit" class="btn btn-warning custom-btn btn-lg">
                            <i class="bi bi-pencil-fill me-2"></i>
                            Update User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary custom-btn">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </a>
                        @if(!$user->trashed())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                onsubmit="return confirm('Delete this user?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger custom-btn">
                                    <i class="bi bi-trash-fill me-2"></i>
                                    Delete User
                                </button>
                            </form>
                        @endif
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

    .custom-alert {
        border-radius: 16px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
    }

    @media(max-width: 768px) {
        .main-card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-buttons {
            width: 100%;
        }

        .header-buttons a,
        .header-buttons button {
            flex: 1;
            text-align: center;
            justify-content: center;
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
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
                    matchMessage.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> <span class="text-success fw-semibold">Passwords match!</span>';
                } else {
                    matchMessage.innerHTML = '<i class="bi bi-exclamation-circle-fill text-danger me-1"></i> <span class="text-danger fw-semibold">Passwords do not match</span>';
                }
            });
        }
    });
</script>
@endpush