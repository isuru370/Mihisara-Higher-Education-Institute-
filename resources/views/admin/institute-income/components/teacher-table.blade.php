@php
    $teacherSummaries = $teacher_summaries ?? [];
@endphp

<div class="main-card mb-4">
    <div class="main-card-header">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bi bi-person-badge me-2"></i>
                Teacher Income Summary
            </h4>
            <p class="mb-0">Teacher income distribution for the selected month</p>
        </div>

        <div class="header-badge">
            {{ count($teacherSummaries) }} Teachers
        </div>
    </div>

    <div class="table-responsive" style="max-height: 400px;">
        <table class="table custom-table table-bordered mb-0 table-sticky">
            <thead>
                <tr>
                    <th style="min-width: 70px;">#</th>
                    <th>Teacher</th>
                    <th>Total (LKR)</th>
                    <th>Teacher (LKR)</th>
                    <th>Organizer (LKR)</th>
                    <th>Institute (LKR)</th>
                </tr>
            </thead>

            <tbody>
                @forelse($teacherSummaries as $index => $teacher)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $teacher['teacher_name'] }}</div>
                            @if(!empty($teacher['teacher_custom_id']))
                                <small class="text-muted">{{ $teacher['teacher_custom_id'] }}</small>
                            @endif
                        </td>
                        <td>LKR {{ number_format($teacher['total_income'] ?? 0, 2) }}</td>
                        <td class="text-success fw-bold">
                            LKR {{ number_format($teacher['teacher_income'] ?? 0, 2) }}
                        </td>
                        <td>LKR {{ number_format($teacher['organizer_income'] ?? 0, 2) }}</td>
                        <td class="text-primary fw-bold">
                            LKR {{ number_format($teacher['institution_income'] ?? 0, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                            No teacher data found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>