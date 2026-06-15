@extends('layouts.app')

@section('title', 'User Profile')
@section('page-title', 'User Profile')

@section('content')

    <div class="profile-page">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Error Message --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">

            {{-- Profile Information --}}
            <div class="col-lg-8">
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="header-icon">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div>
                            <h4>Profile Information</h4>
                            <p>Update your personal information and contact details</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <form action="{{ route('admin.profile.update') }}" method="POST" id="profileForm">
                            @csrf

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-person-circle me-1"></i> Login Name
                                        </label>
                                        <input type="text" name="name"
                                            class="form-control form-control-lg @error('name') is-invalid @enderror"
                                            value="{{ old('name', $user->name) }}" placeholder="Enter login name">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-envelope me-1"></i> Email Address
                                        </label>
                                        <input type="email" name="email"
                                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                                            value="{{ old('email', $user->email) }}" placeholder="Enter email address">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-person me-1"></i> Full Name
                                        </label>
                                        <input type="text" name="full_name"
                                            class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                                            value="{{ old('full_name', $user->systemUser?->full_name) }}"
                                            placeholder="Enter full name">
                                        @error('full_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-phone me-1"></i> Mobile Number
                                        </label>
                                        <input type="text" name="mobile" class="form-control form-control-lg"
                                            value="{{ old('mobile', $user->systemUser?->mobile) }}"
                                            placeholder="Enter mobile number">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-card-text me-1"></i> NIC
                                        </label>
                                        <input type="text" name="nic" class="form-control form-control-lg"
                                            value="{{ old('nic', $user->systemUser?->nic) }}"
                                            placeholder="Enter NIC number">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-calendar-heart me-1"></i> Birthday
                                        </label>
                                        <input type="date" name="bday" class="form-control form-control-lg"
                                            value="{{ old('bday', optional($user->systemUser?->bday)->format('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-gender-ambiguous me-1"></i> Gender
                                        </label>
                                        <select name="gender" class="form-select form-select-lg">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('gender', $user->systemUser?->gender) == 'male' ? 'selected' : '' }}>
                                                <i class="bi bi-gender-male"></i> Male
                                            </option>
                                            <option value="female" {{ old('gender', $user->systemUser?->gender) == 'female' ? 'selected' : '' }}>
                                                <i class="bi bi-gender-female"></i> Female
                                            </option>
                                            <option value="other" {{ old('gender', $user->systemUser?->gender) == 'other' ? 'selected' : '' }}>
                                                <i class="bi bi-gender-ambiguous"></i> Other
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-house-door me-1"></i> Address Line 1
                                        </label>
                                        <input type="text" name="address1" class="form-control form-control-lg"
                                            value="{{ old('address1', $user->systemUser?->address1) }}"
                                            placeholder="Street address">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-building me-1"></i> Address Line 2
                                        </label>
                                        <input type="text" name="address2" class="form-control form-control-lg"
                                            value="{{ old('address2', $user->systemUser?->address2) }}" placeholder="City">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-geo-alt me-1"></i> Address Line 3
                                        </label>
                                        <input type="text" name="address3" class="form-control form-control-lg"
                                            value="{{ old('address3', $user->systemUser?->address3) }}"
                                            placeholder="Postal code / District">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="bi bi-pencil-square me-1"></i> Note
                                        </label>
                                        <textarea name="note" rows="3" class="form-control"
                                            placeholder="Additional notes...">{{ old('note', $user->systemUser?->note) }}</textarea>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-save">
                                    <i class="bi bi-save me-2"></i> Update Profile
                                </button>
                                <button type="reset" class="btn btn-reset">
                                    <i class="bi bi-arrow-repeat me-2"></i> Reset
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">

                {{-- Password Change Card --}}
                <div class="profile-card password-card">
                    <div class="profile-card-header">
                        <div class="header-icon password-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h4>Change Password</h4>
                            <p>Update your account password</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <form action="{{ route('admin.profile.password') }}" method="POST" id="passwordForm">
                            @csrf

                            <div class="form-group mb-3">
                                <label class="form-label">
                                    <i class="bi bi-lock me-1"></i> Current Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control form-control-lg @error('current_password') is-invalid @enderror"
                                        placeholder="Enter current password">
                                    <i class="bi bi-eye-slash toggle-password" data-target="current_password"></i>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">
                                    <i class="bi bi-key me-1"></i> New Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="password" id="new_password"
                                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                                        placeholder="Enter new password">
                                    <i class="bi bi-eye-slash toggle-password" data-target="new_password"></i>
                                </div>
                                <div class="password-strength mt-2">
                                    <div class="strength-bar"></div>
                                    <small class="strength-text"></small>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label">
                                    <i class="bi bi-check-circle me-1"></i> Confirm Password
                                </label>
                                <div class="password-input-wrapper">
                                    <input type="password" name="password_confirmation" id="confirm_password"
                                        class="form-control form-control-lg" placeholder="Confirm new password">
                                    <i class="bi bi-eye-slash toggle-password" data-target="confirm_password"></i>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-change-password w-100">
                                <i class="bi bi-arrow-repeat me-2"></i> Change Password
                            </button>

                        </form>
                    </div>
                </div>

                {{-- User Details Card --}}
                <div class="profile-card user-details-card mt-4">
                    <div class="profile-card-header">
                        <div class="header-icon details-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h4>User Details</h4>
                            <p>Account information summary</p>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <div class="details-list">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">User Type</span>
                                    <span class="detail-value">{{ $user->userType?->name ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="bi bi-toggle-on"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        @if($user->is_active)
                                            <span class="status-badge status-active">
                                                <i class="bi bi-check-circle-fill me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="status-badge status-inactive">
                                                <i class="bi bi-x-circle-fill me-1"></i> Inactive
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Member Since</span>
                                    <span class="detail-value">{{ $user->created_at?->format('d M Y') ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div class="detail-content">
                                    <span class="detail-label">Last Updated</span>
                                    <span class="detail-value">{{ $user->updated_at?->format('d M Y') ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection

@push('styles')
    <style>
        .profile-page {
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Cards */
        .profile-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .profile-card-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 55px;
            height: 55px;
            border-radius: 18px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.25);
        }

        .password-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 8px 16px rgba(245, 158, 11, 0.25);
        }

        .details-icon {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.25);
        }

        .profile-card-header h4 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .profile-card-header p {
            margin: 0;
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .profile-card-body {
            padding: 1.8rem;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 0.7rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .form-control-lg,
        .form-select-lg {
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Password Input */
        .password-input-wrapper {
            position: relative;
        }

        .password-input-wrapper input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #4f46e5;
        }

        /* Password Strength */
        .password-strength {
            margin-top: 8px;
        }

        .strength-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 4px;
            transition: all 0.3s ease;
            width: 0%;
        }

        .strength-text {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 4px;
            display: block;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .btn-save {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            color: white;
        }

        .btn-reset {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 0.7rem 1.8rem;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-reset:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .btn-change-password {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-change-password:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
            color: white;
        }

        /* Details List */
        .details-list {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-size: 1.1rem;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            display: block;
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .detail-value {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.8rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #059669;
        }

        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-card-header {
                padding: 1.2rem;
            }

            .profile-card-body {
                padding: 1.2rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-save,
            .btn-reset {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);

                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('bi-eye-slash');
                    this.classList.add('bi-eye');
                } else {
                    input.type = 'password';
                    this.classList.remove('bi-eye');
                    this.classList.add('bi-eye-slash');
                }
            });
        });

        // Password strength checker
        const newPassword = document.getElementById('new_password');
        if (newPassword) {
            newPassword.addEventListener('input', function () {
                const password = this.value;
                const strengthBar = document.querySelector('.strength-bar');
                const strengthText = document.querySelector('.strength-text');

                let strength = 0;
                let message = '';
                let color = '#e2e8f0';

                if (password.length > 0) {
                    if (password.length >= 8) strength++;
                    if (password.match(/[a-z]+/)) strength++;
                    if (password.match(/[A-Z]+/)) strength++;
                    if (password.match(/[0-9]+/)) strength++;
                    if (password.match(/[$@#&!]+/)) strength++;

                    const percentage = (strength / 5) * 100;
                    strengthBar.style.width = percentage + '%';

                    if (percentage <= 20) {
                        message = 'Very Weak';
                        color = '#ef4444';
                    } else if (percentage <= 40) {
                        message = 'Weak';
                        color = '#f59e0b';
                    } else if (percentage <= 60) {
                        message = 'Fair';
                        color = '#3b82f6';
                    } else if (percentage <= 80) {
                        message = 'Good';
                        color = '#10b981';
                    } else {
                        message = 'Strong';
                        color = '#059669';
                    }

                    strengthBar.style.backgroundColor = color;
                    strengthText.textContent = message;
                    strengthText.style.color = color;
                } else {
                    strengthBar.style.width = '0%';
                    strengthText.textContent = '';
                }
            });
        }

        // Confirm password match
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function () {
                const password = document.getElementById('new_password').value;
                const confirm = this.value;

                if (confirm.length > 0) {
                    if (password === confirm) {
                        this.style.borderColor = '#10b981';
                    } else {
                        this.style.borderColor = '#ef4444';
                    }
                } else {
                    this.style.borderColor = '#e2e8f0';
                }
            });
        }

        // Form reset handler
        document.querySelector('.btn-reset')?.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('profileForm')?.reset();
        });
    </script>
@endpush