@extends('layouts.app')

@section('title', 'Class Hall Details')
@section('page-title', 'Class Hall Details')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">{{ $classHall->hall_name }}</h5>
            <small class="text-muted">{{ $classHall->code }}</small>
        </div>

        <div>
            <a href="{{ route('admin.class-halls.edit', $classHall) }}" class="btn btn-warning btn-sm">Edit</a>
            <a href="{{ route('admin.class-halls.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-3">Hall Information</h6>

                    <p><strong>Code:</strong> {{ $classHall->code }}</p>
                    <p><strong>Hall Name:</strong> {{ $classHall->hall_name }}</p>
                    <p><strong>Hall Type:</strong> {{ $classHall->hall_type ?? '-' }}</p>

                    <p>
                        <strong>Price:</strong>
                        @if ((float) $classHall->hall_price == 0)
                            <span class="badge bg-success">Free</span>
                        @else
                            {{ number_format($classHall->hall_price, 2) }}
                        @endif
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <span class="badge {{ $classHall->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $classHall->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-3">System Information</h6>

                    <p>
                        <strong>Created At:</strong>
                        {{ $classHall->created_at?->format('Y-m-d h:i A') }}
                    </p>

                    <p>
                        <strong>Updated At:</strong>
                        {{ $classHall->updated_at?->format('Y-m-d h:i A') }}
                    </p>

                    <p class="mb-0">
                        <strong>Schedules Count:</strong>
                        {{ $classHall->schedules()->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection