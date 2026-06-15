@php
    $systemUser = $systemUser ?? null;
@endphp

<div class="row g-4">

    <div class="col-lg-4">
        <label class="form-label">Custom ID</label>
        <input type="text" class="form-control custom-input readonly-box"
            value="{{ old('custom_id', $systemUser?->custom_id ?? 'Auto generated') }}" readonly>
    </div>

    <div class="col-lg-4">
        <label class="form-label">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="full_name" class="form-control custom-input @error('full_name') is-invalid @enderror"
            value="{{ old('full_name', $systemUser?->full_name ?? '') }}" required>
        @error('full_name')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4">
        <label class="form-label">Mobile <span class="text-danger">*</span></label>
        <input type="text" name="mobile" class="form-control custom-input @error('mobile') is-invalid @enderror"
            value="{{ old('mobile', $systemUser?->mobile ?? '') }}" required>
        @error('mobile')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4">
        <label class="form-label">NIC <span class="text-danger">*</span></label>
        <input type="text" name="nic" class="form-control custom-input @error('nic') is-invalid @enderror"
            value="{{ old('nic', $systemUser?->nic ?? '') }}" required>
        @error('nic')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4">
        <label class="form-label">Birthday</label>
        <input type="date" name="bday" class="form-control custom-input @error('bday') is-invalid @enderror"
            value="{{ old('bday', $systemUser?->bday?->format('Y-m-d') ?? '') }}">
        @error('bday')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>


    <div class="col-md-4">
        <label class="form-label fw-semibold">Gender *</label>
        <select name="gender" class="form-select custom-input" required>
            <option value="">Select Gender</option>
            <option value="male" {{ old('gender', $systemUser?->gender ?? '') == 'male' ? 'selected' : '' }}>Male
            </option>
            <option value="female" {{ old('gender', $systemUser?->gender ?? '') == 'female' ? 'selected' : '' }}>
                Female</option>
            <option value="other" {{ old('gender', $systemUser?->gender ?? '') == 'other' ? 'selected' : '' }}>
                Other</option>
        </select>
    </div>

    <div class="col-lg-4">
        <label class="form-label">Address 1 <span class="text-danger">*</span></label>
        <input type="text" name="address1" class="form-control custom-input @error('address1') is-invalid @enderror"
            value="{{ old('address1', $systemUser?->address1 ?? '') }}" required>
        @error('address1')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4">
        <label class="form-label">Address 2</label>
        <input type="text" name="address2" class="form-control custom-input @error('address2') is-invalid @enderror"
            value="{{ old('address2', $systemUser?->address2 ?? '') }}">
        @error('address2')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4">
        <label class="form-label">Address 3</label>
        <input type="text" name="address3" class="form-control custom-input @error('address3') is-invalid @enderror"
            value="{{ old('address3', $systemUser?->address3 ?? '') }}">
        @error('address3')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control custom-input @error('email') is-invalid @enderror"
            value="{{ old('email', $systemUser?->user?->email ?? '') }}" required>
        @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4">
        <label class="form-label">
            User Type <span class="text-danger">*</span>
        </label>

        <select name="user_type_id" class="form-select custom-input @error('user_type_id') is-invalid @enderror"
            required>

            <option value="">Select User Type</option>

            @foreach ($userTypes as $userType)
                <option value="{{ $userType->id }}" {{ old('user_type_id', $systemUser?->user?->user_type_id ?? '') == $userType->id ? 'selected' : '' }}>
                    {{ $userType->name }}
                </option>
            @endforeach

        </select>

        @error('user_type_id')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label">
            Password
            @if(isset($systemUser))
                <small class="text-muted">(leave blank to keep current password)</small>
            @endif
        </label>
        <input type="password" name="password" class="form-control custom-input @error('password') is-invalid @enderror"
            @if(!isset($systemUser)) required @endif>
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Note</label>
        <textarea name="note" rows="4"
            class="form-control custom-input @error('note') is-invalid @enderror">{{ old('note', $systemUser?->note ?? '') }}</textarea>
        @error('note')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="custom-switch">
            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                @checked(old('is_active', $systemUser?->is_active ?? true))>
            <label for="is_active">Active Account</label>
        </div>
    </div>

</div>

@push('styles')
    <style>
        .custom-input {
            min-height: 52px;
            border-radius: 16px !important;
            border: 1px solid #e2e8f0;
            box-shadow: none !important;
            background: #fff;
        }

        .custom-input:focus {
            border-color: #93c5fd !important;
            box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .08) !important;
        }

        .readonly-box {
            background: #f8fafc;
            color: #64748b;
        }

        .custom-switch {
            display: flex;
            align-items: center;
            gap: .7rem;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 16px;
            padding: 1rem 1.2rem;
        }

        .custom-switch label {
            font-weight: 600;
            margin: 0;
        }
    </style>
@endpush