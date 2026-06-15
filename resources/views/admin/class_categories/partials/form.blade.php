@php
    $category = $category ?? null;
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
    action="{{ $category ? route('admin.class-categories.update', $category) : route('admin.class-categories.store') }}">

    @csrf

    @if($category)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-12">
            <div class="border rounded-4 p-3">
                <h6 class="fw-bold mb-3">Category Information</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="category_name" class="form-control"
                            value="{{ old('category_name', $category->category_name ?? '') }}"
                            placeholder="Theory / Revision / Paper" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Code *</label>
                        <input type="text" name="code" class="form-control"
                            value="{{ old('code', $category->code ?? '') }}"
                            placeholder="THEORY / REVISION / PAPER" required>
                    </div>

                    <div class="col-md-12">
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_schedulable" value="1" id="is_schedulable"
                                    class="form-check-input"
                                    {{ old('is_schedulable', $category->is_schedulable ?? true) ? 'checked' : '' }}>
                                <label for="is_schedulable" class="form-check-label">Schedulable</label>
                            </div>

                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" value="1" id="is_active"
                                    class="form-check-input"
                                    {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.class-categories.index') }}" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
        </div>
    </div>
</form>