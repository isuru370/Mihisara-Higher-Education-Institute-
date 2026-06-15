@php
    $feeData = $classCategoryFee ?? null;

    $selectedStudentClassId = old(
        'student_class_id',
        isset($selectedClassId)
        ? $selectedClassId
        : optional($feeData)->student_class_id
    );

    $selectedCategoryId = old(
        'class_category_id',
        optional($feeData)->class_category_id
    );

    $feeValue = old(
        'fee',
        optional($feeData)->fee ?? ''
    );

    $noteValue = old(
        'note',
        optional($feeData)->note ?? ''
    );

    $isActiveInput = old('is_active');

    if ($isActiveInput === null) {
        $isActiveInput = optional($feeData)->is_active ?? true;
    }

    $formAction = $feeData
        ? route('admin.class-category-fees.update', $feeData)
        : route('admin.class-category-fees.store');
@endphp

<form method="POST" action="{{ $formAction }}">
    @csrf

    @if($feeData)
        @method('PUT')
    @endif

    <div class="row g-4">

        <div class="col-12">
            <div class="section-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="section-title mb-0 fw-bold">
                        Class Category Fee Information
                    </h6>

                    @if($feeData)
                        <span class="badge bg-primary">Edit Mode</span>
                    @else
                        <span class="badge bg-success">Create Mode</span>
                    @endif
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Class *
                        </label>

                        <select name="student_class_id"
                            class="form-select custom-input @error('student_class_id') is-invalid @enderror" required>
                            <option value="">Select Class</option>

                            @foreach($classes as $class)
                                @php
                                    $gradeName = optional($class->grade)->grade_name ?? 'N/A';
                                    $subjectName = optional($class->subject)->subject_name ?? 'N/A';
                                @endphp

                                <option value="{{ $class->id }}" {{ (string) $selectedStudentClassId === (string) $class->id ? 'selected="selected"' : '' }}>
                                    {{ $class->class_name }} | Grade: {{ $gradeName }} | Subject: {{ $subjectName }}
                                </option>
                            @endforeach
                        </select>

                        @error('student_class_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Category *
                        </label>

                        <select name="class_category_id"
                            class="form-select custom-input @error('class_category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>

                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) $selectedCategoryId === (string) $category->id ? 'selected="selected"' : '' }}>
                                    {{ $category->category_name }}
                                    @if(!empty($category->code))
                                        - {{ $category->code }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('class_category_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Fee *
                        </label>

                        <input type="number" name="fee"
                            class="form-control custom-input @error('fee') is-invalid @enderror" value="{{ $feeValue }}"
                            min="0" step="0.01" placeholder="1700.00" required>

                        @error('fee')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">
                            Status
                        </label>

                        <input type="hidden" name="is_active" value="0">

                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                                @checked($isActiveInput)>
                            <label for="is_active" class="form-check-label fw-semibold">
                                Active
                            </label>
                        </div>

                        @error('is_active')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Note
                        </label>

                        <textarea name="note" class="form-control custom-input @error('note') is-invalid @enderror"
                            rows="3" placeholder="Optional note">{{ $noteValue }}</textarea>

                        @error('note')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
            <a href="{{ route('admin.class-category-fees.index') }}" class="btn btn-outline-secondary custom-btn">
                Cancel
            </a>

            <button type="button" class="btn btn-outline-secondary custom-btn" disabled aria-disabled="true">
                Future Button
            </button>

            <button type="submit" class="btn btn-primary custom-btn">
                {{ $buttonText ?? 'Save Fee' }}
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
            box-shadow: 0 6px 20px rgba(0, 0, 0, .04);
            padding: 1.25rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .custom-input {
            border-radius: 14px !important;
            min-height: 48px;
            border: 1px solid #dbe3ec;
            box-shadow: none;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        textarea.custom-input {
            min-height: 110px;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover:not(:disabled) {
            transform: translateY(-2px);
        }

        .custom-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .section-card .d-flex {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
@endpush