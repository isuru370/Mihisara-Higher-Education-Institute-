@extends('layouts.app')

@section('title', 'Assign Class to Student')
@section('page-title', 'Assign Class')

@section('content')

<div class="assign-class-page">

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- HEADER -->
        <div class="main-card-header">

            <div>
                <h4>Assign Class to Student</h4>
                <p>Enroll a student in a new class</p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.student-class-management.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

        </div>

        <!-- FORM -->
        <div class="form-container">

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    Please fix the following errors:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.student-class-management.assign') }}" method="POST" id="assignForm">
                @csrf

                <div class="row g-4">

                    <!-- Student Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="student_id" class="form-label fw-semibold">
                                Student <span class="text-danger">*</span>
                            </label>
                            <select name="student_id" id="student_id" class="form-select custom-input @error('student_id') is-invalid @enderror" required>
                                <option value="">-- Select Student --</option>
                                @foreach($students as $studentOption)
                                    <option value="{{ $studentOption->id }}" 
                                        {{ (old('student_id') == $studentOption->id || (isset($student) && $student->id == $studentOption->id)) ? 'selected' : '' }}>
                                        {{ $studentOption->custom_id }} - {{ $studentOption->full_name }}
                                        @if(!$studentOption->is_active) (Inactive) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Class Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="student_class_id" class="form-label fw-semibold">
                                Class <span class="text-danger">*</span>
                            </label>
                            <select name="student_class_id" id="student_class_id" class="form-select custom-input @error('student_class_id') is-invalid @enderror" required>
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('student_class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }} 
                                        ({{ $class->class_type }} - {{ $class->medium }})
                                        @if(!$class->is_active) - Inactive @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('student_class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Category Fee -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="class_category_fee_id" class="form-label fw-semibold">
                                Category Fee
                            </label>
                            <select name="class_category_fee_id" id="class_category_fee_id" class="form-select custom-input @error('class_category_fee_id') is-invalid @enderror">
                                <option value="">-- Default Fee --</option>
                            </select>
                            @error('class_category_fee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select a specific category fee or leave as default</small>
                        </div>
                    </div>

                    <!-- Status & Free Card -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_active" class="form-label fw-semibold">Status</label>
                            <select name="is_active" id="is_active" class="form-select custom-input @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_free_card" class="form-label fw-semibold">Free Card</label>
                            <select name="is_free_card" id="is_free_card" class="form-select custom-input @error('is_free_card') is-invalid @enderror">
                                <option value="0" {{ old('is_free_card', 0) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('is_free_card') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('is_free_card')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">If "Yes", no fee will be charged</small>
                        </div>
                    </div>

                    <!-- Custom Fee -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="custom_fee" class="form-label fw-semibold">Custom Fee (LKR)</label>
                            <input type="number" 
                                   name="custom_fee" 
                                   id="custom_fee" 
                                   class="form-control custom-input @error('custom_fee') is-invalid @enderror" 
                                   placeholder="Leave empty for default fee"
                                   value="{{ old('custom_fee') }}"
                                   step="0.01"
                                   min="0">
                            @error('custom_fee')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Set a custom fee for this enrollment</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="custom_fee_reason" class="form-label fw-semibold">Custom Fee Reason</label>
                            <input type="text" 
                                   name="custom_fee_reason" 
                                   id="custom_fee_reason" 
                                   class="form-control custom-input @error('custom_fee_reason') is-invalid @enderror" 
                                   placeholder="Reason for custom fee"
                                   value="{{ old('custom_fee_reason') }}">
                            @error('custom_fee_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_percentage" class="form-label fw-semibold">Discount Percentage (%)</label>
                            <input type="number" 
                                   name="discount_percentage" 
                                   id="discount_percentage" 
                                   class="form-control custom-input @error('discount_percentage') is-invalid @enderror" 
                                   placeholder="0-100"
                                   value="{{ old('discount_percentage') }}"
                                   min="0"
                                   max="100"
                                   step="0.01">
                            @error('discount_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter discount percentage (0-100)</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discount_reason" class="form-label fw-semibold">Discount Reason</label>
                            <input type="text" 
                                   name="discount_reason" 
                                   id="discount_reason" 
                                   class="form-control custom-input @error('discount_reason') is-invalid @enderror" 
                                   placeholder="Reason for discount"
                                   value="{{ old('discount_reason') }}">
                            @error('discount_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="note" class="form-label fw-semibold">Note</label>
                            <textarea name="note" 
                                      id="note" 
                                      class="form-control custom-input @error('note') is-invalid @enderror" 
                                      rows="3"
                                      placeholder="Additional notes about this enrollment">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary custom-btn btn-lg">
                            <i class="bi bi-check-lg"></i> Assign Class
                        </button>
                        <a href="{{ route('admin.student-class-management.index') }}" class="btn btn-light border custom-btn btn-lg ms-2">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                    </div>

                </div>
            </form>

        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    .assign-class-page {
        animation: fadeIn .4s ease;
    }

    .main-card {
        background: #fff;
        border-radius: 28px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
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

    .main-card-header p {
        margin: 0;
        color: #64748b;
    }

    .header-buttons {
        display: flex;
        gap: .7rem;
        flex-wrap: wrap;
    }

    .custom-btn {
        border-radius: 14px;
        padding: .7rem 1.2rem;
        font-weight: 600;
        border: none;
        transition: .2s ease;
    }

    .custom-btn:hover {
        transform: translateY(-2px);
    }

    .custom-input {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        min-height: 48px;
        padding: .6rem 1rem;
    }

    .custom-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
    }

    .form-group {
        margin-bottom: .5rem;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: .4rem;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media(max-width:768px) {
        .main-card-header {
            flex-direction: column;
            align-items: stretch;
        }
        .header-buttons {
            width: 100%;
        }
        .header-buttons a {
            flex: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Load category fees when class is selected
        $('#student_class_id').on('change', function() {
            const classId = this.value;
            const feeSelect = document.getElementById('class_category_fee_id');
            
            feeSelect.innerHTML = '<option value="">-- Default Fee --</option>';
            
            if (classId) {
                feeSelect.innerHTML = '<option value="">Loading fees...</option>';
                
                $.ajax({
                    url: `/admin/student-class-management/class/${classId}/category-fees`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            feeSelect.innerHTML = '<option value="">-- Default Fee --</option>';
                            response.data.forEach(fee => {
                                const option = document.createElement('option');
                                option.value = fee.id;
                                option.textContent = `${fee.category_name} - LKR ${parseFloat(fee.fee).toFixed(2)}`;
                                feeSelect.appendChild(option);
                            });
                        } else {
                            feeSelect.innerHTML = '<option value="">-- No Category Fees Available --</option>';
                        }
                    },
                    error: function() {
                        feeSelect.innerHTML = '<option value="">-- Error Loading Fees --</option>';
                    }
                });
            }
        });

        // Auto-populate student if provided
        @if(isset($student) && $student)
            $('#student_id').val('{{ $student->id }}').trigger('change');
        @endif

        // Form validation
        $('#assignForm').on('submit', function(e) {
            const studentId = $('#student_id').val();
            const classId = $('#student_class_id').val();
            
            if (!studentId) {
                e.preventDefault();
                alert('Please select a student.');
                return false;
            }
            
            if (!classId) {
                e.preventDefault();
                alert('Please select a class.');
                return false;
            }
            
            return true;
        });
    });
</script>
@endpush