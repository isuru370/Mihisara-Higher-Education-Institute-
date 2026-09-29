@extends('layouts.app')

@section('title', 'Fee Options')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Fee Options</h4>

            @if($classCategoryFee)
                <div class="text-muted">
                    {{ $classCategoryFee->studentClass->class_name ?? 'Class' }}
                    -
                   {{ $classCategoryFee->category->category_name ?? '-' }}
                </div>
            @endif
        </div>

        @if($classCategoryFee)
            <a href="{{ route('admin.class-category-fee-options.create', [
                'class_category_fee_id' => $classCategoryFee->id
            ]) }}"
               class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
                Add Fee Option
            </a>
        @endif
    </div>


    {{-- Category Fee Information --}}
    @if($classCategoryFee)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">
                        <div class="text-muted small">
                            Class
                        </div>

                        <div class="fw-semibold">
                            {{ $classCategoryFee->studentClass->class_name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">
                            Category
                        </div>

                        <div class="fw-semibold">
                            {{ $classCategoryFee->category->category_name ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">
                            Base Fee
                        </div>

                        <div class="fw-semibold">
                            Rs. {{ number_format($classCategoryFee->fee, 2) }}
                        </div>
                    </div>

                </div>

            </div>
        </div>
    @endif


    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Fee Options --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                Available Fee Options
            </h5>
        </div>

        <div class="card-body p-0">

            @if($options->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Option</th>
                            <th>Fee</th>
                            <th>Default</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($options as $option)

                            <tr>

                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $option->label }}
                                    </div>
                                </td>

                                <td>
                                    <span class="fw-semibold">
                                        Rs. {{ number_format($option->fee, 2) }}
                                    </span>
                                </td>

                                <td>

                                    @if($option->is_default)

                                        <span class="badge bg-primary">
                                            Default
                                        </span>

                                    @else

                                        <form method="POST"
                                              action="{{ route(
                                                  'admin.class-category-fee-options.set-default',
                                                  $option->id
                                              ) }}"
                                              class="d-inline">

                                            @csrf

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-secondary">
                                                Set Default
                                            </button>

                                        </form>

                                    @endif

                                </td>

                                <td>

                                    @if($option->is_active)

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            Inactive
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    <span class="text-muted">
                                        {{ $option->note ?: '-' }}
                                    </span>
                                </td>

                                <td class="text-end">

                                    <div class="d-inline-flex gap-1">

                                        {{-- Edit --}}
                                        <a href="{{ route(
                                            'admin.class-category-fee-options.edit',
                                            $option->id
                                        ) }}"
                                           class="btn btn-sm btn-outline-primary">

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        {{-- Activate --}}
                                        @if(!$option->is_active)

                                            <form method="POST"
                                                  action="{{ route(
                                                      'admin.class-category-fee-options.activate',
                                                      $option->id
                                                  ) }}"
                                                  class="d-inline">

                                                @csrf

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Activate">

                                                    <i class="bi bi-check-lg"></i>

                                                </button>

                                            </form>

                                        @else

                                            {{-- Deactivate --}}
                                            <form method="POST"
                                                  action="{{ route(
                                                      'admin.class-category-fee-options.deactivate',
                                                      $option->id
                                                  ) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to deactivate this fee option?');">

                                                @csrf

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-warning"
                                                        title="Deactivate">

                                                    <i class="bi bi-pause"></i>

                                                </button>

                                            </form>

                                        @endif


                                        {{-- Delete --}}
                                        <form method="POST"
                                              action="{{ route(
                                                  'admin.class-category-fee-options.destroy',
                                                  $option->id
                                              ) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this fee option?');">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center py-5">

                    <div class="mb-3">
                        <i class="bi bi-cash-stack fs-1 text-muted"></i>
                    </div>

                    <h5>No Fee Options Found</h5>

                    <p class="text-muted mb-4">
                        Create the first fee option for this category.
                    </p>

                    @if($classCategoryFee)

                        <a href="{{ route(
                            'admin.class-category-fee-options.create',
                            ['class_category_fee_id' => $classCategoryFee->id]
                        ) }}"
                           class="btn btn-primary">

                            <i class="bi bi-plus-lg"></i>
                            Add Fee Option

                        </a>

                    @endif

                </div>

            @endif

        </div>

    </div>

</div>

@endsection