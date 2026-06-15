@php
    $extraIncome = $extraIncome ?? null;
    $isEdit = !empty($extraIncome);

    $incomeTypes = [
        'hall_rent' => 'Hall Rent',
        'extra' => 'Extra',
        'refund' => 'Refund',
        'other' => 'Other',
    ];

    $statuses = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    $selectedIncomeType = old('income_type', $extraIncome->income_type ?? 'extra');
    $selectedStatus = old('status', $extraIncome->status ?? 'received');
    $incomeDateValue = old(
        'income_date',
        !empty($extraIncome?->income_date)
        ? $extraIncome->income_date->format('Y-m-d')
        : now()->format('Y-m-d')
    );
@endphp

<div class="row g-4">

    <div class="col-lg-6">
        <label class="form-label fw-semibold">
            Amount <span class="text-danger">*</span>
        </label>

        <input type="number" step="0.01" min="0.01" name="amount"
            class="form-control custom-input @error('amount') is-invalid @enderror"
            value="{{ old('amount', $extraIncome->amount ?? '') }}" placeholder="Enter amount" required>

        @error('amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label fw-semibold">
            Income Date <span class="text-danger">*</span>
        </label>

        <input type="date" name="income_date"
            class="form-control custom-input @error('income_date') is-invalid @enderror" value="{{ $incomeDateValue }}"
            required>

        @error('income_date')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label fw-semibold">
            Reason
        </label>

        <input type="text" name="reason" class="form-control custom-input @error('reason') is-invalid @enderror"
            value="{{ old('reason', $extraIncome->reason ?? '') }}" placeholder="Enter reason">

        @error('reason')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label fw-semibold">
            Income Type
        </label>

        <select name="income_type" class="form-select custom-input @error('income_type') is-invalid @enderror">
            @foreach($incomeTypes as $typeValue => $typeLabel)
                <option value="{{ $typeValue }}" {{ $selectedIncomeType === $typeValue ? 'selected="selected"' : '' }}>
                    {{ $typeLabel }}
                </option>
            @endforeach
        </select>

        @error('income_type')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6">
        <label class="form-label fw-semibold">
            Status
        </label>

        <select name="status" class="form-select custom-input @error('status') is-invalid @enderror">
            @foreach($statuses as $statusValue => $statusLabel)
                <option value="{{ $statusValue }}" {{ $selectedStatus === $statusValue ? 'selected="selected"' : '' }}>
                    {{ $statusLabel }}
                </option>
            @endforeach
        </select>

        @error('status')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label class="form-label fw-semibold">
            Note
        </label>

        <textarea name="note" rows="4" class="form-control custom-input @error('note') is-invalid @enderror"
            placeholder="Optional note">{{ old('note', $extraIncome->note ?? '') }}</textarea>

        @error('note')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 d-flex justify-content-end gap-2 pt-2">
        <a href="{{ route('admin.extra-incomes.index') }}" class="btn btn-outline-secondary custom-btn">
            <i class="bi bi-x-lg me-1"></i>
            Cancel
        </a>

        <button type="submit" class="btn btn-primary custom-btn">
            <i class="bi bi-save me-1"></i>
            {{ $buttonText ?? 'Save' }}
        </button>
    </div>

</div>

@push('styles')
    <style>
        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
            box-shadow: none;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        textarea.custom-input {
            min-height: 120px;
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