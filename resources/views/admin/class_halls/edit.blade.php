@extends('layouts.app')

@section('title', 'Edit Class Hall')
@section('page-title', 'Edit Class Hall')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">Edit Class Hall</h5>
            <small class="text-muted">{{ $classHall->hall_name }}</small>
        </div>

        <a href="{{ route('admin.class-halls.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card-body">
        @include('admin.class_halls.partials.form', [
            'classHall' => $classHall,
            'buttonText' => 'Update Hall'
        ])
    </div>
</div>
@endsection