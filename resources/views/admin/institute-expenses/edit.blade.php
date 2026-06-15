@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@push('styles')
    <style>
        .form-card {
            background: #fff;
            border-radius: 28px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        .form-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eef2f7;
            background: linear-gradient(135deg, #f8fafc, #fff);
        }
        .form-body {
            padding: 1.5rem;
        }
        .form-label {
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.5rem;
        }
        .form-control-custom {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.7rem 1rem;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        .btn-update {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            color: white;
        }
        .btn-cancel {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            color: white;
        }
        .warning-alert {
            border-radius: 16px;
            margin-bottom: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="mb-3">
            <a href="{{ route('admin.institute-expenses.index') }}" class="btn-cancel"><i class="bi bi-arrow-left"></i> Back</a>
        </div>

        <div class="form-card">
            <div class="form-header">
                <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square"></i> Edit Expense</h4>
                <p class="text-muted mb-0">Update expense transaction details</p>
            </div>
            <div class="form-body">
                @php $isCurrentMonth = Carbon\Carbon::parse($instituteExpense->payment_date)->isCurrentMonth(); @endphp

                @if(!$isCurrentMonth)
                    <div class="alert alert-warning warning-alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Warning:</strong> This expense is from a past month. Editing may affect previous reports.
                    </div>
                @endif

                <form action="{{ route('admin.institute-expenses.update', $instituteExpense->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control form-control-custom" 
                                   value="{{ old('amount', $instituteExpense->amount) }}" required>
                            @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-custom" 
                                   value="{{ old('payment_date', $instituteExpense->payment_date) }}" required>
                            @error('payment_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reason Code</label>
                            <select name="reason_code" class="form-control form-control-custom">
                                <option value="">Select Reason Code</option>
                                @foreach($reasons as $reason)
                                    <option value="{{ $reason->reason_code }}" 
                                        {{ old('reason_code', $instituteExpense->reason_code) == $reason->reason_code ? 'selected' : '' }}>
                                        {{ $reason->reason_code }} - {{ $reason->reason }}
                                    </option>
                                @endforeach
                            </select>
                            @error('reason_code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control form-control-custom" 
                                   value="{{ old('reason', $instituteExpense->reason) }}" placeholder="Enter reason for expense">
                            @error('reason') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" rows="3" class="form-control form-control-custom" 
                                      placeholder="Additional notes...">{{ old('note', $instituteExpense->note) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn-update"><i class="bi bi-save"></i> Update Expense</button>
                        <a href="{{ route('admin.institute-expenses.index') }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection