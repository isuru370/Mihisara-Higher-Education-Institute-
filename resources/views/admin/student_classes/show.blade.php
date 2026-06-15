@extends('layouts.app')

@section('title', 'Class Details')
@section('page-title', 'Class Details')

@section('content')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">{{ $studentClass->class_name }}</h5>
                <small class="text-muted">{{ ucfirst($studentClass->class_type) }} / {{ $studentClass->medium }}</small>
            </div>

            <div>
                <a href="{{ route('admin.student-classes.edit', $studentClass) }}" class="btn btn-warning btn-sm">Edit</a>
                <a href="{{ route('admin.student-classes.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="fw-bold mb-3">Class Information</h6>

                        <p><strong>Class Name:</strong> {{ $studentClass->class_name }}</p>
                        <p><strong>Class Type:</strong> {{ ucfirst($studentClass->class_type) }}</p>
                        <p><strong>Medium:</strong> {{ $studentClass->medium }}</p>
                        <p><strong>Grade:</strong> {{ $studentClass->grade->grade_name ?? 'N/A' }}</p>
                        <p><strong>Subject:</strong> {{ $studentClass->subject->subject_name ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="border rounded-4 p-3 h-100">
                        <h6 class="fw-bold mb-3">Teacher Information</h6>

                        <p><strong>Teacher:</strong> {{ $studentClass->teacher->full_name ?? 'N/A' }}</p>
                        <p><strong>Teacher ID:</strong> {{ $studentClass->teacher->custom_id ?? 'N/A' }}</p>
                        <p><strong>Mobile:</strong> {{ $studentClass->teacher->mobile ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $studentClass->teacher->email ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="border rounded-4 p-3">
                        <h6 class="fw-bold mb-3">Payment Configuration</h6>

                        @if($studentClass->paymentConfig)
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Teacher %:</strong> {{ $studentClass->paymentConfig->teacher_percentage }}%</p>
                                </div>

                                <div class="col-md-4">
                                    <p><strong>Organizer %:</strong> {{ $studentClass->paymentConfig->organizer_percentage }}%
                                    </p>
                                </div>

                                <div class="col-md-4">
                                    <p><strong>Institution %:</strong>
                                        {{ $studentClass->paymentConfig->institution_percentage }}%</p>
                                </div>

                                <div class="col-md-4">
                                    <p><strong>Organizer:</strong> {{ $studentClass->paymentConfig->organizer->name ?? 'None' }}
                                    </p>
                                </div>

                                <div class="col-md-4">
                                    <p><strong>Effective From:</strong>
                                        {{ optional($studentClass->paymentConfig->effective_from)->format('Y-m-d') ?? 'N/A' }}
                                    </p>
                                </div>

                                <div class="col-md-4">
                                    <p><strong>Effective To:</strong>
                                        {{ optional($studentClass->paymentConfig->effective_to)->format('Y-m-d') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">Payment config not set.</p>
                        @endif
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="border rounded-4 p-3">
                        <h6 class="fw-bold mb-3">Status</h6>

                        <span class="badge {{ $studentClass->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $studentClass->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <span class="badge {{ $studentClass->is_ongoing ? 'bg-primary' : 'bg-light text-dark border' }}">
                            {{ $studentClass->is_ongoing ? 'Ongoing' : 'Not Ongoing' }}
                        </span>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-4">

                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">
                            Class Category Fees
                        </h5>
                    </div>

                    <div class="card-body">

                        @if($categoryFees->count())

                            <div class="table-responsive">

                                <table class="table table-bordered align-middle">

                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Category</th>
                                            <th>Code</th>
                                            <th>Fee</th>
                                            <th>Status</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($categoryFees as $fee)

                                            <tr>

                                                <td>
                                                    {{ $loop->iteration }}
                                                </td>

                                                <td>
                                                    @if($fee->category)
                                                        {{ $fee->category->category_name }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td>
                                                    @if($fee->category && $fee->category->code)
                                                        {{ $fee->category->code }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td>
                                                    {{ number_format($fee->fee, 2) }}
                                                </td>

                                                <td>

                                                    @if($fee->is_active)

                                                        <span class="badge bg-success">
                                                            Active
                                                        </span>

                                                    @else

                                                        <span class="badge bg-secondary">
                                                            Inactive
                                                        </span>

                                                    @endif

                                                </td>

                                                <td>
                                                    {{ $fee->note ? $fee->note : '-' }}
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        @else

                            <div class="alert alert-warning mb-0">
                                No category fees added for this class.
                            </div>

                        @endif

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection