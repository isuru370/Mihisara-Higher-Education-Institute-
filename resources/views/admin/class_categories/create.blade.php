@extends('layouts.app')

@section('title', 'Create Class Category')
@section('page-title', 'Create Class Category')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Create Class Category</h5>
        <a href="{{ route('admin.class-categories.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card-body">
        @include('admin.class_categories.partials.form', [
            'category' => null,
            'buttonText' => 'Save Category'
        ])
    </div>
</div>
@endsection