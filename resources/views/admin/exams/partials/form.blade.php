@php
    $isEdit = isset($exam) && $exam;
    $formAction = $isEdit ? route('admin.exams.update', $exam->id) : route('admin.exams.store');
    $formMethod = $isEdit ? 'PUT' : 'POST';

    // Get old values or exam values
    $oldTitle = old('title', $exam->title ?? '');
    $oldClassId = old('student_class_id', $exam->student_class_id ?? '');
    $oldCategoryId = old('class_category_id', $exam->class_category_id ?? '');
    $oldHallId = old('class_hall_id', $exam->class_hall_id ?? '');
    $oldExamDate = old('exam_date', isset($exam->exam_date) ? $exam->exam_date->format('Y-m-d') : '');
    $oldStartTime = old(
        'start_time',
        isset($exam->start_time) ? \Carbon\Carbon::parse($exam->start_time)->format('H:i') : '',
    );
    $oldEndTime = old('end_time', isset($exam->end_time) ? \Carbon\Carbon::parse($exam->end_time)->format('H:i') : '');
    $oldNote = old('note', $exam->note ?? '');
    $oldStatus = old('status', $exam->status ?? 'scheduled');

    // Get today's date for min date validation
    $today = now()->format('Y-m-d');

    // Get selected class name for summary
    $selectedClassName = 'Not set';
    if ($oldClassId && isset($classes)) {
        $selectedClass = $classes->firstWhere('id', $oldClassId);
        $selectedClassName = $selectedClass ? $selectedClass->class_name : 'Not set';
    }

    // Get selected hall name for summary
    $selectedHallName = 'Not set';
    if ($oldHallId && isset($halls)) {
        $selectedHall = $halls->firstWhere('id', $oldHallId);
        $selectedHallName = $selectedHall ? $selectedHall->hall_name : 'Not set';
    }

    // Get selected category name for summary
    $selectedCategoryName = 'Not set';
    if ($oldCategoryId && isset($categories)) {
        $selectedCategory = $categories->firstWhere('id', $oldCategoryId);
        $selectedCategoryName = $selectedCategory ? $selectedCategory->category_name : 'Not set';
    }

    // Format time for summary
    $formattedTime = 'Not set';
    if ($oldStartTime && $oldEndTime) {
        $formattedTime = date('h:i A', strtotime($oldStartTime)) . ' - ' . date('h:i A', strtotime($oldEndTime));
    }

    // Format date for summary
    $formattedDate = 'Not set';
    if ($oldExamDate) {
        $formattedDate = date('d M Y', strtotime($oldExamDate));
    }
@endphp

<div class="exam-form-wrapper">
    <div class="form-card">
        <!-- Header -->
        <div class="form-card-header">
            <div class="header-left">
                <div class="header-icon">
                    <i class="bi bi-{{ $isEdit ? 'pencil-square' : 'plus-circle' }}"></i>
                </div>
                <div>
                    <h4>{{ $isEdit ? 'Edit Exam' : 'Create New Exam' }}</h4>
                    <p>{{ $isEdit ? 'Update exam details and schedule' : 'Add a new exam to the system' }}</p>
                </div>
            </div>
            <div class="header-right">
                <span class="status-badge {{ $isEdit ? 'bg-primary' : 'bg-success' }}">
                    <i class="bi bi-{{ $isEdit ? 'pencil' : 'plus' }}"></i>
                    {{ $isEdit ? 'Editing' : 'Creating' }}
                </span>
            </div>
        </div>

        <div class="form-card-body">
            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li><i class="bi bi-dot"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Flash Messages -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
                    <div class="alert-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="alert-content">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ $formAction }}" method="POST" id="examForm" novalidate>
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Basic Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="bi bi-info-circle"></i>
                                <h6>Basic Information</h6>
                            </div>

                            <div class="row g-3">
                                <!-- Exam Title - Floating Label -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" name="title" id="title"
                                            class="form-control @error('title') is-invalid @enderror"
                                            value="{{ $oldTitle }}" placeholder="Enter exam title" required>
                                        <label for="title">
                                            <i class="bi bi-pencil"></i>
                                            Exam Title
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="invalid-feedback">
                                            @error('title')
                                                {{ $message }}
                                            @else
                                                Please enter an exam title.
                                            @enderror
                                        </div>
                                        <div class="form-text">
                                            <i class="bi bi-info-circle"></i>
                                            Enter a clear and descriptive title for the exam.
                                        </div>
                                    </div>
                                </div>

                                <!-- Note - Floating Label -->
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror"
                                            placeholder="Add notes about this exam" rows="3" style="height: 100px">{{ $oldNote }}</textarea>
                                        <label for="note">
                                            <i class="bi bi-sticky"></i>
                                            Additional Notes
                                        </label>
                                        @error('note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="bi bi-info-circle"></i>
                                            Optional: Add any special instructions or notes.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="bi bi-calendar-event"></i>
                                <h6>Exam Schedule</h6>
                            </div>

                            <div class="row g-3">
                                <!-- Exam Date - Floating Label -->
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="date" name="exam_date" id="exam_date"
                                            class="form-control @error('exam_date') is-invalid @enderror"
                                            value="{{ $oldExamDate }}" min="{{ $today }}" required>
                                        <label for="exam_date">
                                            <i class="bi bi-calendar"></i>
                                            Exam Date
                                            <span class="text-danger">*</span>
                                        </label>
                                        @error('exam_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">Please select a valid exam date.</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="bi bi-info-circle"></i>
                                            Select the date for this exam.
                                        </div>
                                    </div>
                                </div>

                                <!-- Start Time - Floating Label -->
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="time" name="start_time" id="start_time"
                                            class="form-control @error('start_time') is-invalid @enderror"
                                            value="{{ $oldStartTime }}" required>
                                        <label for="start_time">
                                            <i class="bi bi-clock"></i>
                                            Start Time
                                            <span class="text-danger">*</span>
                                        </label>
                                        @error('start_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">Please select a start time.</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="bi bi-info-circle"></i>
                                            When the exam begins.
                                        </div>
                                    </div>
                                </div>

                                <!-- End Time - Floating Label -->
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="time" name="end_time" id="end_time"
                                            class="form-control @error('end_time') is-invalid @enderror"
                                            value="{{ $oldEndTime }}" required>
                                        <label for="end_time">
                                            <i class="bi bi-clock"></i>
                                            End Time
                                            <span class="text-danger">*</span>
                                        </label>
                                        @error('end_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="invalid-feedback">Please select an end time.</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="bi bi-info-circle"></i>
                                            When the exam ends.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Time Validation Warning -->
                            <div id="timeWarning" class="alert alert-warning d-none mt-3">
                                <i class="bi bi-exclamation-triangle"></i>
                                <span id="timeWarningText">End time must be after start time.</span>
                            </div>
                        </div>

                        <!-- Assignment Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <i class="bi bi-diagram-3"></i>
                                <h6>Assignment Details</h6>
                            </div>

                            <div class="row g-3">
                                <!-- ✅ Class - NORMAL LABEL (No floating) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="student_class_id" class="form-label fw-semibold">
                                            <i class="bi bi-people text-primary"></i>
                                            Class
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="student_class_id" id="student_class_id"
                                            class="form-select form-select-lg @error('student_class_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select Class</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}"
                                                    {{ $oldClassId == $class->id ? 'selected' : '' }}>
                                                    {{ $class->class_name }}
                                                    @if ($class->grade)
                                                        ({{ $class->grade->grade_name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('student_class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                <i class="bi bi-info-circle"></i>
                                                Select the class for this exam.
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- ✅ Category - NORMAL LABEL (No floating) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_category_id" class="form-label fw-semibold">
                                            <i class="bi bi-tags text-primary"></i>
                                            Category
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="class_category_id" id="class_category_id"
                                            class="form-select form-select-lg @error('class_category_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select Category</option>
                                            @if ($oldCategoryId && isset($categories))
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ $oldCategoryId == $category->id ? 'selected' : '' }}>
                                                        {{ $category->category_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('class_category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                <i class="bi bi-info-circle"></i>
                                                Select the exam category.
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- ✅ Hall - NORMAL LABEL (No floating) -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="class_hall_id" class="form-label fw-semibold">
                                            <i class="bi bi-building text-primary"></i>
                                            Exam Hall
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select name="class_hall_id" id="class_hall_id"
                                            class="form-select form-select-lg @error('class_hall_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select Hall</option>
                                            @foreach ($halls as $hall)
                                                <option value="{{ $hall->id }}"
                                                    {{ $oldHallId == $hall->id ? 'selected' : '' }}>
                                                    {{ $hall->hall_name }}
                                                    @if ($hall->capacity)
                                                        (Capacity: {{ $hall->capacity }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('class_hall_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                <i class="bi bi-info-circle"></i>
                                                Select the hall for the exam.
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="col-lg-4">
                        <!-- Status Card (Edit Only) -->
                        @if ($isEdit)
                            <div class="form-section status-section">
                                <div class="section-header">
                                    <i class="bi bi-toggle-on"></i>
                                    <h6>Exam Status</h6>
                                </div>

                                <div class="status-select-wrapper">
                                    <select name="status"
                                        class="form-select form-select-lg @error('status') is-invalid @enderror"
                                        id="statusSelect">
                                        <option value="scheduled" {{ $oldStatus == 'scheduled' ? 'selected' : '' }}>
                                            <i class="bi bi-clock"></i> Scheduled
                                        </option>
                                        <option value="ongoing" {{ $oldStatus == 'ongoing' ? 'selected' : '' }}>
                                            <i class="bi bi-play-circle"></i> Ongoing
                                        </option>
                                        <option value="completed" {{ $oldStatus == 'completed' ? 'selected' : '' }}>
                                            <i class="bi bi-check-circle"></i> Completed
                                        </option>
                                        <option value="cancelled" {{ $oldStatus == 'cancelled' ? 'selected' : '' }}>
                                            <i class="bi bi-x-circle"></i> Cancelled
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <!-- Status Info -->
                                    <div class="status-info mt-3">
                                        <div class="status-indicator" id="statusIndicator">
                                            <span
                                                class="badge bg-{{ $oldStatus == 'scheduled' ? 'primary' : ($oldStatus == 'ongoing' ? 'warning' : ($oldStatus == 'completed' ? 'success' : 'danger')) }}">
                                                {{ ucfirst($oldStatus) }}
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            <span id="statusDescription">
                                                @switch($oldStatus)
                                                    @case('scheduled')
                                                        Exam is planned and waiting to start.
                                                    @break

                                                    @case('ongoing')
                                                        Exam is currently in progress.
                                                    @break

                                                    @case('completed')
                                                        Exam has been finished.
                                                    @break

                                                    @case('cancelled')
                                                        Exam has been cancelled.
                                                    @break
                                                @endswitch
                                            </span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Summary Card -->
                        <div class="form-section summary-section">
                            <div class="section-header">
                                <i class="bi bi-list-check"></i>
                                <h6>Exam Summary</h6>
                            </div>

                            <div class="summary-content">
                                <div class="summary-item">
                                    <span class="summary-label">
                                        <i class="bi bi-pencil"></i> Title
                                    </span>
                                    <span class="summary-value" id="summaryTitle">
                                        {{ $oldTitle ?: 'Not set' }}
                                    </span>
                                </div>

                                <div class="summary-item">
                                    <span class="summary-label">
                                        <i class="bi bi-calendar"></i> Date
                                    </span>
                                    <span class="summary-value" id="summaryDate">
                                        {{ $formattedDate }}
                                    </span>
                                </div>

                                <div class="summary-item">
                                    <span class="summary-label">
                                        <i class="bi bi-clock"></i> Time
                                    </span>
                                    <span class="summary-value" id="summaryTime">
                                        {{ $formattedTime }}
                                    </span>
                                </div>

                                <div class="summary-item">
                                    <span class="summary-label">
                                        <i class="bi bi-people"></i> Class
                                    </span>
                                    <span class="summary-value" id="summaryClass">
                                        {{ $selectedClassName }}
                                    </span>
                                </div>

                                <div class="summary-item">
                                    <span class="summary-label">
                                        <i class="bi bi-tags"></i> Category
                                    </span>
                                    <span class="summary-value" id="summaryCategory">
                                        {{ $selectedCategoryName }}
                                    </span>
                                </div>

                                <div class="summary-item">
                                    <span class="summary-label">
                                        <i class="bi bi-building"></i> Hall
                                    </span>
                                    <span class="summary-value" id="summaryHall">
                                        {{ $selectedHallName }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="form-section actions-section">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg custom-submit-btn">
                                    <i class="bi bi-{{ $isEdit ? 'check-circle' : 'plus-circle' }}"></i>
                                    {{ $isEdit ? 'Update Exam' : 'Create Exam' }}
                                </button>
                                <a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i>
                                    Cancel & Go Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
    <style>
        /* ============================================ */
        /* FORM WRAPPER                                */
        /* ============================================ */
        .exam-form-wrapper {
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================ */
        /* FORM CARD                                   */
        /* ============================================ */
        .form-card {
            background: white;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }

        .form-card-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .header-left h4 {
            margin: 0;
            font-weight: 700;
            color: #0f172a;
        }

        .header-left p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bg-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .bg-success {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .form-card-body {
            padding: 2rem;
        }

        /* ============================================ */
        /* FORM SECTIONS                               */
        /* ============================================ */
        .form-section {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .form-section:hover {
            border-color: #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .section-header i {
            font-size: 1.25rem;
            color: #2563eb;
        }

        .section-header h6 {
            margin: 0;
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
        }

        /* ============================================ */
        /* FORM FLOATING - For Input & Textarea only    */
        /* ============================================ */
        .form-floating {
            position: relative;
        }

        .form-floating>.form-control {
            height: 58px;
            padding: 1rem 0.75rem;
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-floating>.form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
        }

        .form-floating>.form-control.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .form-floating>.form-control.is-valid {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .form-floating>label {
            padding: 1rem 0.75rem;
            color: #64748b;
            font-weight: 500;
        }

        .form-floating>label i {
            margin-right: 0.5rem;
        }

        .form-floating>textarea.form-control {
            height: auto;
            min-height: 100px;
        }

        /* ============================================ */
        /* ✅ FORM GROUP - For Select Fields Only       */
        /* ============================================ */
        .form-group {
            margin-bottom: 0;
        }

        .form-group .form-label {
            font-size: 0.85rem;
            color: #1e293b;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .form-group .form-label i {
            font-size: 0.95rem;
        }

        .form-group .form-select {
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            padding: 0.7rem 1rem;
            background: #fafbfc;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            min-height: 52px;
            cursor: pointer;
        }

        .form-group .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
        }

        .form-group .form-select.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .form-group .form-select.is-valid {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .form-group .form-select.form-select-lg {
            min-height: 56px;
            font-size: 0.95rem;
        }

        .form-group .form-text {
            margin-top: 0.3rem;
            font-size: 0.78rem;
            color: #94a3b8;
        }

        .form-group .form-text i {
            margin-right: 0.25rem;
        }

        .form-group .invalid-feedback {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        /* ============================================ */
        /* ALERTS                                      */
        /* ============================================ */
        .custom-alert {
            border-radius: 16px;
            border: none;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .custom-alert .alert-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .custom-alert .alert-content {
            flex: 1;
        }

        .custom-alert ul {
            padding-left: 0;
            list-style: none;
        }

        .custom-alert ul li {
            padding: 0.15rem 0;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid #10b981;
        }

        /* ============================================ */
        /* STATUS SECTION                              */
        /* ============================================ */
        .status-section {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
        }

        .status-select-wrapper select {
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            padding: 0.75rem 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 56px;
            font-size: 0.95rem;
            background: #fafbfc;
        }

        .status-select-wrapper select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
        }

        .status-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: white;
            border-radius: 12px;
            border: 1px solid #eef2f7;
        }

        .status-indicator .badge {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* ============================================ */
        /* SUMMARY SECTION                             */
        /* ============================================ */
        .summary-section {
            background: linear-gradient(135deg, #f0f9ff, #ffffff);
            border-color: #dbeafe;
        }

        .summary-content {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0.8rem;
            background: white;
            border-radius: 10px;
            border: 1px solid #eef2f7;
            transition: all 0.2s ease;
        }

        .summary-item:hover {
            border-color: #dbeafe;
            background: #fafbfc;
            transform: translateX(2px);
        }

        .summary-label {
            font-weight: 600;
            color: #475569;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .summary-label i {
            font-size: 0.85rem;
            color: #2563eb;
        }

        .summary-value {
            color: #0f172a;
            font-weight: 600;
            font-size: 0.82rem;
            text-align: right;
            max-width: 55%;
            word-break: break-word;
        }

        /* ============================================ */
        /* BUTTONS                                     */
        /* ============================================ */
        .custom-submit-btn {
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
        }

        .custom-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }

        .custom-submit-btn:active {
            transform: translateY(0);
        }

        .btn-outline-secondary {
            border-radius: 14px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border: 2px solid #e2e8f0;
            color: #475569;
            transition: all 0.3s ease;
        }

        .btn-outline-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        /* ============================================ */
        /* RESPONSIVE                                  */
        /* ============================================ */
        @media (max-width: 992px) {
            .form-card-header {
                padding: 1.25rem;
                flex-direction: column;
                align-items: flex-start;
            }

            .form-card-body {
                padding: 1.25rem;
            }

            .form-section {
                padding: 1.25rem;
            }
        }

        @media (max-width: 768px) {
            .form-card-header {
                padding: 1rem;
            }

            .header-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .header-left h4 {
                font-size: 1.1rem;
            }

            .form-card-body {
                padding: 1rem;
            }

            .form-section {
                padding: 1rem;
            }

            .form-floating>.form-control {
                height: 52px;
                font-size: 0.9rem;
            }

            .form-group .form-select {
                min-height: 48px;
                font-size: 0.9rem;
            }

            .form-group .form-select.form-select-lg {
                min-height: 50px;
            }

            .summary-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .summary-value {
                max-width: 100%;
                text-align: left;
            }
        }

        @media (max-width: 576px) {
            .form-card {
                border-radius: 20px;
            }

            .form-card-header {
                padding: 0.75rem;
            }

            .form-card-body {
                padding: 0.75rem;
            }

            .form-section {
                padding: 0.75rem;
                border-radius: 16px;
            }

            .section-header {
                margin-bottom: 1rem;
            }

            .section-header h6 {
                font-size: 0.85rem;
            }

            .custom-submit-btn {
                font-size: 0.95rem;
                padding: 0.6rem 1.25rem;
            }
        }

        /* ============================================ */
        /* LOADING STATE                               */
        /* ============================================ */
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            top: 50%;
            left: 50%;
            margin: -12px 0 0 -12px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            'use strict';

            // ============================================
            // 1. CLASS CATEGORY DYNAMIC LOADING
            // ============================================
            const selectedCategoryId = "{{ $oldCategoryId }}";

            const classCategories = {
                @foreach ($classes as $class)
                    {{ $class->id }}: [
                        @foreach ($class->categoryFees as $fee)
                            {
                                id: {{ $fee->category->id }},
                                name: "{{ $fee->category->category_name }}"
                            },
                        @endforeach
                    ],
                @endforeach
            };

            const classSelect = document.getElementById('student_class_id');
            const categorySelect = document.getElementById('class_category_id');

            function loadCategories(classId) {
                categorySelect.innerHTML = '<option value="">Select Category</option>';

                if (!classId || !classCategories[classId] || classCategories[classId].length === 0) {
                    categorySelect.innerHTML += '<option value="" disabled>No categories available</option>';
                    return;
                }

                classCategories[classId].forEach(function(category) {
                    const selected = category.id == selectedCategoryId ? 'selected' : '';
                    categorySelect.innerHTML += `
                        <option value="${category.id}" ${selected}>
                            ${category.name}
                        </option>
                    `;
                });
            }

            classSelect.addEventListener('change', function() {
                loadCategories(this.value);
                updateSummary();
            });

            // Initial load
            if (classSelect.value) {
                loadCategories(classSelect.value);
            }

            // ============================================
            // 2. REAL-TIME VALIDATION
            // ============================================
            const form = document.getElementById('examForm');
            const inputs = form.querySelectorAll('input, select, textarea');

            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });

                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid') || this.classList.contains(
                        'is-valid')) {
                        validateField(this);
                    }
                    updateSummary();
                });

                input.addEventListener('change', function() {
                    validateField(this);
                    updateSummary();
                });
            });

            function validateField(input) {
                const isValid = input.checkValidity();

                if (!isValid) {
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                } else {
                    input.classList.remove('is-invalid');
                    if (input.value.trim() !== '') {
                        input.classList.add('is-valid');
                    }
                }
            }

            // ============================================
            // 3. TIME VALIDATION
            // ============================================
            const startTime = document.getElementById('start_time');
            const endTime = document.getElementById('end_time');
            const timeWarning = document.getElementById('timeWarning');
            const timeWarningText = document.getElementById('timeWarningText');

            function validateTimes() {
                if (startTime.value && endTime.value) {
                    const start = startTime.value;
                    const end = endTime.value;

                    if (start >= end) {
                        timeWarning.classList.remove('d-none');
                        timeWarningText.textContent = 'End time must be after start time.';
                        endTime.classList.add('is-invalid');
                        return false;
                    } else {
                        timeWarning.classList.add('d-none');
                        endTime.classList.remove('is-invalid');
                        endTime.classList.add('is-valid');
                        return true;
                    }
                }
                return true;
            }

            startTime.addEventListener('change', validateTimes);
            endTime.addEventListener('change', validateTimes);
            startTime.addEventListener('input', validateTimes);
            endTime.addEventListener('input', validateTimes);

            // ============================================
            // 4. DATE VALIDATION
            // ============================================
            const examDate = document.getElementById('exam_date');
            const today = new Date().toISOString().split('T')[0];
            examDate.setAttribute('min', today);

            examDate.addEventListener('change', function() {
                if (this.value < today) {
                    this.classList.add('is-invalid');
                    this.setCustomValidity('Exam date cannot be in the past.');
                } else {
                    this.classList.remove('is-invalid');
                    this.setCustomValidity('');
                    this.classList.add('is-valid');
                }
                updateSummary();
            });

            // ============================================
            // 5. SUMMARY UPDATE
            // ============================================
            function updateSummary() {
                // Title
                const titleInput = document.getElementById('title');
                document.getElementById('summaryTitle').textContent = titleInput.value || 'Not set';

                // Date
                const dateInput = document.getElementById('exam_date');
                if (dateInput.value) {
                    const date = new Date(dateInput.value + 'T00:00:00');
                    document.getElementById('summaryDate').textContent = date.toLocaleDateString('en-US', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                } else {
                    document.getElementById('summaryDate').textContent = 'Not set';
                }

                // Time
                const start = document.getElementById('start_time');
                const end = document.getElementById('end_time');
                if (start.value && end.value) {
                    const startFormatted = formatTime(start.value);
                    const endFormatted = formatTime(end.value);
                    document.getElementById('summaryTime').textContent = `${startFormatted} - ${endFormatted}`;
                } else {
                    document.getElementById('summaryTime').textContent = 'Not set';
                }

                // Class
                const classSelect = document.getElementById('student_class_id');
                if (classSelect.value) {
                    const selectedOption = classSelect.options[classSelect.selectedIndex];
                    document.getElementById('summaryClass').textContent = selectedOption.text.split('(')[0].trim();
                } else {
                    document.getElementById('summaryClass').textContent = 'Not set';
                }

                // Category
                const categorySelect = document.getElementById('class_category_id');
                if (categorySelect.value) {
                    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                    document.getElementById('summaryCategory').textContent = selectedOption.text.trim();
                } else {
                    document.getElementById('summaryCategory').textContent = 'Not set';
                }

                // Hall
                const hallSelect = document.getElementById('class_hall_id');
                if (hallSelect.value) {
                    const selectedOption = hallSelect.options[hallSelect.selectedIndex];
                    document.getElementById('summaryHall').textContent = selectedOption.text.split('(')[0].trim();
                } else {
                    document.getElementById('summaryHall').textContent = 'Not set';
                }
            }

            function formatTime(time) {
                const [hours, minutes] = time.split(':');
                const h = parseInt(hours);
                const ampm = h >= 12 ? 'PM' : 'AM';
                const h12 = h % 12 || 12;
                return `${h12}:${minutes} ${ampm}`;
            }

            // Initial summary update
            updateSummary();

            // ============================================
            // 6. STATUS SELECTOR WITH VISUAL FEEDBACK
            // ============================================
            const statusSelect = document.getElementById('statusSelect');
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    const status = this.value;
                    const indicator = document.getElementById('statusIndicator');
                    const description = document.getElementById('statusDescription');

                    const statusMap = {
                        'scheduled': {
                            color: 'primary',
                            text: 'Exam is planned and waiting to start.'
                        },
                        'ongoing': {
                            color: 'warning',
                            text: 'Exam is currently in progress.'
                        },
                        'completed': {
                            color: 'success',
                            text: 'Exam has been finished.'
                        },
                        'cancelled': {
                            color: 'danger',
                            text: 'Exam has been cancelled.'
                        }
                    };

                    const info = statusMap[status] || statusMap['scheduled'];

                    indicator.innerHTML =
                        `<span class="badge bg-${info.color}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
                    description.textContent = info.text;

                    Swal.fire({
                        icon: 'info',
                        title: 'Status Updated',
                        text: `Exam status changed to ${status.charAt(0).toUpperCase() + status.slice(1)}`,
                        timer: 1500,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                });
            }

            // ============================================
            // 7. FORM SUBMISSION WITH VALIDATION
            // ============================================
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                let isValid = true;
                const allInputs = this.querySelectorAll(
                    'input[required], select[required], textarea[required]');

                allInputs.forEach(input => {
                    if (!input.checkValidity()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    }
                });

                if (!validateTimes()) {
                    isValid = false;
                }

                if (examDate.value && examDate.value < today) {
                    examDate.classList.add('is-invalid');
                    isValid = false;
                }

                if (!isValid) {
                    const firstError = this.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        firstError.focus();
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fix all errors before submitting.',
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    return;
                }

                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;

                this.submit();
            });

            // ============================================
            // 8. KEYBOARD SHORTCUTS
            // ============================================
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });

            // ============================================
            // 9. CONFIRM ON LEAVE
            // ============================================
            let formChanged = false;
            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.addEventListener('change', function() {
                    formChanged = true;
                });
                input.addEventListener('input', function() {
                    formChanged = true;
                });
            });

            window.addEventListener('beforeunload', function(e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                }
            });

            form.addEventListener('submit', function() {
                formChanged = false;
            });

            // ============================================
            // 10. TOOLTIP INITIALIZATION
            // ============================================
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(el) {
                return new bootstrap.Tooltip(el);
            });

            console.log('✅ Exam form initialized successfully');
        });
    </script>
@endpush