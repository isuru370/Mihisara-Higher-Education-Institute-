@php
    $teacher = $teacher ?? null;
@endphp

<div class="row g-4">

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">Teacher Information</h6>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Full Name *</label>
                    <input type="text" name="full_name" class="form-control custom-input"
                        value="{{ old('full_name', $teacher->full_name ?? '') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Initials *</label>
                    <input type="text" name="initials" class="form-control custom-input"
                        value="{{ old('initials', $teacher->initials ?? '') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email *</label>
                    <input type="email" name="email" class="form-control custom-input"
                        value="{{ old('email', $teacher->email ?? '') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mobile *</label>
                    <input type="text" name="mobile" class="form-control custom-input"
                        value="{{ old('mobile', $teacher->mobile ?? '') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">NIC *</label>
                    <input type="text" name="nic" class="form-control custom-input"
                        value="{{ old('nic', $teacher->nic ?? '') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Birthday *</label>
                    <input type="date" name="bday" class="form-control custom-input"
                        value="{{ old('bday', !empty($teacher?->bday) ? \Carbon\Carbon::parse($teacher->bday)->format('Y-m-d') : '') }}"
                        required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Gender *</label>
                    <select name="gender" class="form-select custom-input" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $teacher->gender ?? '') == 'male' ? 'selected' : '' }}>Male
                        </option>
                        <option value="female" {{ old('gender', $teacher->gender ?? '') == 'female' ? 'selected' : '' }}>
                            Female</option>
                        <option value="other" {{ old('gender', $teacher->gender ?? '') == 'other' ? 'selected' : '' }}>
                            Other</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">Address Information</h6>

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Address Line 1 *</label>
                    <input type="text" name="address1" class="form-control custom-input"
                        value="{{ old('address1', $teacher->address1 ?? '') }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Address Line 2</label>
                    <input type="text" name="address2" class="form-control custom-input"
                        value="{{ old('address2', $teacher->address2 ?? '') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Address Line 3</label>
                    <input type="text" name="address3" class="form-control custom-input"
                        value="{{ old('address3', $teacher->address3 ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">Bank Information</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bank Branch</label>
                    <select name="bank_branch_id" class="form-select select2-bank-branch">
                        <option value="">Search Bank or Branch</option>

                        @foreach($bankBranches as $branch)
                            <option value="{{ $branch->id }}" {{ old('bank_branch_id', $teacher->bank_branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->bank->bank_name ?? '' }} - {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Account Number</label>
                    <input type="text" name="account_number" class="form-control custom-input"
                        value="{{ old('account_number', $teacher->account_number ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">Qualification Information</h6>

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Graduation Details</label>
                    <textarea name="graduation_details" class="form-control custom-input"
                        rows="3">{{ old('graduation_details', $teacher->graduation_details ?? '') }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Experience</label>
                    <textarea name="experience" class="form-control custom-input"
                        rows="3">{{ old('experience', $teacher->experience ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">Status</h6>

            <div class="form-check form-switch">

                <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                    @checked(old('is_active', $teacher->is_active ?? true))>

                <label for="is_active" class="form-check-label fw-semibold">
                    Active
                </label>

            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('admin.teachers.index') }}" class="btn btn-light border custom-btn">
            Cancel
        </a>
        <button type="submit" class="btn btn-primary custom-btn">
            {{ $buttonText }}
        </button>
    </div>

</div>