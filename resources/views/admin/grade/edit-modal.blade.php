<!-- Edit Grade Modal -->
<div class="modal fade"
     id="editGradeModal"
     tabindex="-1"
     aria-labelledby="editGradeModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">

        <form id="editGradeForm" method="POST">

            @csrf
            @method('PUT')

            <div class="modal-content">

                <div class="modal-header bg-warning">

                    <h5 class="modal-title" id="editGradeModalLabel">
                        <i class="fas fa-edit"></i>
                        Edit Grade
                    </h5>

                    <button type="button"
                            class="btn-close"
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

                        <input
                            type="text"
                            name="grade_name"
                            id="edit_grade_name"
                            class="form-control"
                            required>

                    </div>

                    {{-- Status --}}
                    <div class="form-check form-switch">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_active"
                            id="edit_is_active"
                            value="1">

                        <label class="form-check-label">
                            Active
                        </label>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Close

                    </button>

                    <button
                        type="submit"
                        class="btn btn-warning">

                        <i class="fas fa-save"></i>

                        Update Grade

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const editButtons = document.querySelectorAll('.editBtn');

    editButtons.forEach(button => {

        button.addEventListener('click', function () {

            let id = this.dataset.id;
            let name = this.dataset.name;
            let status = this.dataset.status;

            document.getElementById('edit_grade_name').value = name;

            document.getElementById('edit_is_active').checked =
                status == 1 ? true : false;

            document.getElementById('editGradeForm').action =
                "{{ url('admin/grades') }}/" + id;

            let modal = new bootstrap.Modal(
                document.getElementById('editGradeModal')
            );

            modal.show();

        });

    });

});

</script>