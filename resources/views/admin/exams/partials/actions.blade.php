<div class="action-buttons">

    {{-- View --}}
    <a href="{{ route('admin.exams.show', $exam->id) }}"
       class="action-btn view-btn"
       title="View">
        <i class="bi bi-eye-fill"></i>
    </a>

    {{-- Results --}}
    @if($showResults)
        <a href="{{ route('admin.exams.results', $exam->id) }}"
           class="action-btn results-btn"
           title="Results">
            <i class="bi bi-bar-chart-fill"></i>
        </a>
    @endif

    {{-- Mark Entry --}}
    @if($showMarkEntry)
        <a href="{{ route('admin.exams.mark-entry', $exam->id) }}"
           class="action-btn mark-btn"
           title="Marks">
            <i class="bi bi-journal-check"></i>
        </a>
    @endif

    {{-- Edit --}}
    @if($showEdit)
        <a href="{{ route('admin.exams.edit', $exam->id) }}"
           class="action-btn edit-btn"
           title="Edit">
            <i class="bi bi-pencil-fill"></i>
        </a>
    @endif

    {{-- Cancel --}}
    @if($showCancel)
        <button
            type="button"
            class="action-btn cancel-btn"
            data-bs-toggle="modal"
            data-bs-target="#cancelModal"
            data-exam-id="{{ $exam->id }}"
            data-exam-title="{{ $exam->title }}"
            data-exam-route="{{ route('admin.exams.cancel', $exam->id) }}"
            title="Cancel">

            <i class="bi bi-x-circle-fill"></i>

        </button>
    @endif

    {{-- Delete --}}
    @if($showDelete)
        <form method="POST"
              action="{{ route('admin.exams.destroy', $exam->id) }}"
              class="d-inline">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="action-btn delete-btn"
                    title="Delete">

                <i class="bi bi-trash-fill"></i>

            </button>

        </form>
    @endif

</div>