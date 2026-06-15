@php
    $organizer = $organizer ?? null;
@endphp

<form method="POST"
    action="{{ $organizer ? route('admin.organizers.update', $organizer) : route('admin.organizers.store') }}">

    @csrf
    @if($organizer) @method('PUT') @endif

    <div class="row g-3">

        <div class="col-md-4">
            <label class="form-label">Code</label>
            <input type="text" class="form-control" value="Auto Generated" disabled>
        </div>

        <div class="col-md-8">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $organizer->name ?? '') }}"
                required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $organizer->mobile ?? '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $organizer->email ?? '') }}">
        </div>

        <div class="col-md-4">
            <label class="form-label">NIC</label>
            <input type="text" name="nic" class="form-control" value="{{ old('nic', $organizer->nic ?? '') }}">
        </div>

        <div class="col-md-12">
            <label class="form-label">Note</label>
            <textarea name="note" class="form-control">{{ old('note', $organizer->note ?? '') }}</textarea>
        </div>

        <div class="col-md-12">
            <div class="form-check form-switch">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $organizer->is_active ?? true) ? 'checked' : '' }}>
                <label>Active</label>
            </div>
        </div>

        <div class="col-md-12 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.organizers.index') }}" class="btn btn-light border">Cancel</a>
            <button class="btn btn-primary">{{ $buttonText }}</button>
        </div>

    </div>

</form>