@extends('layouts.app')

@section('title', 'Student Card History')
@section('page-title', 'Student Card History')

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="fw-bold mb-1">Student Card History</h4>
            <p class="text-muted mb-0">
                Complete card assignment and replacement history
            </p>
        </div>

        <a href="{{ route('admin.students.show', $student->id) }}"
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Back
        </a>

    </div>

    {{-- Student Information --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-person-badge"></i>
                Student Information
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-3 mb-3">
                    <label class="text-muted">Student Name</label>
                    <h6 class="fw-bold">
                        {{ $student->full_name }}
                    </h6>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="text-muted">Registration No</label>
                    <h6 class="fw-bold">
                        {{ $student->registration_number }}
                    </h6>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="text-muted">Current QR</label>

                    @if($student->custom_id)

                        <div>
                            <span class="badge bg-success">
                                {{ $student->custom_id }}
                            </span>
                        </div>

                    @else

                        <span class="badge bg-danger">
                            No Active Card
                        </span>

                    @endif

                </div>

                <div class="col-md-3 mb-3">

                    <label class="text-muted">Status</label>

                    @if($student->student_disable)

                        <div>
                            <span class="badge bg-danger">
                                Disabled
                            </span>
                        </div>

                    @else

                        <div>
                            <span class="badge bg-success">
                                Active
                            </span>
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

    {{-- History Table --}}
    <div class="card shadow-sm border-0">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i>
                Card History
            </h5>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                    <tr>

                        <th>#</th>
                        <th>Action</th>
                        <th>Card</th>
                        <th>Old Card</th>
                        <th>New Card</th>
                        <th>Reason</th>
                        <th>Performed By</th>
                        <th>Date</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($cards as $history)

                        <tr>

                            <td>
                                {{ $cards->firstItem() + $loop->index }}
                            </td>

                            <td>

                                @switch($history->action)

                                    @case('assigned')
                                        <span class="badge bg-primary">
                                            Assigned
                                        </span>
                                    @break

                                    @case('replaced')
                                        <span class="badge bg-info">
                                            Replaced
                                        </span>
                                    @break

                                    @case('lost')
                                        <span class="badge bg-danger">
                                            Lost
                                        </span>
                                    @break

                                    @case('damaged')
                                        <span class="badge bg-warning text-dark">
                                            Damaged
                                        </span>
                                    @break

                                    @case('deactivated')
                                        <span class="badge bg-secondary">
                                            Deactivated
                                        </span>
                                    @break

                                @endswitch

                            </td>

                            <td>
                                {{ optional($history->card)->card_number ?? '-' }}
                            </td>

                            <td>
                                {{ optional($history->oldCard)->card_number ?? '-' }}
                            </td>

                            <td>
                                {{ optional($history->newCard)->card_number ?? '-' }}
                            </td>

                            <td>
                                {{ $history->reason ?? '-' }}
                            </td>

                            <td>
                                {{ optional($history->performedBy)->name ?? 'System' }}
                            </td>

                            <td>
                                {{ $history->performed_at->format('d M Y h:i A') }}
                            </td>

                        </tr>
                                            @empty

                        <tr>

                            <td colspan="8" class="text-center py-5">

                                <i class="bi bi-clock-history fs-1 text-muted"></i>

                                <h5 class="mt-3">
                                    No Card History Found
                                </h5>

                                <p class="text-muted mb-0">
                                    This student has no recorded card history.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        @if($cards->hasPages())

            <div class="card-footer bg-white">

                {{ $cards->links() }}

            </div>

        @endif

    </div>

</div>

@endsection


@push('styles')

<style>

.card{
    border-radius:16px;
}

.card-header{
    font-weight:600;
}

.table th{
    font-size:14px;
    font-weight:600;
    white-space:nowrap;
}

.table td{
    vertical-align:middle;
}

.badge{
    font-size:12px;
    padding:7px 10px;
}

.table tbody tr:hover{
    background:#f8f9fa;
}

label{
    font-size:13px;
    font-weight:600;
    color:#6c757d;
    margin-bottom:5px;
}

h6{
    margin-bottom:0;
}

.card-header.bg-primary{
    border-radius:16px 16px 0 0;
}

.pagination{
    margin-bottom:0;
}

.pagination .page-link{
    border-radius:8px;
    margin:0 2px;
}

@media(max-width:768px){

    .table{
        font-size:13px;
    }

    .badge{
        font-size:11px;
    }

    h4{
        font-size:20px;
    }

}

</style>

@endpush