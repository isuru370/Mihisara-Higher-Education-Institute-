@extends('layouts.app')

@section('title', 'Class Categories')
@section('page-title', 'Class Categories')

@section('content')

    <div class="categories-page">

        <!-- HERO -->
        <div class="hero-card mb-4">
            <div class="hero-content">
                <div>
                    <h4 class="mb-1 fw-bold">Class Categories</h4>
                    <p class="mb-0 text-muted">
                        Manage Theory, Revision and Paper categories.
                    </p>
                </div>

                <div class="hero-actions">
                    <a href="{{ route('admin.class-categories.create') }}" class="btn btn-primary custom-btn">
                        <i class="bi bi-plus-lg me-1"></i>
                        Add Category
                    </a>

                    <a href="{{ route('admin.student-classes.index') }}" class="btn btn-outline-secondary custom-btn">
                        <i class="bi bi-journal-bookmark-fill me-1"></i>
                        View Classes
                    </a>

                    <!-- Future feature -->
                    <button class="btn btn-light border custom-btn" disabled>
                        <i class="bi bi-graph-up-arrow me-1"></i>
                        Analytics
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN CARD -->
        <div class="main-card">

            <!-- SEARCH -->
            <div class="search-card">

                <form method="GET" action="{{ route('admin.class-categories.index') }}">
                    <div class="row g-3">

                        <div class="col-lg-5">
                            <div class="search-input-wrapper">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control custom-input"
                                    placeholder="Search category / code" value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <select name="is_active" class="form-select custom-input">
                                <option value="">All Status</option>
                                <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>Active</option>
                                <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <button class="btn btn-primary w-100 custom-btn" type="submit">
                                Search
                            </button>
                        </div>

                        <div class="col-lg-2">
                            <a href="{{ route('admin.class-categories.index') }}"
                                class="btn btn-light border w-100 custom-btn">
                                Reset
                            </a>
                        </div>

                    </div>
                </form>

            </div>

            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table custom-table align-middle">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Code</th>
                            <th>Schedulable</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    {{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}
                                </td>

                                <!-- CATEGORY -->
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="category-avatar">
                                            {{ strtoupper(substr($category->category_name, 0, 1)) }}
                                        </div>

                                        <div>
                                            <div class="category-name">
                                                {{ $category->category_name }}
                                            </div>

                                            <small class="text-muted">
                                                Class category item
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- CODE -->
                                <td>
                                    <span class="badge bg-light text-dark border custom-badge">
                                        {{ $category->code }}
                                    </span>
                                </td>

                                <!-- SCHEDULABLE -->
                                <td>
                                    @if($category->is_schedulable)
                                        <span class="badge bg-info text-dark custom-badge">Yes</span>
                                    @else
                                        <span class="badge bg-light text-dark border custom-badge">No</span>
                                    @endif
                                </td>

                                <!-- STATUS -->
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success custom-badge">Active</span>
                                    @else
                                        <span class="badge bg-secondary custom-badge">Inactive</span>
                                    @endif
                                </td>

                                <!-- ACTIONS -->
                                <td class="text-end">
                                    <div class="action-buttons">

                                        <a href="{{ route('admin.class-categories.show', $category) }}"
                                            class="action-btn view-btn" title="View">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>

                                        <a href="{{ route('admin.class-categories.edit', $category) }}"
                                            class="action-btn edit-btn" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>

                                        <form method="POST"
                                            action="{{ route('admin.class-categories.toggleActive', $category) }}"
                                            onsubmit="return confirm('Change active status?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="action-btn toggle-btn" title="Toggle Active">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.class-categories.destroy', $category) }}"
                                            onsubmit="return confirm('Delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" title="Delete">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="empty-state">
                                        <i class="bi bi-tags"></i>
                                        <h5>No Categories Found</h5>
                                        <p>Try adjusting the search filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            <div class="mt-4">
                {{ $categories->links() }}
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <style>
        .categories-page {
            animation: fadeIn 0.4s ease;
        }

        .hero-card,
        .main-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            overflow: hidden;
        }

        .hero-card {
            padding: 1.35rem 1.5rem;
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

        .search-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-input-wrapper i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #64748b;
        }

        .custom-input {
            border-radius: 14px !important;
            border: 1px solid #e2e8f0;
            min-height: 48px;
            padding-left: 42px;
        }

        .custom-input:focus {
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

        .custom-table thead th {
            border: none;
            background: #f8fafc;
            color: #475569;
            font-size: .82rem;
            text-transform: uppercase;
            padding: 1rem;
            vertical-align: middle;
        }

        .custom-table tbody tr {
            transition: .2s ease;
        }

        .custom-table tbody tr:hover {
            background: #f8fafc;
        }

        .custom-table tbody td {
            padding: 1rem;
            border-color: #f1f5f9;
            vertical-align: middle;
        }

        .category-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: 0 8px 18px rgba(79, 70, 229, .20);
        }

        .category-name {
            font-weight: 700;
        }

        .badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
        }

        .custom-badge {
            border-radius: 10px;
            padding: .5rem .7rem;
            font-size: .75rem;
        }

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
            display: inline-flex;
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

        .toggle-btn {
            background: #ecfdf5;
            color: #10b981;
        }

        .delete-btn {
            background: #fef2f2;
            color: #ef4444;
        }

        .empty-state {
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 700;
        }

        @media(max-width:768px) {
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