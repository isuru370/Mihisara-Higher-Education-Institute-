@extends('layouts.app')

@section('title', 'Class Category Details')
@section('page-title', 'Class Category Details')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">{{ $classCategory->category_name }}</h5>
            <small class="text-muted">{{ $classCategory->code }}</small>
        </div>

        <div>
            <a href="{{ route('admin.class-categories.edit', $classCategory) }}" class="btn btn-warning btn-sm">Edit</a>
            <a href="{{ route('admin.class-categories.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-md-12">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-3">Category Information</h6>

                    <p><strong>Name:</strong> {{ $classCategory->category_name }}</p>
                    <p><strong>Code:</strong> {{ $classCategory->code }}</p>

                    <p>
                        <strong>Schedulable:</strong>
                        <span class="badge {{ $classCategory->is_schedulable ? 'bg-info text-dark' : 'bg-light text-dark border' }}">
                            {{ $classCategory->is_schedulable ? 'Yes' : 'No' }}
                        </span>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <span class="badge {{ $classCategory->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $classCategory->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>

                    <p>
                        <strong>Created At:</strong>
                        {{ $classCategory->created_at?->format('Y-m-d h:i A') }}
                    </p>

                    <p class="mb-0">
                        <strong>Updated At:</strong>
                        {{ $classCategory->updated_at?->format('Y-m-d h:i A') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection