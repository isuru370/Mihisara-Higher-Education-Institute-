@php
    $classSummaries = $class_summaries ?? [];
@endphp

<div class="main-card">
    <div class="main-card-header">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-book me-2"></i>
                Class Income Summary
            </h4>
            <p class="mb-0">Class-wise income breakdown for the selected month</p>
        </div>

        <div class="header-badge">
            {{ count($classSummaries) }} Classes
        </div>
    </div>

    <div class="table-responsive" style="max-height: 500px;">
        <table class="table custom-table table-bordered mb-0 table-sticky">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Grade</th>
                    <th>Total (LKR)</th>
                    <th>Teacher (LKR)</th>
                    <th>Organizer (LKR)</th>
                    <th>Institute (LKR)</th>
                </tr>
            </thead>

            <tbody>
                @forelse($classSummaries as $index => $class)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $class['class_name'] }}</div>
                        </td>
                        <td>{{ $class['grade_name'] }}</td>
                        <td>LKR {{ number_format($class['total_income'] ?? 0, 2) }}</td>
                        <td class="text-success fw-bold">
                            LKR {{ number_format($class['teacher_income'] ?? 0, 2) }}
                        </td>
                        <td>LKR {{ number_format($class['organizer_income'] ?? 0, 2) }}</td>
                        <td class="text-primary fw-bold">
                            LKR {{ number_format($class['institution_income'] ?? 0, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            No class data found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>