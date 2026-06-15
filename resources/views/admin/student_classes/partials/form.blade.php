@php
    $studentClass = $studentClass ?? null;
    $paymentConfig = $paymentConfig ?? null;
    $isEdit = !empty($studentClass);

    $selectedClassType = old('class_type', $studentClass->class_type ?? 'offline');
    $selectedTeacherId = old('teacher_id', $studentClass->teacher_id ?? '');
    $selectedGradeId = old('grade_id', $studentClass->grade_id ?? '');
    $selectedSubjectId = old('subject_id', $studentClass->subject_id ?? '');
    $selectedOrganizerId = old('organizer_id', $paymentConfig->organizer_id ?? '');

    $selectedIsActive = old('is_active', $studentClass->is_active ?? true);
    $selectedIsOngoing = old('is_ongoing', $studentClass->is_ongoing ?? false);

    $effectiveFrom = old(
        'effective_from',
        !empty($paymentConfig?->effective_from)
            ? \Carbon\Carbon::parse($paymentConfig->effective_from)->format('Y-m-d')
            : ''
    );

    $effectiveTo = old(
        'effective_to',
        !empty($paymentConfig?->effective_to)
            ? \Carbon\Carbon::parse($paymentConfig->effective_to)->format('Y-m-d')
            : ''
    );
@endphp

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST"
      action="{{ $isEdit ? route('admin.student-classes.update', $studentClass) : route('admin.student-classes.store') }}">
    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">

        <div class="col-12">
            <div class="section-card p-4">
                <h6 class="section-title">
                    <i class="bi bi-journal-bookmark me-2"></i> Class Information
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Class Name *</label>
                        <input
                            type="text"
                            name="class_name"
                            class="form-control custom-input @error('class_name') is-invalid @enderror"
                            value="{{ old('class_name', $studentClass->class_name ?? '') }}"
                            required
                            placeholder="Enter class name">
                        @error('class_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Class Type *</label>
                        <select name="class_type" class="form-select custom-input @error('class_type') is-invalid @enderror" required>
                            <option value="offline" {{ $selectedClassType === 'offline' ? 'selected="selected"' : '' }}>Offline</option>
                            <option value="online" {{ $selectedClassType === 'online' ? 'selected="selected"' : '' }}>Online</option>
                            <option value="hybrid" {{ $selectedClassType === 'hybrid' ? 'selected="selected"' : '' }}>Hybrid</option>
                        </select>
                        @error('class_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Medium *</label>
                        <input
                            type="text"
                            name="medium"
                            class="form-control custom-input @error('medium') is-invalid @enderror"
                            value="{{ old('medium', $studentClass->medium ?? 'Sinhala') }}"
                            required
                            placeholder="Sinhala / English / Tamil">
                        @error('medium')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Teacher *</label>
                        <select name="teacher_id" class="form-select custom-input @error('teacher_id') is-invalid @enderror" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ $selectedTeacherId == $teacher->id ? 'selected="selected"' : '' }}>
                                    {{ $teacher->full_name }} - {{ $teacher->custom_id }}
                                </option>
                            @endforeach
                        </select>
                        @error('teacher_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Grade *</label>
                        <select name="grade_id" class="form-select custom-input @error('grade_id') is-invalid @enderror">
                            <option value="">Select Grade</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ $selectedGradeId == $grade->id ? 'selected="selected"' : '' }}>
                                    {{ $grade->grade_name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="mt-2">
                            <input
                                type="text"
                                name="new_grade"
                                class="form-control custom-input @error('new_grade') is-invalid @enderror"
                                value="{{ old('new_grade') }}"
                                placeholder="Or add new grade">
                            @error('new_grade')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @error('grade_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Subject *</label>
                        <select name="subject_id" class="form-select custom-input @error('subject_id') is-invalid @enderror">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ $selectedSubjectId == $subject->id ? 'selected="selected"' : '' }}>
                                    {{ $subject->subject_name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="mt-2">
                            <input
                                type="text"
                                name="new_subject"
                                class="form-control custom-input @error('new_subject') is-invalid @enderror"
                                value="{{ old('new_subject') }}"
                                placeholder="Or add new subject">
                            @error('new_subject')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        @error('subject_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="section-card p-4">
                <h6 class="section-title">
                    <i class="bi bi-cash-coin me-2"></i> Payment Configuration
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Teacher Percentage *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="teacher_percentage"
                            class="form-control custom-input @error('teacher_percentage') is-invalid @enderror"
                            value="{{ old('teacher_percentage', $paymentConfig->teacher_percentage ?? '') }}"
                            required>
                        @error('teacher_percentage')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Organizer Percentage</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="organizer_percentage"
                            class="form-control custom-input @error('organizer_percentage') is-invalid @enderror"
                            value="{{ old('organizer_percentage', $paymentConfig->organizer_percentage ?? 0) }}">
                        <small class="text-muted">If there is no organizer, put 0.</small>
                        @error('organizer_percentage')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Institution Percentage *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="institution_percentage"
                            class="form-control custom-input @error('institution_percentage') is-invalid @enderror"
                            value="{{ old('institution_percentage', $paymentConfig->institution_percentage ?? '') }}"
                            required>
                        @error('institution_percentage')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Organizer</label>
                        <select name="organizer_id" class="form-select custom-input @error('organizer_id') is-invalid @enderror">
                            <option value="">None</option>
                            @foreach($organizers as $organizer)
                                <option value="{{ $organizer->id }}" {{ $selectedOrganizerId == $organizer->id ? 'selected="selected"' : '' }}>
                                    {{ $organizer->name }} - {{ $organizer->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('organizer_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Effective From</label>
                        <input
                            type="date"
                            name="effective_from"
                            class="form-control custom-input @error('effective_from') is-invalid @enderror"
                            value="{{ $effectiveFrom }}">
                        @error('effective_from')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Effective To</label>
                        <input
                            type="date"
                            name="effective_to"
                            class="form-control custom-input @error('effective_to') is-invalid @enderror"
                            value="{{ $effectiveTo }}">
                        @error('effective_to')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="section-card p-4">
                <h6 class="section-title">
                    <i class="bi bi-toggle-on me-2"></i> Status
                </h6>

                <div class="d-flex gap-4 flex-wrap">
                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            id="is_active"
                            class="form-check-input"
                            {{ $selectedIsActive ? 'checked="checked"' : '' }}>
                        <label for="is_active" class="form-check-label fw-semibold">Active</label>
                    </div>

                    <div class="form-check form-switch">
                        <input
                            type="checkbox"
                            name="is_ongoing"
                            value="1"
                            disabled
                            id="is_ongoing"
                            class="form-check-input"
                            {{ $selectedIsOngoing ? 'checked="checked"' : '' }}>
                        <label for="is_ongoing" class="form-check-label fw-semibold">Ongoing Class</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 pt-2">
            <a href="{{ route('admin.student-classes.index') }}" class="btn btn-outline-secondary px-4 custom-btn">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary px-4 custom-btn">
                <i class="bi bi-save me-1"></i> {{ $buttonText ?? 'Save Class' }}
            </button>
        </div>

    </div>
</form>

@push('styles')
<style>
    .section-card {
        background: #fff;
        border-radius: 24px;
        border: 1px solid #eef2f7;
        box-shadow: 0 6px 20px rgba(0,0,0,.04);
    }

    .section-title {
        font-weight: 700;
        margin-bottom: 1.25rem;
        color: #0f172a;
        display: flex;
        align-items: center;
    }

    .section-title i {
        color: var(--primary, #2563eb);
    }

    .custom-input {
        border-radius: 14px;
        min-height: 48px;
        border: 1px solid #dbe3ec;
        box-shadow: none;
    }

    .custom-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,.10);
    }

    textarea.custom-input {
        min-height: 110px;
    }
</style>
@endpush