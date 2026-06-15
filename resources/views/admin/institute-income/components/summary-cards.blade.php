@php
    $summary = $summary ?? [
        'class_income' => 0,
        'admission_income' => 0,
        'teacher_income' => 0,
        'organizer_income' => 0,
        'institution_income' => 0,
        'extra_income' => 0,
        'total_expenses' => 0,
        'gross_income' => 0,
        'net_income' => 0,
    ];
@endphp

<div class="row g-3 mb-4">

    <div class="col-md-3">
        <div class="summary-card summary-blue">
            <div class="card-body text-center">
                <small class="summary-label">Class Income</small>
                <h3 class="summary-value">
                    LKR {{ number_format($summary['class_income'] ?? 0, 2) }}
                </h3>
                <small class="summary-sub">Total Student Payments</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-info">
            <div class="card-body text-center">
                <small class="summary-label">Admission Income</small>
                <h3 class="summary-value">
                    LKR {{ number_format($summary['admission_income'] ?? 0, 2) }}
                </h3>
                <small class="summary-sub">Admission Payments</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-green">
            <div class="card-body text-center">
                <small class="summary-label">Teacher Income</small>
                <h3 class="summary-value">
                    LKR {{ number_format($summary['teacher_income'] ?? 0, 2) }}
                </h3>
                <small class="summary-sub">Paid to Teachers</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="summary-card summary-warning">
            <div class="card-body text-center">
                <small class="summary-label">Organizer Income</small>
                <h3 class="summary-value">
                    LKR {{ number_format($summary['organizer_income'] ?? 0, 2) }}
                </h3>
                <small class="summary-sub">Paid to Organizers</small>
            </div>
        </div>
    </div>

</div>

<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="summary-card summary-dark">
            <div class="card-body text-center">
                <small class="summary-label">Institute Income</small>
                <h3 class="summary-value">
                    LKR {{ number_format($summary['institution_income'] ?? 0, 2) }}
                </h3>
                <small class="summary-sub">Institute Share</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="summary-card summary-dark">
            <div class="card-body text-center">
                <small class="summary-label">Extra Income</small>
                <h3 class="summary-value">
                    LKR {{ number_format($summary['extra_income'] ?? 0, 2) }}
                </h3>
                <small class="summary-sub">Additional Income</small>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="summary-card summary-red">
            <div class="card-body text-center">
                <small class="summary-label">Expenses</small>
                <h3 class="summary-value">
                    LKR {{ number_format($summary['total_expenses'] ?? 0, 2) }}
                </h3>
                <small class="summary-sub">Total Expenses</small>
            </div>
        </div>
    </div>

</div>

<div class="row g-3 mb-4">

    <div class="col-md-6">
        <div class="summary-card summary-blue">
            <div class="card-body text-center">
                <small class="summary-label">Gross Income</small>

                <h3 class="summary-value">
                    LKR {{ number_format($summary['gross_income'] ?? 0, 2) }}
                </h3>

                <small class="summary-sub">
                    Class + Admission + Extra Income
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="summary-card" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">

            <div class="card-body text-center">
                <small class="summary-label text-white-50">
                    Net Income
                </small>

                <h3 class="summary-value text-white">
                    LKR {{ number_format($summary['net_income'] ?? 0, 2) }}
                </h3>

                <small class="summary-sub text-white-50">
                    Gross Income - Expenses
                </small>
            </div>
        </div>
    </div>

</div>