@extends('layouts.app')

@section('title','Create Organizer')
@section('page-title','Create Organizer')

@section('content')
<div class="card border-0 shadow-sm rounded-4">

    <div class="card-header bg-white d-flex justify-content-between">
        <h5 class="fw-bold">Create Organizer</h5>
        <a href="{{ route('admin.organizers.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card-body">

        @include('admin.organizers.partials.form',[
            'organizer'=>null,
            'buttonText'=>'Save Organizer'
        ])

    </div>
</div>
@endsection