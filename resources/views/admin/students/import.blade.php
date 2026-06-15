@extends('layouts.app')

@section('title', 'Import Bulk Students')
@section('page-title', 'Import Bulk Students')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item active">Students</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Import Students (CSV / Excel)</h4>
    </div>

    <div class="card-body">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Import Errors --}}
        @if(session('importErrors'))
            <div class="alert alert-danger">
                <strong>Import Errors:</strong>
                <ul class="mb-0">
                    @foreach(session('importErrors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Select File</label>
                <input type="file" 
                       name="file" 
                       class="form-control" 
                       accept=".csv,.xlsx,.xls" 
                       required>
                <small class="text-muted">
                    Allowed formats: CSV, XLSX, XLS
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                Import Students
            </button>

            <a href="{{ asset('student_import_template_with_dummy_data.xlsx') }}" 
               class="btn btn-secondary">
                Download Sample Template
            </a>
        </form>

    </div>
</div>

@endsection