@extends('layouts.app')

@section('title', 'Create Class Hall')
@section('page-title', 'Create Class Hall')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Create Class Hall</h5>
        <a href="{{ route('admin.class-halls.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card-body">
        @include('admin.class_halls.partials.form', [
            'classHall' => null,
            'buttonText' => 'Save Hall'
        ])
    </div>
</div>
@endsection