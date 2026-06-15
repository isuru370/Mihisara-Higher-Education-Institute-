@php
    $student = $student ?? null;
    $isEdit = $isEdit ?? false;

    $latestAdmissionPayment = $latestAdmissionPayment ?? null;

    $showAdmissionFields = old('admission', !empty($latestAdmissionPayment) ? 1 : 0) == 1;
    $selectedAdmissionId = old('admission_id', $latestAdmissionPayment?->admission_id);
    $selectedAmount = old('amount', $latestAdmissionPayment?->amount);

    if (!empty($student?->img_url)) {
        $studentImage = \Illuminate\Support\Str::startsWith($student->img_url, 'http')
            ? $student->img_url
            : asset('storage/' . ltrim($student->img_url, '/'));
    } else {
        $studentImage = asset('images/default-student.png');
    }
@endphp

<div class="row g-4">

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">
                <i class="bi bi-qr-code me-2"></i> Temporary QR Information
            </h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Temporary QR Code <span class="text-danger">*</span>
                    </label>

                    <input type="text" name="temporary_qr_code"
                        class="form-control custom-input @error('temporary_qr_code') is-invalid @enderror"
                        value="{{ old('temporary_qr_code', $student->temporary_qr_code ?? '') }}"
                        placeholder="Example: TMP001" oninput="this.value = this.value.toUpperCase()" {{ $isEdit ? 'readonly="readonly"' : 'autofocus="autofocus"' }} required>

                    <small class="text-muted">Valid examples: TMP001, TMP010, TMP100, TMP1000</small>

                    @error('temporary_qr_code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">QR Expire Date</label>

                    <input type="text" class="form-control bg-light custom-input"
                        value="{{ $isEdit ? optional($student->temporary_qr_code_expire_date)->format('Y-m-d H:i') : 'Auto set by system: 2 months' }}"
                        readonly disabled>

                    <small class="text-muted">
                        {{ $isEdit ? 'Expire date is managed by system.' : 'This date will be generated automatically.' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">
                <i class="bi bi-image me-2"></i> Student Image
            </h6>

            <div class="row g-3 align-items-start">
                <div class="col-md-3">
                    <div class="image-preview-wrapper">
                        <img id="imagePreview" src="{{ $studentImage }}" alt="Student Image" class="image-preview"
                            onerror="this.src='{{ asset('storage/uploads/male.png') }}'">
                    </div>
                </div>

                <div class="col-md-9">
                    <label class="form-label fw-semibold">
                        {{ $isEdit ? 'Update Student Image' : 'Upload Student Image' }}
                        <small class="text-muted fw-normal">(Optional)</small>
                    </label>

                    <input type="file" name="image" id="imageInput"
                        class="form-control custom-input @error('image') is-invalid @enderror"
                        accept="image/jpeg,image/jpg,image/png,image/webp" capture="environment">

                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i> Allowed: JPG, JPEG, PNG, WEBP. Max: 5MB.
                    </small>

                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <button type="button" class="btn btn-outline-danger btn-sm mt-3" id="removeImageBtn">
                        <i class="bi bi-trash me-1"></i>
                        {{ $isEdit ? 'Reset to Default' : 'Remove' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">
                <i class="bi bi-person-badge me-2"></i> Student Information
            </h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name"
                        class="form-control custom-input @error('full_name') is-invalid @enderror"
                        value="{{ old('full_name', $student->full_name ?? '') }}" required
                        placeholder="Enter full name">
                    @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Initial Name <span class="text-danger">*</span></label>
                    <input type="text" name="initial_name"
                        class="form-control custom-input @error('initial_name') is-invalid @enderror"
                        value="{{ old('initial_name', $student->initial_name ?? '') }}" required
                        placeholder="e.g., J.D. Smith">
                    @error('initial_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mobile <span class="text-danger">*</span></label>
                    <input type="tel" name="mobile"
                        class="form-control custom-input @error('mobile') is-invalid @enderror"
                        value="{{ old('mobile', $student->mobile ?? '') }}" pattern="[0-9]{10,15}" required
                        placeholder="07XXXXXXXX">
                    @error('mobile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">WhatsApp Mobile <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="tel" name="whatsapp_mobile"
                        class="form-control custom-input @error('whatsapp_mobile') is-invalid @enderror"
                        value="{{ old('whatsapp_mobile', $student->whatsapp_mobile ?? '') }}" pattern="[0-9]{10,15}"
                        placeholder="07XXXXXXXX">
                    @error('whatsapp_mobile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="email" name="email"
                        class="form-control custom-input @error('email') is-invalid @enderror"
                        value="{{ old('email', $student->email ?? '') }}" placeholder="student@example.com">
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">NIC <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="text" name="nic" class="form-control custom-input @error('nic') is-invalid @enderror"
                        value="{{ old('nic', $student->nic ?? '') }}" placeholder="National ID">
                    @error('nic')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Birthday <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="date" name="bday" class="form-control custom-input @error('bday') is-invalid @enderror"
                        value="{{ old('bday', !empty($student?->bday) ? \Carbon\Carbon::parse($student->bday)->format('Y-m-d') : '') }}">
                    @error('bday')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">

                    <label class="form-label fw-semibold">
                        Gender <span class="text-danger">*</span>
                    </label>

                    <select name="gender" class="form-select custom-input @error('gender') is-invalid @enderror"
                        required>

                        <option value="">Select Gender</option>

                        <option value="male" {{ old('gender', $student->gender ?? '') === 'male' ? 'selected="selected"' : '' }}>
                            Male
                        </option>

                        <option value="female" {{ old('gender', $student->gender ?? '') === 'female' ? 'selected="selected"' : '' }}>
                            Female
                        </option>

                        <option value="other" {{ old('gender', $student->gender ?? '') === 'other' ? 'selected="selected"' : '' }}>
                            Other
                        </option>

                    </select>

                    <small class="text-muted d-block mt-1">
                        Used for profile and reports.
                    </small>

                    @error('gender')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror

                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">
                <i class="bi bi-house-door me-2"></i> Address Information
            </h6>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Address Line 1 <span class="text-danger">*</span></label>
                    <input type="text" name="address1"
                        class="form-control custom-input @error('address1') is-invalid @enderror"
                        value="{{ old('address1', $student->address1 ?? '') }}" required placeholder="Street address">
                    @error('address1')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Address Line 2 <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="text" name="address2"
                        class="form-control custom-input @error('address2') is-invalid @enderror"
                        value="{{ old('address2', $student->address2 ?? '') }}" placeholder="City / Town">
                    @error('address2')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Address Line 3 <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="text" name="address3"
                        class="form-control custom-input @error('address3') is-invalid @enderror"
                        value="{{ old('address3', $student->address3 ?? '') }}" placeholder="Postal code / District">
                    @error('address3')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">
                <i class="bi bi-shield-lock me-2"></i> Guardian Information
            </h6>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Guardian First Name <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="text" name="guardian_fname"
                        class="form-control custom-input @error('guardian_fname') is-invalid @enderror"
                        value="{{ old('guardian_fname', $student->guardian_fname ?? '') }}" placeholder="First name">
                    @error('guardian_fname')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Guardian Last Name <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="text" name="guardian_lname"
                        class="form-control custom-input @error('guardian_lname') is-invalid @enderror"
                        value="{{ old('guardian_lname', $student->guardian_lname ?? '') }}" placeholder="Last name">
                    @error('guardian_lname')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Guardian NIC <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="text" name="guardian_nic"
                        class="form-control custom-input @error('guardian_nic') is-invalid @enderror"
                        value="{{ old('guardian_nic', $student->guardian_nic ?? '') }}" placeholder="Guardian NIC">
                    @error('guardian_nic')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Guardian Mobile <span class="text-danger">*</span></label>
                    <input type="tel" name="guardian_mobile"
                        class="form-control custom-input @error('guardian_mobile') is-invalid @enderror"
                        value="{{ old('guardian_mobile', $student->guardian_mobile ?? '') }}" pattern="[0-9]{10,15}"
                        required placeholder="Guardian contact">
                    @error('guardian_mobile')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="section-card p-4">
            <h6 class="section-title">
                <i class="bi bi-book me-2"></i> Class Information
            </h6>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Grade <span class="text-danger">*</span>
                    </label>

                    <select name="grade_id" class="form-select custom-input @error('grade_id') is-invalid @enderror"
                        required>
                        <option value="">Select Grade</option>

                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" @if(old('grade_id', $student->grade_id ?? '') == $grade->id)
                            selected @endif>
                                {{ $grade->grade_name }}
                            </option>
                        @endforeach
                    </select>

                    <small class="text-muted d-block mt-1">
                        Select the student's class grade.
                    </small>

                    @error('grade_id')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Class Type <span class="text-danger">*</span>
                    </label>

                    <select name="class_type" class="form-select custom-input @error('class_type') is-invalid @enderror"
                        required>
                        <option value="offline" @if(old('class_type', $student->class_type ?? 'offline') === 'offline')
                        selected @endif>
                            Offline
                        </option>

                        <option value="online" @if(old('class_type', $student->class_type ?? '') === 'online') selected
                        @endif>
                            Online
                        </option>

                        <option value="hybrid" @if(old('class_type', $student->class_type ?? '') === 'hybrid') selected
                        @endif>
                            Hybrid
                        </option>
                    </select>

                    <small class="text-muted d-block mt-1">
                        Choose how this student attends classes.
                    </small>

                    @error('class_type')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Student School <small
                            class="text-muted fw-normal">(Optional)</small></label>
                    <input type="text" name="student_school"
                        class="form-control custom-input @error('student_school') is-invalid @enderror"
                        value="{{ old('student_school', $student->student_school ?? '') }}" placeholder="School name">
                    @error('student_school')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="admission" value="1" id="admission" {{ $showAdmissionFields ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold ms-2" for="admission">
                            Admission Paid
                        </label>
                    </div>
                </div>

                <div class="col-md-6 mt-3 {{ $showAdmissionFields ? '' : 'd-none' }}" id="admissionBox">
                    <label class="form-label fw-semibold">Select Admission</label>
                    <select name="admission_id" id="admissionSelect" class="form-select custom-input">
                        <option value="">Select Admission</option>
                        @foreach($admissions as $admission)
                            <option value="{{ $admission->id }}" data-amount="{{ $admission->amount }}" {{ (int) $selectedAdmissionId === (int) $admission->id ? 'selected' : '' }}>
                                {{ $admission->name }} - Rs. {{ number_format($admission->amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mt-3 {{ $showAdmissionFields ? '' : 'd-none' }}" id="paymentAmountBox">
                    <label class="form-label fw-semibold">Admission Payment Amount</label>
                    <input type="number" name="amount" id="amountInput" class="form-control custom-input" readonly
                        value="{{ $selectedAmount }}">
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary px-4 custom-btn">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary px-4 custom-btn">
            <i class="bi bi-save me-1"></i> {{ $buttonText ?? 'Save Student' }}
        </button>
    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const admissionCheckbox = document.getElementById('admission');
            const admissionBox = document.getElementById('admissionBox');
            const paymentAmountBox = document.getElementById('paymentAmountBox');

            console.log('checkbox exists:', admissionCheckbox);
            console.log('checked:', admissionCheckbox.checked);

            function toggleAdmissionFields() {

                const show = admissionCheckbox.checked;

                console.log('toggle show:', show);

                admissionBox.classList.toggle('d-none', !show);
                paymentAmountBox.classList.toggle('d-none', !show);
            }

            admissionCheckbox.addEventListener('change', toggleAdmissionFields);

            toggleAdmissionFields();
        });
    </script>
@endpush