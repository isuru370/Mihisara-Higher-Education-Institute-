@extends('layouts.app')

@section('title', 'Edit Class Category')
@section('page-title', 'Edit Class Category')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">Edit Class Category</h5>
            <small class="text-muted">{{ $classCategory->category_name }}</small>
        </div>

        <a href="{{ route('admin.class-categories.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card-body">
        @include('admin.class_categories.partials.form', [
            'category' => $classCategory,
            'buttonText' => 'Update Category'
        ])
    </div>
</div>
@endsection