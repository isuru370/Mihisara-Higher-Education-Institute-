@extends('layouts.app')

@section('title', 'Edit Extra Income')
@section('page-title', 'Edit Extra Income')

@section('content')
    <div class="extra-income-page">

        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <h3 class="fw-bold mb-1">Edit Extra Income</h3>
                    <p class="text-muted mb-0">
                        Update record details
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.extra-incomes.show', $extraIncome) }}"
                        class="btn btn-outline-primary custom-btn">
                        <i class="bi bi-eye me-1"></i>
                        View
                    </a>

                    <a href="{{ route('admin.extra-incomes.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <div class="main-card">
            <form method="POST" action="{{ route('admin.extra-incomes.update', $extraIncome) }}">
                @csrf
                @method('PUT')

                @include('admin.extra-incomes.partials.form', [
                    'extraIncome' => $extraIncome,
                    'buttonText' => 'Update Income'
                ])
            </form>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .extra-income-page {
            animation: fadeIn .4s ease;
        }

        .hero-card,
        .main-card {
               background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,.05);
            border: 1px solid #eef2f7;
            padding: 1.35rem 1.5rem;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-actions {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }
    </style>
@endpush