@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Student</label>

        @if($enrollment->exists)
            <input type="hidden" name="student_id" value="{{ $enrollment->student_id }}">

            <input type="text" class="form-control"
                value="{{ $enrollment->student?->custom_id }} - {{ $enrollment->student?->initial_name }}" readonly>
        @else
            <select name="student_id" id="student_id" class="form-control" required>
                @if($enrollment->student)
                    <option value="{{ $enrollment->student_id }}" selected>
                        {{ $enrollment->student->custom_id }} - {{ $enrollment->student->initial_name }}
                    </option>
                @endif
            </select>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Class</label>

        @if($enrollment->exists)
            <input type="hidden" name="student_class_id" value="{{ $enrollment->student_class_id }}">

            <input type="text" class="form-control"
                value="{{ $enrollment->studentClass?->class_name }} | Grade: {{ $enrollment->studentClass?->grade?->grade_name ?? '-' }} | Subject: {{ $enrollment->studentClass?->subject?->subject_name ?? '-' }} | Teacher: {{ $enrollment->studentClass?->teacher?->initials ?? '-' }}"
                readonly>
        @else
            <select name="student_class_id" id="student_class_id" class="form-control" required>
                @if($enrollment->studentClass)
                    <option value="{{ $enrollment->student_class_id }}" selected>
                        {{ $enrollment->studentClass->class_name }}
                        | Grade: {{ $enrollment->studentClass->grade?->grade_name ?? '-' }}
                        | Subject: {{ $enrollment->studentClass->subject?->subject_name ?? '-' }}
                        | Teacher: {{ $enrollment->studentClass->teacher?->initials ?? '-' }}
                    </option>
                @endif
            </select>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Category</label>
        <select name="class_category_fee_id" id="class_category_fee_id" class="form-control" required>
            @if($enrollment->classCategoryFee)
                <option value="{{ $enrollment->class_category_fee_id }}" selected>
                    {{ $enrollment->classCategoryFee?->category?->category_name }}
                    - Rs. {{ number_format($enrollment->classCategoryFee?->fee ?? 0, 2) }}
                </option>
            @else
                <option value="">Select class first</option>
            @endif
        </select>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Default Fee</label>
        <input type="text" id="default_fee" class="form-control" readonly>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Free Card</label>
        <select name="is_free_card" id="is_free_card" class="form-control">
            <option value="0" {{ old('is_free_card', $enrollment->is_free_card) == 0 ? 'selected' : '' }}>No</option>
            <option value="1" {{ old('is_free_card', $enrollment->is_free_card) == 1 ? 'selected' : '' }}>Yes</option>
        </select>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Custom Fee</label>
        <input type="number" step="0.01" name="custom_fee" id="custom_fee" class="form-control"
            value="{{ old('custom_fee', $enrollment->custom_fee) }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Custom Fee Reason</label>

        <select name="custom_fee_reason" class="form-control">
            <option value="">Select Reason</option>

            <option value="Student enrolled in multiple categories" {{ old('custom_fee_reason', $enrollment->custom_fee_reason) == 'Student enrolled in multiple categories' ? 'selected' : '' }}>
                Student enrolled in multiple categories
            </option>

            <option value="Special Discount" {{ old('custom_fee_reason', $enrollment->custom_fee_reason) == 'Special Discount' ? 'selected' : '' }}>
                Special Discount
            </option>

            <option value="Scholarship" {{ old('custom_fee_reason', $enrollment->custom_fee_reason) == 'Scholarship' ? 'selected' : '' }}>
                Scholarship
            </option>

            <option value="Sibling Discount" {{ old('custom_fee_reason', $enrollment->custom_fee_reason) == 'Sibling Discount' ? 'selected' : '' }}>
                Sibling Discount
            </option>

            <option value="Staff Child" {{ old('custom_fee_reason', $enrollment->custom_fee_reason) == 'Staff Child' ? 'selected' : '' }}>
                Staff Child
            </option>

            <option value="Promotional" {{ old('custom_fee_reason', $enrollment->custom_fee_reason) == 'Promotional' ? 'selected' : '' }}>
                Promotional
            </option>

            <option value="Other" {{ old('custom_fee_reason', $enrollment->custom_fee_reason) == 'Other' ? 'selected' : '' }}>
                Other
            </option>
        </select>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Discount %</label>
        <input type="number" step="0.01" name="discount_percentage" id="discount_percentage" class="form-control"
            value="{{ old('discount_percentage', $enrollment->discount_percentage) }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Discount Reason</label>

        <select name="discount_reason" class="form-control">
            <option value="">Select Reason</option>

            <option value="Half Card" {{ old('discount_reason', $enrollment->discount_reason) == 'Half Card' ? 'selected' : '' }}>
                Half Card
            </option>

            <option value="Special Discount" {{ old('discount_reason', $enrollment->discount_reason) == 'Special Discount' ? 'selected' : '' }}>
                Special Discount
            </option>

            <option value="Scholarship" {{ old('discount_reason', $enrollment->discount_reason) == 'Scholarship' ? 'selected' : '' }}>
                Scholarship
            </option>

            <option value="Sibling Discount" {{ old('discount_reason', $enrollment->discount_reason) == 'Sibling Discount' ? 'selected' : '' }}>
                Sibling Discount
            </option>

            <option value="Staff Child" {{ old('discount_reason', $enrollment->discount_reason) == 'Staff Child' ? 'selected' : '' }}>
                Staff Child
            </option>

            <option value="Promotional" {{ old('discount_reason', $enrollment->discount_reason) == 'Promotional' ? 'selected' : '' }}>
                Promotional
            </option>

            <option value="Other" {{ old('discount_reason', $enrollment->discount_reason) == 'Other' ? 'selected' : '' }}>
                Other
            </option>
        </select>
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">Final Fee</label>
        <input type="text" id="final_fee" class="form-control" readonly>
    </div>

    @if($enrollment->exists)
        <div class="col-md-3 mb-3">
            <label class="form-label">Active Status</label>
            <select name="is_active" class="form-control">
                <option value="1" {{ old('is_active', $enrollment->is_active) == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $enrollment->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="col-md-3 mb-3">
            <label class="form-label">Left At</label>
            <input type="date" name="left_at" class="form-control"
                value="{{ old('left_at', optional($enrollment->left_at)->format('Y-m-d')) }}">
        </div>
    @endif

    <div class="col-md-12 mb-3">
        <label class="form-label">Note</label>
        <textarea name="note" class="form-control" rows="3">{{ old('note', $enrollment->note) }}</textarea>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        {{ $buttonText ?? 'Save' }}
    </button>

    <a href="{{ route('admin.student-class-enrollments.index') }}" class="btn btn-secondary">
        Cancel
    </a>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function loadCategories(classId, selectedFeeId = null) {
            $('#class_category_fee_id').html('<option value="">Loading...</option>');
            $('#default_fee').val('');
            $('#final_fee').val('');

            if (!classId) {
                $('#class_category_fee_id').html('<option value="">Select class first</option>');
                return;
            }

            $.get("{{ url('admin/class-category-fees/by-class') }}/" + classId, function (data) {
                $('#class_category_fee_id').html('<option value="">Select Category</option>');

                data.forEach(function (item) {
                    let selected = selectedFeeId == item.id ? 'selected' : '';

                    $('#class_category_fee_id').append(
                        `<option value="${item.id}" data-fee="${item.fee}" ${selected}>
                                            ${item.category_name} - Rs. ${item.fee}
                                        </option>`
                    );
                });

                let fee = $('#class_category_fee_id option:selected').data('fee') || 0;
                $('#default_fee').val(fee);
                calculateFinalFee();
            });
        }

        $('#class_category_fee_id').on('change', function () {
            let fee = $('#class_category_fee_id option:selected').data('fee') || 0;
            $('#default_fee').val(fee);
            calculateFinalFee();
        });

        $('#custom_fee, #discount_percentage, #is_free_card').on('input change', function () {
            calculateFinalFee();
        });

        function calculateFinalFee() {
            let isFree = $('#is_free_card').val() == '1';

            if (isFree) {
                $('#final_fee').val('0.00');
                return;
            }

            let defaultFee = parseFloat($('#default_fee').val()) || 0;
            let customFee = parseFloat($('#custom_fee').val());
            let baseFee = !isNaN(customFee) ? customFee : defaultFee;
            let discount = parseFloat($('#discount_percentage').val()) || 0;
            let finalFee = baseFee - (baseFee * discount / 100);

            $('#final_fee').val(finalFee.toFixed(2));
        }

        $(document).ready(function () {
            let selectedClassId = "{{ old('student_class_id', $enrollment->student_class_id) }}";
            let selectedFeeId = "{{ old('class_category_fee_id', $enrollment->class_category_fee_id) }}";

            @if(!$enrollment->exists)
                $('#student_id').select2({
                    placeholder: 'Search by Student ID, QR, Full Name, Initial Name',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('admin.students.search') }}",
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return { q: params.term || '' };
                        },
                        processResults: function (data) {
                            return { results: data };
                        }
                    }
                });

                $('#student_class_id').select2({
                    placeholder: 'Search class',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('admin.student-classes.search') }}",
                        dataType: 'json',
                        delay: 300,
                        data: function (params) {
                            return { q: params.term || '' };
                        },
                        processResults: function (data) {
                            return { results: data };
                        }
                    }
                });

                $('#student_class_id').on('change', function () {
                    loadCategories($(this).val());
                });
            @endif

                            if (selectedClassId) {
                loadCategories(selectedClassId, selectedFeeId);
            } else {
                calculateFinalFee();
            }
        });
    </script>
@endpush