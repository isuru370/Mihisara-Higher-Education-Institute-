@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h4>Enrollment Details</h4>

            <a href="{{ route('admin.student-class-enrollments.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <p><strong>Student:</strong> {{ $studentClassEnrollment->student?->full_name }}</p>
                <p><strong>Class:</strong> {{ $studentClassEnrollment->studentClass?->class_name }}</p>

                <p><strong>Category:</strong>
                    {{ $studentClassEnrollment->classCategoryFee?->category?->category_name ?? '-' }}</p>
                <p><strong>Default Fee:</strong>
                    {{ number_format($studentClassEnrollment->classCategoryFee?->fee ?? 0, 2) }}</p>

                <p><strong>Free Card:</strong> {{ $studentClassEnrollment->is_free_card ? 'Yes' : 'No' }}</p>
                <p><strong>Custom Fee:</strong> {{ $studentClassEnrollment->custom_fee ?? '-' }}</p>
                <p><strong>Discount:</strong> {{ $studentClassEnrollment->discount_percentage ?? 0 }}%</p>
                <p><strong>Final Fee:</strong> {{ number_format($studentClassEnrollment->final_fee, 2) }}</p>
                <p><strong>Paid Amount:</strong> {{ number_format($studentClassEnrollment->paid_amount, 2) }}</p>
                <p><strong>Balance:</strong> {{ number_format($studentClassEnrollment->balance, 2) }}</p>
                <p><strong>Payment Status:</strong> {{ ucfirst($studentClassEnrollment->payment_status) }}</p>
                <p><strong>Status:</strong> {{ $studentClassEnrollment->is_active ? 'Active' : 'Inactive' }}</p>
                <p><strong>Enrolled At:</strong> {{ $studentClassEnrollment->enrolled_at?->format('Y-m-d') }}</p>
                <p><strong>Left At:</strong> {{ $studentClassEnrollment->left_at?->format('Y-m-d') ?? '-' }}</p>
                <p><strong>Note:</strong> {{ $studentClassEnrollment->note ?? '-' }}</p>
            </div>
        </div>
    </div>
@endsection