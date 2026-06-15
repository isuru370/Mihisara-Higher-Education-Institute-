@extends('layouts.app')

@section('title', 'Teacher Payment Report')
@section('page-title', 'Teacher Payment Report')

@push('styles')
    <style>
        .teacher-payment-page {
            animation: fadeIn .4s ease;
        }

        /* Hero Card */
        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            padding: 2rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8, #1e40af);
            box-shadow: 0 20px 45px rgba(37, 99, 235, .25);
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
            top: -100px;
            right: -80px;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
            bottom: -60px;
            left: -40px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .7rem 1rem;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(10px);
            font-size: .85rem;
            font-weight: 600;
        }

        /* Filter Card */
        .filter-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .filter-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .filter-input, .form-select-custom {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .filter-input:focus, .form-select-custom:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-search {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            width: 100%;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        /* Export Buttons */
        .export-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .btn-pdf {
            background: #dc2626;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-pdf:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            color: white;
        }

        .btn-excel {
            background: #059669;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-excel:hover {
            background: #047857;
            transform: translateY(-2px);
            color: white;
        }

        /* Class Card */
        .class-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .class-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.1);
        }

        .class-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .class-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .class-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .class-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Category Section */
        .category-section {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .category-section:last-child {
            border-bottom: none;
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .category-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #475569;
        }

        .category-name i {
            color: #f59e0b;
            margin-right: 0.5rem;
        }

        .category-total {
            background: #f8fafc;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #2563eb;
        }

        /* Table Styles */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table thead th {
            background: #f8fafc;
            padding: 0.9rem 1rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .payment-table tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .payment-table tbody tr:hover {
            background: #f8fafc;
        }

        .payment-table tbody tr:last-child td {
            border-bottom: none;
        }

        .amount-paid {
            color: #10b981;
            font-weight: 700;
            font-family: monospace;
        }

        .method-badge {
            display: inline-block;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .method-cash {
            background: #dcfce7;
            color: #166534;
        }

        .method-card {
            background: #dbeafe;
            color: #1e40af;
        }

        .method-bank {
            background: #fef3c7;
            color: #92400e;
        }

        /* Class Footer */
        .class-footer {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            border-top: 1px solid #eef2f7;
            text-align: right;
        }

        .class-total {
            font-size: 0.9rem;
            font-weight: 700;
            color: #2563eb;
        }

        .class-total span {
            font-size: 1.1rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
            color: #64748b;
        }

        /* Teacher Info Card */
        .teacher-info-card {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border-radius: 24px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .teacher-info h4 {
            margin: 0;
            font-weight: 700;
        }

        .teacher-info p {
            margin: 0;
            opacity: 0.8;
            font-size: 0.85rem;
        }

        .teacher-stats {
            display: flex;
            gap: 1.5rem;
        }

        .teacher-stat {
            text-align: center;
        }

        .teacher-stat .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            opacity: 0.7;
        }

        .teacher-stat .value {
            font-size: 1.2rem;
            font-weight: 700;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card { padding: 1.5rem; }
            .class-header { flex-direction: column; align-items: flex-start; }
            .export-buttons { justify-content: flex-start; }
            .teacher-info-card { flex-direction: column; text-align: center; }
            .teacher-stats { justify-content: center; }
            .payment-table thead th { font-size: 0.6rem; padding: 0.5rem; }
            .payment-table tbody td { padding: 0.5rem; font-size: 0.7rem; }
        }

        @media print {
            .hero-card, .filter-card, .export-buttons, .btn-search, .btn-pdf, .btn-excel {
                display: none !important;
            }
            .class-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
@endpush

@section('content')
    <div class="teacher-payment-page">
        
        <!-- Hero Card -->
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="mb-3">
                        <span class="hero-badge">
                            <i class="bi bi-person-badge-fill"></i>
                            Teacher Daily Payment Report
                        </span>
                    </div>
                    <h2 class="fw-bold mb-2">Teacher Daily Collection Report</h2>
                    <p class="mb-0 text-light-soft">
                        View and analyze teacher-wise student payments collected.
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-calendar-event-fill"></i>
                        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <div class="filter-label">
                        <i class="bi bi-person-badge me-1"></i> Select Teacher
                    </div>
                    <select name="teacher_id" class="form-select-custom" required>
                        <option value="">-- Select Teacher --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}"
                                {{ (string)$teacherId === (string)$teacher->id ? 'selected' : '' }}>
                                {{ $teacher->custom_id }} - {{ $teacher->initials }} - {{ $teacher->full_name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <div class="filter-label">
                        <i class="bi bi-calendar3 me-1"></i> Select Date
                    </div>
                    <input type="date" name="date" class="filter-input" value="{{ $date }}">
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn-search">
                        <i class="bi bi-search me-1"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>

        @if($teacherId)
            <!-- Export Buttons -->
            <div class="export-buttons">
                <a href="{{ url()->current() }}/pdf?teacher_id={{ $teacherId }}&date={{ $date }}" class="btn-pdf">
                    <i class="bi bi-filetype-pdf"></i> Download PDF
                </a>
                <a href="{{ url()->current() }}/excel?teacher_id={{ $teacherId }}&date={{ $date }}" class="btn-excel">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Download Excel
                </a>
            </div>

            <!-- Teacher Info Card -->
            @if(!empty($data))
                @php
                    $firstClass = $data[0] ?? null;
                    $teacherInfo = $firstClass['teacher_info'] ?? null;
                    $totalCollected = collect($data)->sum('class_total_paid');
                @endphp
                
                @if($teacherInfo)
                <div class="teacher-info-card">
                    <div class="teacher-info">
                        <h4><i class="bi bi-person-circle me-2"></i>{{ $teacherInfo['full_name'] ?? $teacherInfo['initials'] }}</h4>
                        <p><i class="bi bi-envelope me-1"></i> {{ $teacherInfo['email'] ?? 'N/A' }} | 
                           <i class="bi bi-phone me-1"></i> {{ $teacherInfo['mobile'] ?? 'N/A' }}</p>
                    </div>
                    <div class="teacher-stats">
                        <div class="teacher-stat">
                            <div class="label">Total Classes</div>
                            <div class="value">{{ count($data) }}</div>
                        </div>
                        <div class="teacher-stat">
                            <div class="label">Total Collected</div>
                            <div class="value">Rs. {{ number_format($totalCollected, 2) }}</div>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            <!-- Class Cards -->
            @forelse($data as $class)
                <div class="class-card">
                    <div class="class-header">
                        <div class="class-title">
                            <i class="bi bi-book-fill"></i>
                            {{ $class['class_name'] }}
                            <span class="class-badge ms-2">{{ $class['grade_name'] }}</span>
                        </div>
                        <div class="class-badge">
                            <i class="bi bi-people-fill me-1"></i>
                            Total Categories: {{ count($class['categories']) }}
                        </div>
                    </div>

                    <div class="card-body p-0">
                        @foreach($class['categories'] as $category)
                            <div class="category-section">
                                <div class="category-header">
                                    <div class="category-name">
                                        <i class="bi bi-tag-fill"></i>
                                        {{ $category['category_name'] }}
                                    </div>
                                    <div class="category-total">
                                        <i class="bi bi-cash-stack me-1"></i>
                                        Total: Rs. {{ number_format($category['category_total_paid'], 2) }}
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="payment-table">
                                        <thead>
                                            <tr>
                                                <th>Student Code</th>
                                                <th>Student Name</th>
                                                <th>Guardian Mobile</th>
                                                <th>Payment ID</th>
                                                <th>Paid At</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($category['students'] as $student)
                                                @foreach($student['payments'] as $payment)
                                                    <tr>
                                                        <td>
                                                            <code class="bg-light px-1 py-0 rounded">{{ $student['student_code'] }}</code>
                                                        </td>
                                                        <td class="fw-semibold">{{ $student['student_name'] }}</td>
                                                        <td>{{ $student['guardian_mobile'] }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary">#{{ $payment['payment_id'] }}</span>
                                                        </td>
                                                        <td>
                                                            <small>{{ \Carbon\Carbon::parse($payment['paid_at'])->format('d M Y h:i A') }}</small>
                                                        </td>
                                                        <td class="amount-paid">Rs. {{ number_format($payment['amount'], 2) }}</td>
                                                        <td>
                                                            <span class="method-badge method-{{ $payment['payment_method'] ?? 'cash' }}">
                                                                {{ ucfirst($payment['payment_method'] ?? 'Cash') }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="class-footer">
                        <div class="class-total">
                            <i class="bi bi-calculator-fill me-1"></i>
                            Class Total: <span>Rs. {{ number_format($class['class_total_paid'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>No Records Found</h5>
                    <p class="text-muted">No payment records found for the selected teacher and date.</p>
                </div>
            @endforelse
        @else
            <div class="empty-state">
                <i class="bi bi-person-badge"></i>
                <h5>Select a Teacher</h5>
                <p class="text-muted">Please select a teacher and date to view the payment report.</p>
            </div>
        @endif
    </div>
@endsection