@extends('layouts.app')

@section('title', 'Category Students')
@section('page-title', 'Category Students')

@section('content')

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">{{ $studentClass->class_name }}</h5>
                <small class="text-muted">
                    Grade: {{ optional($studentClass->grade)->grade_name ?? '-' }}
                    | Subject: {{ optional($studentClass->subject)->subject_name ?? '-' }}
                    | Teacher: {{ optional($studentClass->teacher)->initials ?? '-' }}
                    | Category: {{ $classCategory->category_name }}
                </small>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.student-class-enrollments.categoryStudentsPdf', [$studentClass->id, $classCategory->id] + request()->query()) }}"
                    class="btn btn-danger btn-sm">
                    PDF
                </a>

                <a href="{{ route('admin.student-class-enrollments.categoryStudentsExcel', [$studentClass->id, $classCategory->id] + request()->query()) }}"
                    class="btn btn-success btn-sm">
                    Excel
                </a>

                <a href="{{ route('admin.student-class-enrollments.index') }}" class="btn btn-outline-secondary btn-sm">
                    Back
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="GET"
                action="{{ route('admin.student-class-enrollments.categoryStudents', [$studentClass->id, $classCategory->id]) }}"
                class="row g-2 mb-4">

                <div class="col-md-8">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search Student ID / QR / Initial Name / Full Name / Mobile..."
                        value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <select name="per_page" class="form-select">
                        <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button class="btn btn-primary w-100" type="submit">
                        Search
                    </button>
                </div>

                <div class="col-md-1">
                    <a href="{{ route('admin.student-class-enrollments.categoryStudents', [$studentClass->id, $classCategory->id]) }}"
                        class="btn btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Contact</th>
                            <th>QR Details</th>
                            <th>Fee Details</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($enrollments as $key => $enrollment)
                            @php
                                $student = $enrollment->student;

                                $studentCode = '-';

                                if (
                                    $student &&
                                    $student->permanent_qr_active &&
                                    !empty($student->custom_id) &&
                                    $student->custom_id != '0'
                                ) {
                                    $studentCode = $student->custom_id;
                                    $qrType = 'Permanent QR';
                                    $qrBadge = 'bg-success';
                                } else {
                                    $studentCode = $student->temporary_qr_code ?? '-';
                                    $qrType = 'Temporary QR';
                                    $qrBadge = 'bg-warning text-dark';
                                }

                                if ($enrollment->is_free_card) {
                                    $feeType = 'Free Card';
                                    $feeBadge = 'bg-dark';
                                } elseif (!is_null($enrollment->custom_fee)) {
                                    $feeType = 'Custom Fee';
                                    $feeBadge = 'bg-warning text-dark';
                                } else {
                                    $feeType = 'Default Fee';
                                    $feeBadge = 'bg-info text-dark';
                                }
                            @endphp

                            <tr>
                                <td>
                                    {{ $enrollments->firstItem() + $key }}
                                </td>

                                <td>
                                    <div class="fw-bold">
                                        {{ $student->full_name ?? '-' }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $student->initial_name ?? '-' }}
                                    </small>
                                </td>

                                <td>
                                    <div>{{ $student->mobile ?? '-' }}</div>
                                </td>

                                <td>
                                    <strong>{{ $studentCode }}</strong>
                                </td>

                                <td>
                                    <span class="badge {{ $feeBadge }} mb-1">
                                        {{ $feeType }}
                                    </span>
                                    <br>
                                    <strong>{{ number_format($enrollment->final_fee, 2) }}</strong>

                                    @if(!is_null($enrollment->custom_fee))
                                        <br>
                                        <small class="text-muted">
                                            Custom: {{ number_format($enrollment->custom_fee, 2) }}
                                        </small>
                                    @endif

                                    @if($enrollment->discount_percentage)
                                        <br>
                                        <small class="text-muted">
                                            Discount: {{ $enrollment->discount_percentage }}%
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if($enrollment->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                        @if($enrollment->left_at)
                                            <br>
                                            <small class="text-muted">
                                                Left: {{ $enrollment->left_at->format('Y-m-d') }}
                                            </small>
                                        @endif
                                    @endif
                                </td>

                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        <a href="{{ route('admin.student-class-enrollments.edit', $enrollment) }}"
                                            class="btn btn-sm btn-outline-warning">
                                            ✏️
                                        </a>

                                        <form method="POST"
                                            action="{{ route('admin.student-class-enrollments.toggleActive', $enrollment) }}"
                                            onsubmit="return confirm('Change enrollment status?')">
                                            @csrf
                                            @method('PATCH')

                                            @if($enrollment->is_active)
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    ⏸
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    ▶
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    No students found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $enrollments->links() }}
            </div>
        </div>
    </div>

@endsection