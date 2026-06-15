@php
    $classHall = $classHall ?? null;
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
    action="{{ $classHall ? route('admin.class-halls.update', $classHall) : route('admin.class-halls.store') }}">
    @csrf

    @if ($classHall)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-12">
            <div class="border rounded-4 p-3">
                <h6 class="fw-bold mb-3">Hall Information</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code *</label>
                        <input type="text" name="code" class="form-control"
                            value="{{ old('code', $classHall->code ?? '') }}"
                            placeholder="HALL-01" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Hall Name *</label>
                        <input type="text" name="hall_name" class="form-control"
                            value="{{ old('hall_name', $classHall->hall_name ?? '') }}"
                            placeholder="Main Hall" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Hall Type</label>
                        <input type="text" name="hall_type" class="form-control"
                            value="{{ old('hall_type', $classHall->hall_type ?? '') }}"
                            placeholder="AC / Non-AC / Online">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Hall Price</label>
                        <input type="number" name="hall_price" class="form-control"
                            value="{{ old('hall_price', $classHall->hall_price ?? 0) }}"
                            min="0" step="0.01" placeholder="0.00">
                        <small class="text-muted">0 නම් Free hall එකක් ලෙස count වෙනවා.</small>
                    </div>

                    <div class="col-md-12">
                        <input type="hidden" name="is_active" value="0">

                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" value="1" id="is_active"
                                class="form-check-input"
                                {{ old('is_active', $classHall->is_active ?? true) ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.class-halls.index') }}" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
        </div>
    </div>
</form>