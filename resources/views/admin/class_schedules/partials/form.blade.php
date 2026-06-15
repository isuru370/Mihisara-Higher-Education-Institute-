@php
    $scheduleData = $classSchedule ?? null;
    $isEdit = !empty($scheduleData);

    $currentScheduleType = $scheduleType ?? request('type', 'recurring');

    $formAction = $isEdit
        ? route('admin.class-schedules.update', $scheduleData)
        : route('admin.class-schedules.store');

    $selectedClassValue = old(
        'student_class_id',
        $selectedClassId ?? optional($scheduleData)->student_class_id
    );

    $selectedCategoryValue = old(
        'class_category_id',
        $selectedCategoryId ?? optional($scheduleData)->class_category_id
    );

    $selectedHallValue = old(
        'class_hall_id',
        optional($scheduleData)->class_hall_id
    );

    $classDateValue = '';
    if (!empty(optional($scheduleData)->class_date)) {
        $classDateValue = \Carbon\Carbon::parse(optional($scheduleData)->class_date)->format('Y-m-d');
    }

    $startDateValue = old('start_date', '');
    $endDateValue = old('end_date', '');

    $startTimeValue = '';
    if (!empty(optional($scheduleData)->start_time)) {
        $startTimeValue = \Carbon\Carbon::parse(optional($scheduleData)->start_time)->format('H:i');
    }

    $endTimeValue = '';
    if (!empty(optional($scheduleData)->end_time)) {
        $endTimeValue = \Carbon\Carbon::parse(optional($scheduleData)->end_time)->format('H:i');
    }

    $statusValue = old('status', optional($scheduleData)->status ?? 'scheduled');

    $isActiveValue = old(
        'is_active',
        $isEdit ? (optional($scheduleData)->is_active ?? true) : true
    );

    $cancelReasonValue = old('cancel_reason', optional($scheduleData)->cancel_reason ?? '');
    $noteValue = old('note', optional($scheduleData)->note ?? '');
@endphp

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4 custom-alert">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $formAction }}">
    @csrf

    @if($isEdit)
        @method('PUT')
    @else
        <input type="hidden" name="schedule_type" value="{{ $currentScheduleType }}">
    @endif

    <div class="row g-4">

        <div class="col-12">
            <div class="section-card p-4">
                <div class="section-header">
                    <div>
                        <h6 class="mb-1 fw-bold">
                            @if($isEdit)
                                Schedule Information
                            @elseif($currentScheduleType === 'single')
                                Single Day Schedule Information
                            @else
                                Recurring Schedule Information
                            @endif
                        </h6>

                        <small class="text-muted">
                            Fill the schedule details carefully before saving
                        </small>
                    </div>

                    @if(!$isEdit)
                        <span class="badge {{ $currentScheduleType === 'single' ? 'bg-success' : 'bg-primary' }} custom-badge">
                            {{ ucfirst($currentScheduleType) }}
                        </span>
                    @endif
                </div>

                <div class="row g-3">

                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">Class *</label>

                        <input type="hidden" name="student_class_id" value="{{ $selectedClassValue }}">

                        <div class="selected-box">
                            @if(!empty($selectedClass))
                                <div class="fw-bold">{{ $selectedClass->class_name }}</div>
                                <div class="text-muted small mt-1">
                                    Grade: {{ optional($selectedClass->grade)->grade_name ?? 'N/A' }} |
                                    Subject: {{ optional($selectedClass->subject)->subject_name ?? 'N/A' }}
                                </div>
                                <div class="text-muted small">
                                    Teacher: {{ optional($selectedClass->teacher)->full_name ?? 'N/A' }}
                                </div>
                            @else
                                <div class="text-muted">No class selected</div>
                            @endif
                        </div>

                        @error('student_class_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">Fee Category *</label>

                        <input type="hidden" name="class_category_fee_id" value="{{ $selectedCategoryFeeId ?? request('class_category_fee_id') }}">

                        <div class="selected-box">
                            @if(!empty($selectedCategory))
                                <div class="fw-bold">{{ $selectedCategory->category_name }}</div>
                                <div class="text-muted small mt-1">
                                    @if(!empty($selectedCategory->code))
                                        Code: {{ $selectedCategory->code }}
                                    @else
                                        No code
                                    @endif
                                </div>
                            @else
                                <div class="text-muted">No category selected</div>
                            @endif
                        </div>

                        @error('class_category_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">Hall</label>

                        <select name="class_hall_id"
                                class="form-select custom-input @error('class_hall_id') is-invalid @enderror">
                            <option value="">No Hall / Not selected</option>

                            @foreach($halls as $hall)
                                <option value="{{ $hall->id }}"
                                    {{ (string) $selectedHallValue === (string) $hall->id ? 'selected="selected"' : '' }}>
                                    {{ $hall->hall_name }}
                                    @if(!empty($hall->code))
                                        - {{ $hall->code }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('class_hall_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($isEdit)
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold">Class Date *</label>

                            <input type="date"
                                   name="class_date"
                                   class="form-control custom-input @error('class_date') is-invalid @enderror"
                                   value="{{ $classDateValue }}"
                                   required>

                            @error('class_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        @if($currentScheduleType === 'single')
                            <div class="col-lg-6">
                                <label class="form-label fw-semibold">Class Date *</label>

                                <input type="date"
                                       name="class_date"
                                       class="form-control custom-input @error('class_date') is-invalid @enderror"
                                       value="{{ old('class_date') }}"
                                       required>

                                @error('class_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="col-lg-4">
                                <label class="form-label fw-semibold">Start Date *</label>

                                <input type="date"
                                       name="start_date"
                                       class="form-control custom-input @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', $startDateValue) }}"
                                       required>

                                @error('start_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label fw-semibold">End Date *</label>

                                <input type="date"
                                       name="end_date"
                                       class="form-control custom-input @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date', $endDateValue) }}"
                                       required>

                                @error('end_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-4">
                                <label class="form-label fw-semibold">Class Day *</label>

                                <select name="class_day"
                                        class="form-select custom-input @error('class_day') is-invalid @enderror"
                                        required>
                                    <option value="">Select Day</option>

                                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                        <option value="{{ $day }}" {{ old('class_day') === $day ? 'selected="selected"' : '' }}>
                                            {{ ucfirst($day) }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('class_day')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    @endif

                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">Start Time *</label>

                        <input type="time"
                               name="start_time"
                               class="form-control custom-input @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time', $startTimeValue) }}"
                               required>

                        @error('start_time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label fw-semibold">End Time *</label>

                        <input type="time"
                               name="end_time"
                               class="form-control custom-input @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time', $endTimeValue) }}"
                               required>

                        @error('end_time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($isEdit)
                        <div class="col-lg-6">
                            <label class="form-label fw-semibold">Status *</label>

                            <select name="status"
                                    class="form-select custom-input @error('status') is-invalid @enderror"
                                    required>
                                @foreach(['scheduled', 'ongoing', 'completed', 'cancelled'] as $status)
                                    <option value="{{ $status }}" {{ $statusValue === $status ? 'selected="selected"' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-lg-6">
                        <label class="form-label fw-semibold d-block">Active</label>
                        <input type="hidden" name="is_active" value="0">

                        <div class="form-check form-switch mt-2">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   id="is_active"
                                   class="form-check-input"
                                   {{ $isActiveValue ? 'checked="checked"' : '' }}>
                            <label for="is_active" class="form-check-label fw-semibold">
                                Active
                            </label>
                        </div>
                    </div>

                    @if($isEdit)
                        <div class="col-12">
                            <label class="form-label fw-semibold">Cancel Reason</label>

                            <textarea name="cancel_reason"
                                      class="form-control custom-input @error('cancel_reason') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Reason if status is cancelled">{{ $cancelReasonValue }}</textarea>

                            <small class="text-muted d-block mt-1">
                                This is used only when status is set to cancelled.
                            </small>

                            @error('cancel_reason')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label fw-semibold">Note</label>

                        <textarea name="note"
                                  class="form-control custom-input @error('note') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Optional note">{{ $noteValue }}</textarea>

                        @error('note')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
            <a href="{{ route('admin.class-schedules.index') }}"
               class="btn btn-outline-secondary custom-btn">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary custom-btn">
                {{ $buttonText ?? 'Save' }}
            </button>
        </div>

    </div>
</form>

@push('styles')
<style>
    .section-card {
        background: #fff;
        border-radius: 28px;
        border: 1px solid #e8eef5;
        box-shadow: 0 14px 35px rgba(15, 23, 42, .06);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eef2f7;
        flex-wrap: wrap;
    }

    .selected-box {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        border: 1px solid #dbeafe;
        border-left: 4px solid #2563eb;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        min-height: 96px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(37, 99, 235, .05);
    }

    .custom-input {
        border-radius: 14px !important;
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

    .custom-alert {
        border-radius: 18px;
    }

    .custom-btn {
        border-radius: 14px;
        padding: .75rem 1.15rem;
        font-weight: 600;
        transition: .2s ease;
    }

    .custom-btn:hover {
        transform: translateY(-2px);
    }

    .custom-badge {
        border-radius: 999px;
        padding: .55rem .9rem;
        font-size: .78rem;
        font-weight: 700;
    }

    @media (max-width: 768px) {
        .section-header {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endpush