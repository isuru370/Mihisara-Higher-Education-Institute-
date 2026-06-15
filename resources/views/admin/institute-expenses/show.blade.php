@extends('layouts.app')

@section('title', 'Expense Details')
@section('page-title', 'Expense Details')

@push('styles')
    <style>
        .details-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        .details-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eef2f7;
            background: linear-gradient(135deg, #f8fafc, #fff);
        }
        .details-body {
            padding: 1.5rem;
        }
        .info-row {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-label {
            width: 180px;
            font-weight: 700;
            color: #475569;
        }
        .info-value {
            flex: 1;
            color: #1e293b;
        }
        .amount-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #dc2626;
        }
        .btn-back {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
        }
        @media (max-width: 768px) {
            .info-row { flex-direction: column; gap: 0.5rem; }
            .info-label { width: 100%; }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="mb-3">
            <a href="{{ route('admin.institute-expenses.index') }}" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Expenses</a>
        </div>

        <div class="details-card">
            <div class="details-header">
                <h4 class="fw-bold mb-1"><i class="bi bi-receipt"></i> Expense Transaction Details</h4>
                <p class="text-muted mb-0">Complete information about this expense record</p>
            </div>
            <div class="details-body">
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-hash"></i> Transaction ID</div>
                    <div class="info-value">#EXP{{ str_pad($instituteExpense->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-calendar3"></i> Payment Date</div>
                    <div class="info-value">{{ Carbon\Carbon::parse($instituteExpense->payment_date)->format('l, d F Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-cash-stack"></i> Amount</div>
                    <div class="info-value amount-value">- Rs. {{ number_format($instituteExpense->amount, 2) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-card-text"></i> Reason</div>
                    <div class="info-value">{{ $instituteExpense->reason ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-tag"></i> Reason Code</div>
                    <div class="info-value">{{ $instituteExpense->reason_code ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-person"></i> Recorded By</div>
                    <div class="info-value">{{ $instituteExpense->user->name ?? 'System' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-clock-history"></i> Created At</div>
                    <div class="info-value">{{ $instituteExpense->created_at->format('l, d F Y h:i A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-pencil-square"></i> Last Updated</div>
                    <div class="info-value">{{ $instituteExpense->updated_at->format('l, d F Y h:i A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-file-text"></i> Note</div>
                    <div class="info-value">{{ $instituteExpense->note ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="bi bi-check-circle"></i> Status</div>
                    <div class="info-value"><span class="badge bg-success">Paid</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection