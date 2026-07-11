@extends('layouts.app')

@section('title', 'Bulk Schedule Update')
@section('page-title', 'Bulk Schedule Update')

@section('content')

<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">

            <ul class="mb-0">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>
    @endif


    {{-- Header --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body d-flex justify-content-between align-items-center">

            <div>

                <h3 class="fw-bold mb-1">
                    {{ $studentClass->class_name }}
                </h3>

                <div class="text-muted">

                    {{ optional($studentClass->subject)->subject_name }}
                    |
                    {{ optional($studentClass->grade)->grade_name }}
                    |
                    {{ optional($studentClass->teacher)->full_name }}

                </div>

            </div>

            <a href="{{ url()->previous() }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left"></i>
                Back

            </a>

        </div>

    </div>


    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                Bulk Update Future Scheduled Classes

            </h5>

        </div>


        <form method="POST"
              action="{{ route('admin.class-schedules.updateBulkSchedule',$pattern->id) }}">

            @csrf
            @method('PUT')

            {{-- Hidden Fields --}}
            <input type="hidden"
                   name="start_date"
                   value="{{ $pattern->start_date->format('Y-m-d') }}">

            <input type="hidden"
                   name="end_date"
                   value="{{ $pattern->end_date->format('Y-m-d') }}">

            <input type="hidden"
                   name="is_active"
                   value="{{ old('is_active', $pattern->is_active ? 1 : 0) }}">

            <div class="card-body">

                <div class="row">

                    {{-- Class --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            Class

                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ $studentClass->class_name }}"
                               readonly>

                    </div>


                    {{-- Category --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            Category

                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ optional($categoryFee->category)->category_name }}"
                               readonly>

                    </div>


                    {{-- Start Date --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            Start Date

                        </label>

                        <input type="date"
                               class="form-control"
                               value="{{ $pattern->start_date->format('Y-m-d') }}"
                               readonly>

                    </div>


                    {{-- End Date --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            End Date

                        </label>

                        <input type="date"
                               class="form-control"
                               value="{{ $pattern->end_date->format('Y-m-d') }}"
                               readonly>

                    </div>


                    {{-- Class Day --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            Class Day

                        </label>

                        <select name="class_day"
                                class="form-select @error('class_day') is-invalid @enderror">

                            @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)

                                <option value="{{ $day }}"
                                    {{ old('class_day',$pattern->class_day)==$day ? 'selected' : '' }}>

                                    {{ ucfirst($day) }}

                                </option>

                            @endforeach

                        </select>

                        @error('class_day')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>


                    {{-- Hall --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            Hall

                        </label>

                        <select name="class_hall_id"
                                class="form-select @error('class_hall_id') is-invalid @enderror">

                            <option value="">

                                Select Hall

                            </option>

                            @foreach($halls as $hall)

                                <option value="{{ $hall->id }}"
                                    {{ old('class_hall_id',$pattern->class_hall_id)==$hall->id ? 'selected' : '' }}>

                                    {{ $hall->hall_name }}

                                </option>

                            @endforeach

                        </select>

                        @error('class_hall_id')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>


                    {{-- Start Time --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            Start Time

                        </label>

                        <input type="time"
                               name="start_time"
                               class="form-control @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time',$pattern->start_time) }}">

                        @error('start_time')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>


                    {{-- End Time --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-bold">

                            End Time

                        </label>

                        <input type="time"
                               name="end_time"
                               class="form-control @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time',$pattern->end_time) }}">

                        @error('end_time')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>


                    {{-- Note --}}
                    <div class="col-md-12 mb-3">

                        <label class="form-label fw-bold">

                            Note

                        </label>

                        <textarea name="note"
                                  rows="4"
                                  class="form-control @error('note') is-invalid @enderror">{{ old('note',$pattern->note) }}</textarea>

                        @error('note')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                </div>

            </div>


            <div class="card-footer bg-white d-flex justify-content-between">

                <a href="{{ url()->previous() }}"
                   class="btn btn-secondary">

                    <i class="bi bi-arrow-left"></i>
                    Back

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    <i class="bi bi-save"></i>
                    Update Future Schedules

                </button>

            </div>

        </form>

    </div>

</div>

@endsection