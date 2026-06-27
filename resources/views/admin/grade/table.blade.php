<div class="table-responsive">

    <table class="table table-bordered table-hover align-middle" id="gradeTable">

        <thead class="table-dark">
            <tr>
                <th width="70">#</th>
                <th>Grade Name</th>
                <th class="text-center">Students</th>
                <th class="text-center">Status</th>
                <th class="text-center">Created</th>
                <th class="text-center" width="150">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($grades as $key => $grade)

                <tr>

                    <td>{{ $key + 1 }}</td>

                    <td>
                        <strong>{{ $grade->grade_name }}</strong>
                    </td>

                    <td class="text-center">
                        <span class="badge bg-info">
                            {{ $grade->students_count }}
                        </span>
                    </td>

                    <td class="text-center">

                        @if($grade->is_active)

                            <span class="badge bg-success">
                                Active
                            </span>

                        @else

                            <span class="badge bg-danger">
                                Inactive
                            </span>

                        @endif

                    </td>

                    <td class="text-center">
                        {{ $grade->created_at->format('d M Y') }}
                    </td>

                    <td class="text-center">

                        {{-- Edit --}}
                        <button
                            class="btn btn-sm btn-warning editBtn"

                            data-id="{{ $grade->id }}"
                            data-name="{{ $grade->grade_name }}"
                            data-status="{{ $grade->is_active }}">

                            <i class="fas fa-edit"></i>

                        </button>

                        {{-- Delete --}}
                        <form
                            action="{{ route('admin.grades.destroy',$grade->id) }}"
                            method="POST"
                            class="d-inline">

                            @csrf
                            @method('DELETE')

                            <button
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this grade?')">

                                <i class="fas fa-trash"></i>

                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="text-center text-muted">

                        No Grades Found.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

</div>