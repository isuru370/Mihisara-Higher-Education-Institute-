@extends('layouts.app')

@section('title', 'Student Payments')
@section('page-title', 'Student Payments')

@section('content')

    <div class="student-payments-page">

        {{-- Stats Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon blue">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <h3>{{ number_format($payments->total()) }}</h3>
                        <p>Total Payments</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon green">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <h3>Rs. {{ number_format($payments->sum('amount'), 2) }}</h3>
                        <p>Total Amount</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon purple">
                        <i class="bi bi-qr-code"></i>
                    </div>
                    <div>
                        <h3>{{ number_format($payments->where('mark_method', 'qr')->count()) }}</h3>
                        <p>QR Payments</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="stats-card">
                    <div class="stats-icon orange">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <h3>{{ number_format($payments->where('mark_method', 'manual')->count()) }}</h3>
                        <p>Manual Payments</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters Card --}}
        <div class="filter-card">
            <div class="filter-header">
                <div class="filter-icon">
                    <i class="bi bi-funnel-fill"></i>
                </div>
                <div>
                    <h5>Filter Payments</h5>
                    <p>Search and filter student payments</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.student-payments.index') }}">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">
                            <i class="bi bi-receipt"></i> Receipt Number
                        </label>
                        <input type="text" name="receipt_number" class="form-control custom-input"
                            value="{{ request('receipt_number') }}" placeholder="Enter receipt number">
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">
                            <i class="bi bi-person"></i> Student Name/ID
                        </label>
                        <input type="text" name="student" class="form-control custom-input" value="{{ request('student') }}"
                            placeholder="Search by student name or ID">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">
                            <i class="bi bi-qr-code"></i> Mark Method
                        </label>
                        <select name="mark_method" class="form-select custom-input">
                            <option value="">All Methods</option>
                            <option value="qr" @selected(request('mark_method') === 'qr')>QR Code</option>
                            <option value="manual" @selected(request('mark_method') === 'manual')>Manual</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">
                            <i class="bi bi-calendar"></i> From Date
                        </label>
                        <input type="date" name="from_date" class="form-control custom-input"
                            value="{{ request('from_date') }}">
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label">
                            <i class="bi bi-calendar"></i> To Date
                        </label>
                        <input type="date" name="to_date" class="form-control custom-input"
                            value="{{ request('to_date') }}">
                    </div>

                    <div class="col-lg-12">
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                            <a href="{{ route('admin.student-payments.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-repeat"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Payments Table --}}
        <div class="payments-card">
            <div class="payments-header">
                <div>
                    <h4>
                        <i class="bi bi-cash-stack"></i> Student Payments
                    </h4>
                    <p>View and manage all student payment transactions</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-print" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <button class="btn btn-export" id="exportBtn">
                        <i class="bi bi-file-earmark-excel"></i> Export
                    </button>
                </div>
            </div>

            <div class="payments-body">
                <div class="table-responsive">
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th>Receipt No</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Collected By</th>
                                <th>Date & Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>
                                        <div class="receipt-number">
                                            <i class="bi bi-receipt-cutoff"></i>
                                            <span class="receipt-no">{{ $payment->receipt_number ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="student-info">
                                            <div class="student-avatar">
                                                {{ strtoupper(substr($payment->student->initial_name ?? 'N', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="student-name">{{ $payment->student->initial_name ?? 'N/A' }}</div>
                                                <small class="student-id">ID:
                                                    {{ $payment->student->custom_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="class-name">
                                            <i class="bi bi-book"></i>
                                            {{ $payment->enrollment->studentClass->class_name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-badge">
                                            <i class="bi bi-tag"></i>
                                            {{ $payment->enrollment->classCategoryFee->category->category_name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="amount-cell">
                                        <span class="currency">Rs.</span>
                                        <span class="amount">{{ number_format($payment->amount, 2) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $methodColors = [
                                                'qr' => ['class' => 'method-qr', 'icon' => 'bi-qr-code', 'text' => 'QR Code'],
                                                'manual' => ['class' => 'method-manual', 'icon' => 'bi-pencil', 'text' => 'Manual'],
                                            ];
                                            $methodInfo = $methodColors[$payment->mark_method] ?? ['class' => 'method-default', 'icon' => 'bi-question', 'text' => ucfirst($payment->mark_method)];
                                        @endphp
                                        <span class="method-badge {{ $methodInfo['class'] }}">
                                            <i class="bi {{ $methodInfo['icon'] }}"></i>
                                            {{ $methodInfo['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="collector-info">
                                            <i class="bi bi-person-circle"></i>
                                            {{ $payment->collectedBy->name ?? 'System' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-time-cell">
                                            <div class="date">
                                                <i class="bi bi-calendar3"></i>
                                                {{ $payment->created_at->format('Y-m-d') }}
                                            </div>
                                            <div class="time">
                                                <i class="bi bi-clock"></i>
                                                {{ $payment->created_at->format('h:i A') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.student-payments.show', $payment->id) }}"
                                                class="action-btn view-btn" title="View Payment">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.student-payments.receipt', $payment->id) }}"
                                                class="action-btn print-btn" title="Print Receipt" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>
                                            <h5>No Payments Found</h5>
                                            <p>No student payments match your search criteria</p>
                                            <a href="{{ route('admin.student-payments.index') }}" class="btn btn-primary mt-2">
                                                <i class="bi bi-arrow-repeat"></i> Clear Filters
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="payments-pagination">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .student-payments-page {
            animation: fadeInUp 0.4s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats Cards */
        .stats-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f7;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.08);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.8rem;
        }

        .stats-icon.blue {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .stats-icon.green {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .stats-icon.purple {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }

        .stats-icon.orange {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .stats-card h3 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
        }

        .stats-card p {
            margin: 0;
            color: #64748b;
            font-weight: 500;
        }

        /* Filter Card */
        .filter-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f7;
        }

        .filter-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eef2f7;
        }

        .filter-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filter-icon i {
            font-size: 1.2rem;
            color: white;
        }

        .filter-header h5 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }

        .filter-header p {
            margin: 0;
            font-size: 0.75rem;
            color: #64748b;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .custom-input {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            transition: all 0.2s ease;
        }

        .custom-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 0.8rem;
        }

        .filter-actions .btn {
            padding: 0.6rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border: none;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        /* Payments Card */
        .payments-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .payments-header {
            padding: 1.2rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .payments-header h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }

        .payments-header p {
            margin: 0;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            gap: 0.8rem;
        }

        .btn-print {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-print:hover {
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .btn-export {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        /* Payments Table */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payments-table thead th {
            background: #f8fafc;
            padding: 1rem 1rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #eef2f7;
        }

        .payments-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }

        .payments-table tbody tr:hover {
            background: #f8fafc;
        }

        .payments-table tbody td {
            padding: 1rem;
            font-size: 0.85rem;
            color: #334155;
            vertical-align: middle;
        }

        /* Receipt Number */
        .receipt-number {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .receipt-number i {
            color: #10b981;
            font-size: 1rem;
        }

        .receipt-no {
            font-weight: 600;
            color: #4f46e5;
        }

        /* Student Info */
        .student-info {
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .student-name {
            font-weight: 600;
            color: #1e293b;
        }

        .student-id {
            font-size: 0.7rem;
            color: #64748b;
        }

        /* Class Name */
        .class-name {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .class-name i {
            color: #10b981;
            font-size: 0.8rem;
        }

        /* Category Badge */
        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.7rem;
            background: #f3e8ff;
            color: #6b21a5;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Amount Cell */
        .amount-cell {
            font-weight: 700;
        }

        .currency {
            color: #64748b;
            font-size: 0.7rem;
            margin-right: 0.2rem;
        }

        .amount {
            color: #1e293b;
            font-size: 0.9rem;
        }

        /* Method Badge */
        .method-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.7rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .method-qr {
            background: #e0e7ff;
            color: #4338ca;
        }

        .method-manual {
            background: #fef3c7;
            color: #d97706;
        }

        .method-default {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Collector Info */
        .collector-info {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
        }

        .collector-info i {
            color: #10b981;
        }

        /* Date Time Cell */
        .date-time-cell {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .date,
        .time {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.7rem;
        }

        .date i,
        .time i {
            color: #64748b;
            font-size: 0.7rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.4rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .view-btn {
            background: #eff6ff;
            color: #2563eb;
        }

        .view-btn:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
        }

        .print-btn {
            background: #fef3c7;
            color: #d97706;
        }

        .print-btn:hover {
            background: #d97706;
            color: white;
            transform: translateY(-2px);
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
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        /* Pagination */
        .payments-pagination {
            padding: 1rem 1.5rem;
            border-top: 1px solid #eef2f7;
            background: #f8fafc;
        }

        .payments-pagination .pagination {
            justify-content: center;
            margin: 0;
        }

        .payments-pagination .page-link {
            border-radius: 8px;
            margin: 0 2px;
            border: 1px solid #e2e8f0;
            color: #475569;
            transition: all 0.2s ease;
        }

        .payments-pagination .page-link:hover {
            background: #eff6ff;
            border-color: #4f46e5;
            color: #1e40af;
            transform: translateY(-2px);
        }

        .payments-pagination .active .page-link {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-color: #4f46e5;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-card {
                padding: 1rem;
            }

            .stats-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .stats-card h3 {
                font-size: 1.3rem;
            }

            .filter-actions {
                flex-direction: column;
            }

            .filter-actions .btn {
                width: 100%;
            }

            .payments-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
            }

            .header-actions .btn {
                flex: 1;
                text-align: center;
            }
        }
    </style>
@endpush