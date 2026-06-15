@extends('layouts.app')

@section('title', 'Edit Enrollment')
@section('page-title', 'Edit Enrollment')

@push('styles')
<style>
    .enrollment-page {
        animation: fadeIn .3s ease;
    }

    .hero-card,
    .main-card {
        background: #fff;
        border-radius: 28px;
        border: 1px solid #eef2f7;
        box-shadow: 0 10px 30px rgba(0,0,0,.05);
    }

    .hero-card {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .hero-content {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:1rem;
        flex-wrap:wrap;
    }

    .main-card {
        padding:1.5rem;
    }

    .custom-btn {
        border-radius:14px;
        padding:.7rem 1.2rem;
        font-weight:600;
    }

    .btn-primary{
        background:linear-gradient(135deg,#2563eb,#3b82f6);
        border:none;
    }

    @media(max-width:768px){
        .hero-content{
            flex-direction:column;
            align-items:stretch;
        }
    }
</style>
@endpush

@section('content')

<div class="enrollment-page">

    <div class="hero-card">

        <div class="hero-content">

            <div>
                <h4 class="fw-bold mb-1">
                    Edit Student Enrollment
                </h4>

                <p class="text-muted mb-0">
                    {{ $enrollment->student?->custom_id }}
                    -
                    {{ $enrollment->student?->initial_name }}
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">

                <button type="button"
                        class="btn btn-outline-primary custom-btn"
                        disabled>
                    Future Button
                </button>

                <a href="{{ route('admin.student-class-enrollments.index') }}"
                   class="btn btn-outline-secondary custom-btn">
                    Back
                </a>

            </div>

        </div>

    </div>

    <div class="main-card">

        <form action="{{ route('admin.student-class-enrollments.update', $enrollment) }}"
              method="POST">

            @csrf
            @method('PUT')

            @include('admin.student-class-enrollments.partials.form', [
                'buttonText' => 'Update Enrollment'
            ])

        </form>

    </div>

</div>

@endsection