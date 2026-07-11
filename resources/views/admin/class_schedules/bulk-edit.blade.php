@extends('layouts.app')

@section('title', 'Bulk Update Schedule')
@section('page-title', 'Bulk Update Schedule')

@section('content')

    <div class="container-fluid">

        {{-- Header --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <div>
                        <h4 class="fw-bold mb-1">
                            {{ $studentClass->class_name }}
                        </h4>

                        <div class="text-muted">

                            <span class="me-3">
                                <strong>Teacher :</strong>
                                {{ optional($studentClass->teacher)->full_name }}
                            </span>

                            <span class="me-3">
                                <strong>Subject :</strong>
                                {{ optional($studentClass->subject)->subject_name }}
                            </span>

                            <span class="me-3">
                                <strong>Grade :</strong>
                                {{ optional($studentClass->grade)->grade_name }}
                            </span>

                        </div>

                    </div>

                    <a href="{{ route('admin.class-schedules.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                </div>

            </div>
        </div>


        {{-- Category --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6">

                        <h6 class="text-muted">
                            Category
                        </h6>

                        <h5>
                            {{ optional($categoryFee->category)->category_name }}
                        </h5>

                    </div>

                    <div class="col-md-6">

                        <h6 class="text-muted">
                            Fee
                        </h6>

                        <h5>
                            Rs. {{ number_format($categoryFee->fee, 2) }}
                        </h5>

                    </div>

                </div>

            </div>

        </div>


        {{-- Pattern List --}}
        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    Schedule Patterns
                </h5>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Day</th>

                            <th>Time</th>

                            <th>Hall</th>

                            <th>Start Date</th>

                            <th>End Date</th>

                            <th width="120">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($patterns as $pattern)
                            <tr>

                                <td class="text-capitalize">
                                    {{ $pattern->class_day }}
                                </td>

                                <td>

                                    {{ \Carbon\Carbon::parse($pattern->start_time)->format('h:i A') }}

                                    -

                                    {{ \Carbon\Carbon::parse($pattern->end_time)->format('h:i A') }}

                                </td>

                                <td>

                                    {{ optional($pattern->hall)->hall_name ?? 'N/A' }}

                                </td>

                                <td>

                                    {{ $pattern->start_date->format('Y-m-d') }}

                                </td>

                                <td>

                                    {{ $pattern->end_date->format('Y-m-d') }}

                                </td>

                                <td>

                                    <a href="{{ route('admin.class-schedules.editBulkSchedule', $pattern->id) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                        Edit
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="text-center py-5">

                                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>

                                    No Schedule Pattern Found

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection
