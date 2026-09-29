@extends('layouts.app')

@section('title', 'Add Fee Option')

@section('content')

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="mb-1">
                Add Fee Option
            </h4>

            <div class="text-muted">
                {{ $classCategoryFee->studentClass->class_name ?? '-' }}
                -
                {{ $classCategoryFee->category->name ?? '-' }}
            </div>
        </div>

        <a href="{{ route('admin.class-category-fee-options.index', [
            'class_category_fee_id' => $classCategoryFee->id
        ]) }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left"></i>
            Back

        </a>

    </div>


    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">
            <h5 class="mb-0">
                Fee Option Details
            </h5>
        </div>

        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.class-category-fee-options.store') }}">

                @csrf

                <input type="hidden"
                       name="class_category_fee_id"
                       value="{{ $classCategoryFee->id }}">


                {{-- Label --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Option Name
                        <span class="text-danger">*</span>
                    </label>

                    <input type="text"
                           name="label"
                           value="{{ old('label') }}"
                           class="form-control @error('label') is-invalid @enderror"
                           placeholder="Example: Theory Only"
                           maxlength="150"
                           required>

                    @error('label')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Fee --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Fee
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            Rs.
                        </span>

                        <input type="number"
                               name="fee"
                               value="{{ old('fee') }}"
                               class="form-control @error('fee') is-invalid @enderror"
                               min="0"
                               step="0.01"
                               placeholder="2500.00"
                               required>

                    </div>

                    @error('fee')
                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Default --}}
                <div class="form-check mb-3">

                    <input type="checkbox"
                           name="is_default"
                           value="1"
                           class="form-check-input"
                           id="is_default"
                           {{ old('is_default') ? 'checked' : '' }}>

                    <label class="form-check-label" for="is_default">
                        Set as default fee option
                    </label>

                    <div class="form-text">
                        The default option can be used when no specific option is selected.
                    </div>

                </div>


                {{-- Active --}}
                <div class="form-check mb-3">

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="form-check-input"
                           id="is_active"
                           {{ old('is_active', true) ? 'checked' : '' }}>

                    <label class="form-check-label" for="is_active">
                        Active
                    </label>

                </div>


                {{-- Note --}}
                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Note
                    </label>

                    <textarea name="note"
                              rows="4"
                              class="form-control @error('note') is-invalid @enderror"
                              placeholder="Optional note...">{{ old('note') }}</textarea>

                    @error('note')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Buttons --}}
                <div class="d-flex justify-content-end gap-2">

                    <a href="{{ route('admin.class-category-fee-options.index', [
                        'class_category_fee_id' => $classCategoryFee->id
                    ]) }}"
                       class="btn btn-light">

                        Cancel

                    </a>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="bi bi-check-lg"></i>
                        Save Fee Option

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection