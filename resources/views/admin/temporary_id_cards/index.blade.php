@php
    $showTempIdActions = auth()->check() && auth()->user()->email === 'admin@nexorait.lk';
@endphp

@extends('layouts.app')

@section('title', 'Temporary ID Cards')
@section('page-title', 'Temporary ID Cards')

@push('styles')
    <style>
        .tmp-page {
            animation: fadeIn .35s ease;
        }

        .hero-card,
        .main-card,
        .stat-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .hero-card {
            padding: 1.35rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-actions {
            display: flex;
            gap: .7rem;
            flex-wrap: wrap;
        }

        .main-card {
            padding: 1.5rem;
        }

        .stat-card {
            padding: 1.1rem 1.2rem;
            height: 100%;
        }

        .stat-label {
            color: #64748b;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: .35rem;
        }

        .stat-value {
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .filter-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 1.1rem;
            margin-bottom: 1.25rem;
        }

        .custom-input,
        .form-select,
        .form-control {
            border-radius: 14px;
            min-height: 48px;
            border: 1px solid #e2e8f0;
            box-shadow: none;
        }

        .custom-input:focus,
        .form-select:focus,
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        .custom-btn {
            border-radius: 14px;
            padding: .7rem 1.15rem;
            font-weight: 600;
            border: none;
            transition: .2s ease;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }

        .btn-success {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .btn-dark {
            background: linear-gradient(135deg, #0f172a, #1e293b);
        }

        .table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .badge-status {
            padding: .45rem .75rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: .78rem;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-downloaded {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-expired {
            background: #fee2e2;
            color: #991b1b;
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        }

        .action-btn {
            border-radius: 12px;
            padding: .45rem .7rem;
            font-weight: 600;
        }

        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            color: #64748b;
        }

        .empty-state .icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eff6ff;
            color: #2563eb;
            margin-bottom: 1rem;
            font-size: 1.75rem;
        }

        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-actions {
                width: 100%;
            }

            .hero-actions .btn {
                flex: 1;
            }
        }
    </style>
@endpush

@section('content')
    <div class="tmp-page">

        <div class="hero-card">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Temporary ID Cards</h4>
                    <p class="mb-0 text-muted">Manage temporary ID card numbers, status, preview, and PDF export.</p>
                </div>

                <div class="hero-actions">
                    @if($showTempIdActions)
                        <a href="{{ route('admin.temporary-id-cards.create') }}" class="btn btn-primary custom-btn">
                            + Generate New
                        </a>

                        <a href="{{ route('admin.temporary-id-cards.preview') }}" class="btn btn-success custom-btn">
                            Preview
                        </a>

                        <a href="{{ route('admin.temporary-id-cards.update-status') }}" class="btn btn-danger custom-btn">
                            Change Status
                        </a>
                    @endif

                    <a href="{{ route('admin.temporary-id-cards.index') }}" class="btn btn-secondary custom-btn">
                        Refresh
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-label">Total Cards</div>
                    <div class="stat-value">{{ number_format($counts->total_count ?? 0) }}</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ number_format($counts->pending_count ?? 0) }}</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-label">Downloaded</div>
                    <div class="stat-value">{{ number_format($counts->downloaded_count ?? 0) }}</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-label">Issued</div>
                    <div class="stat-value">{{ number_format($counts->issued_count ?? 0) }}</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-label">Active</div>
                    <div class="stat-value">{{ number_format($counts->active_count ?? 0) }}</div>
                </div>
            </div>

            <div class="col-md-2">
                <div class="stat-card">
                    <div class="stat-label">Expired</div>
                    <div class="stat-value">{{ number_format($counts->expired_count ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="main-card">

            <div class="filter-card">
                <form method="GET" action="{{ route('admin.temporary-id-cards.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Search</label>
                            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control custom-input"
                                placeholder="Search TMP number or card number">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select custom-input">
                                <option value="">All Status</option>
                                <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                                <option value="downloaded" @selected(($status ?? '') === 'downloaded')>Downloaded</option>
                                <option value="issued" @selected(($status ?? '') === 'issued')>Issued</option>
                                <option value="active" @selected(($status ?? '') === 'active')>Active</option>
                                <option value="expired" @selected(($status ?? '') === 'expired')>Expired</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary custom-btn flex-grow-1">
                                Search
                            </button>

                            <a href="{{ route('admin.temporary-id-cards.index') }}" class="btn btn-light border custom-btn">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>Temporary ID Number</th>
                            <th>Card Number</th>
                            <th>Status</th>
                            <th>Issue Date</th>
                            <th>Updated</th>
                            <th style="width: 240px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($temporaryIdCards as $card)
                            <tr>
                                <td>
                                    {{ $loop->iteration + ($temporaryIdCards->currentPage() - 1) * $temporaryIdCards->perPage() }}
                                </td>

                                <td class="mono fw-semibold">
                                    {{ $card->temporary_id_number }}
                                </td>

                                <td class="mono">
                                    {{ $card->card_number }}
                                </td>

                                <td>
                                    @if($card->status === 'pending')
                                        <span class="badge-status badge-pending">Pending</span>
                                    @elseif($card->status === 'downloaded')
                                        <span class="badge-status badge-downloaded">Downloaded</span>
                                    @elseif($card->status === 'issued')
                                        <span class="badge-status badge-active">Issued</span>
                                    @elseif($card->status === 'active')
                                        <span class="badge-status badge-active">Active</span>
                                    @elseif($card->status === 'expired')
                                        <span class="badge-status badge-expired">Expired</span>
                                    @else
                                        <span class="badge-status badge-expired">{{ ucfirst($card->status) }}</span>
                                    @endif
                                </td>

                                <td>
                                    {{ optional($card->created_at)->format('Y-m-d H:i') ?? '-' }}
                                </td>

                                <td>
                                    {{ optional($card->updated_at)->format('Y-m-d H:i') ?? '-' }}
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-2">

                                        <button type="button" class="btn btn-outline-primary btn-sm action-btn"
                                            data-bs-toggle="modal" data-bs-target="#tmpPreviewModal{{ $card->id }}">
                                            Preview
                                        </button>

                                    </div>


                                    @include('admin.temporary_id_cards.partials.preview-modal', ['card' => $card])

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="icon">🪪</div>
                                        <h5 class="fw-bold mb-1">No temporary ID cards found</h5>
                                        <p class="mb-0">Try adjusting your search or generate a new set of cards.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">
                <div class="text-muted small">
                    Showing {{ $temporaryIdCards->firstItem() ?? 0 }} to {{ $temporaryIdCards->lastItem() ?? 0 }}
                    of {{ $temporaryIdCards->total() }} records
                </div>

                <div>
                    {{ $temporaryIdCards->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection