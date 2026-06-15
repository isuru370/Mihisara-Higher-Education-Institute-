@extends('layouts.app')

@section('title', 'Admissions')
@section('page-title', 'Admissions')

@push('styles')
    <style>
        .admissions-page {
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

        .text-light-soft {
            color: rgba(255, 255, 255, .78);
        }

        /* Stats Cards */
        .stats-row {
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 1.25rem;
            border: 1px solid #eef2f7;
            transition: all 0.2s ease;
            height: 100%;
        }

        .stat-card:hover {
            border-color: #2563eb;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0;
        }

        .stat-value.text-primary {
            color: #2563eb;
        }

        .stat-value.text-success {
            color: #10b981;
        }

        .stat-value.text-danger {
            color: #ef4444;
        }

        /* Filter Card */
        .filter-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            padding: 1.25rem 1.5rem;
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

        .filter-input {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .filter-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .btn-search {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
        }

        .btn-reset {
            background: #64748b;
            border: none;
            border-radius: 12px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-reset:hover {
            background: #475569;
            transform: translateY(-2px);
            color: white;
        }

        /* Table Card */
        .table-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #eef2f7;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-bottom: 1px solid #eef2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-title {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .table-title i {
            color: #2563eb;
            margin-right: 0.5rem;
        }

        .btn-add {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            border-radius: 14px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            color: white;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.25);
            color: white;
        }

        /* Table Styles */
        .admission-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admission-table thead th {
            background: #f8fafc;
            padding: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }

        .admission-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .admission-table tbody tr:hover {
            background: #f8fafc;
        }

        .admission-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Amount Styles */
        .amount-value {
            font-weight: 700;
            color: #10b981;
            font-family: monospace;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: .2s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .view-btn {
            background: #eff6ff;
            color: #2563eb;
        }

        .edit-btn {
            background: #fef3c7;
            color: #d97706;
        }

        .delete-btn {
            background: #fef2f2;
            color: #ef4444;
        }

        /* Badges */
        .badge-active {
            background: #dcfce7;
            color: #166534;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
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

        /* Pagination */
        .pagination-container {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid #eef2f7;
        }

        .pagination {
            margin: 0;
            gap: 0.25rem;
        }

        .pagination .page-link {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-weight: 500;
            padding: 0.5rem 1rem;
        }

        .pagination .page-link:hover {
            background: #eff6ff;
            border-color: #2563eb;
            color: #2563eb;
        }

        .pagination .active .page-link {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-color: #2563eb;
            color: white;
        }

        /* Alert */
        .custom-alert {
            border-radius: 16px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-card {
                padding: 1.5rem;
            }

            .stat-value {
                font-size: 1.1rem;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
            }

            .admission-table thead th {
                font-size: 0.65rem;
                padding: 0.5rem;
            }

            .admission-table tbody td {
                padding: 0.5rem;
                font-size: 0.75rem;
            }

            .action-buttons {
                justify-content: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="admissions-page">

        {{-- HERO CARD --}}
        <div class="hero-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 position-relative">
                <div>
                    <div class="hero-badge mb-3">
                        <i class="bi bi-journal-bookmark-fill"></i>
                        Admission Management
                    </div>
                    <h2 class="fw-bold mb-2">Admission Fees</h2>
                    <p class="mb-0 text-light-soft">
                        Manage admission fee structures and settings for student enrollments.
                    </p>
                </div>
                <div class="text-end">
                    <div class="hero-badge mb-2">
                        <i class="bi bi-calendar-event-fill"></i>
                        {{ now()->format('d M Y') }}
                    </div>
                    <div class="hero-badge">
                        <i class="bi bi-clock-fill"></i>
                        {{ now()->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS CARDS --}}
        <div class="stats-row">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Total Admissions</div>
                        <div class="stat-value text-primary">{{ $admissions->total() }}</div>
                        <small class="text-muted">All admission records</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Active Admissions</div>
                        <div class="stat-value text-success">{{ $admissions->where('is_active', true)->count() }}</div>
                        <small class="text-muted">Currently active</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-label">Total Amount</div>
                        <div class="stat-value text-primary">Rs. {{ number_format($admissions->sum('amount'), 2) }}</div>
                        <small class="text-muted">Sum of all admission fees</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER CARD --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('admin.admissions.index') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <div class="filter-label">
                        <i class="bi bi-search me-1"></i> Search Admissions
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="filter-input"
                        placeholder="Search by admission name or note...">
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn-search w-100">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.admissions.index') }}" class="btn-reset w-100">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLE CARD --}}
        <div class="table-card">
            <div class="table-header">
                <h5 class="table-title">
                    <i class="bi bi-table"></i> Admissions List
                </h5>
                <a href="{{ route('admin.admissions.create') }}" class="btn-add">
                    <i class="bi bi-plus-lg"></i> Add New Admission
                </a>
            </div>

            <div class="table-responsive">
                <table class="admission-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Admission Name</th>
                            <th style="width: 15%;">Amount</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 30%;">Note</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissions as $admission)
                            <tr>
                                <td>{{ $admissions->firstItem() + $loop->index }}</td>
                                <td class="fw-semibold">{{ $admission->name }}</td>
                                <td class="amount-value">Rs. {{ number_format($admission->amount, 2) }}</td>
                                <td>
                                    @if($admission->is_active)
                                        <span class="badge-active">
                                            <i class="bi bi-check-circle-fill me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge-inactive">
                                            <i class="bi bi-x-circle-fill me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($admission->note)
                                        <span class="text-muted">{{ \Illuminate\Support\Str::limit($admission->note, 50) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.admissions.show', $admission) }}" class="action-btn view-btn"
                                            title="View">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('admin.admissions.edit', $admission) }}" class="action-btn edit-btn"
                                            title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('admin.admissions.destroy', $admission) }}" method="POST"
                                            style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this admission?')">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No Admissions Found</h5>
                                    <p class="text-muted">Click "Add New Admission" to create your first admission record.</p>
                                    <a href="{{ route('admin.admissions.create') }}" class="btn-add mt-2">
                                        <i class="bi bi-plus-lg"></i> Add New Admission
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admissions->hasPages())
                <div class="pagination-container">
                    {{ $admissions->links() }}
                </div>
            @endif
        </div>

        {{-- FOOTER NOTE --}}
        <div class="text-center mt-3">
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                Showing {{ $admissions->firstItem() ?? 0 }} to {{ $admissions->lastItem() ?? 0 }} of
                {{ $admissions->total() }} admissions
            </small>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) alert.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
@endpush