@extends('layouts.app')

@section('title', 'Edit Exam')
@section('page-title', 'Edit Exam')

@section('content')
    @include('admin.exams.partials.form', [
        'exam' => $exam,
        'classes' => $classes,
        'halls' => $halls,
    ])
@endsection
