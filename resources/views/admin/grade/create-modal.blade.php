<!-- Create Grade Modal -->
<div class="modal fade"
     id="createGradeModal"
     tabindex="-1"
     aria-labelledby="createGradeModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">

        <form action="{{ route('admin.grades.store') }}" method="POST">

            @csrf

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title" id="createGradeModalLabel">
                        <i class="fas fa-plus-circle"></i>
                        Add New Grade
                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    {{-- Grade Name --}}
                    <div class="mb-3">

                        <label class="form-label">
                            Grade Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="grade_name"
                               class="form-control @error('grade_name') is-invalid @enderror"
                               value="{{ old('grade_name') }}"
                               placeholder="Enter Grade Name"
                               required>

                        @error('grade_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Status --}}
                    <div class="form-check form-switch">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            id="createStatus"
                            value="1"
                            checked>

                        <label class="form-check-label" for="createStatus">
                            Active
                        </label>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        <i class="fas fa-times"></i>
                        Close

                    </button>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-save"></i>
                        Save Grade

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

{{-- Reopen Modal if Validation Error --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('createGradeModal'));
        modal.show();
    });
</script>
@endif