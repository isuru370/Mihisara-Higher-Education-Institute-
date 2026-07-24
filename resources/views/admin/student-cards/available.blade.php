@extends('layouts.app')

@section('title', 'Available Student Cards')
@section('page-title', 'Available Student Cards')

@section('content')

<div class="halls-page">

    <!-- STATS -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <h3>{{ $cards->total() }}</h3>
                    <p>Available Cards</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon orange">
                    <i class="bi bi-person-x-fill"></i>
                </div>
                <div>
                    <h3>{{ $students->count() }}</h3>
                    <p>Students without Card</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0">
                        {{ optional(\App\Models\StudentCard::latest()->first())->created_at?->format('d M Y') ?? 'N/A' }}
                    </h6>
                    <p>Last Generated</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon purple">
                    <i class="bi bi-sort-numeric-up"></i>
                </div>
                <div>
                    <h3>{{ (\App\Models\StudentCard::max('card_sequence') ?? 0) + 1 }}</h3>
                    <p>Next Sequence</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- HEADER -->
        <div class="main-card-header">
            <div>
                <h4>Available Student Cards</h4>
                <p>Cards that are ready to be assigned to students. 
                    <span class="badge bg-success ms-1">{{ $cards->total() }} available</span>
                </p>
            </div>

            <div class="header-buttons">
                <a href="{{ route('admin.student-cards.generate') }}" class="btn btn-primary custom-btn">
                    <i class="bi bi-plus-circle"></i>
                    Generate Cards
                </a>

                <a href="{{ route('admin.student-cards.index') }}" class="btn btn-light border custom-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back to Inventory
                </a>
            </div>
        </div>

        <!-- ALERT MESSAGES -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show custom-alert">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show custom-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show custom-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- SEARCH -->
        <div class="search-card">
            <form method="GET" action="{{ route('admin.student-cards.available') }}">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" name="keyword" class="form-control custom-input"
                                placeholder="Search by Card Number or QR Code..." 
                                value="{{ request('keyword') }}">
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100 custom-btn">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>

                    <div class="col-lg-3">
                        <a href="{{ route('admin.student-cards.available') }}" class="btn btn-light border w-100 custom-btn">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
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
                        <th>Card Number</th>
                        <th>QR Code</th>
                        <th>Sequence</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $card)
                        <tr>
                            <td>{{ $cards->firstItem() + $loop->index }}</td>

                            <!-- CARD NUMBER -->
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="card-avatar">
                                        <i class="bi bi-credit-card"></i>
                                    </div>
                                    <div>
                                        <div class="hall-name">
                                            {{ $card->card_number }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- QR CODE -->
                            <td>
                                <code class="qr-code">{{ $card->qr_code }}</code>
                            </td>

                            <!-- SEQUENCE -->
                            <td>
                                <span class="badge bg-info custom-badge">{{ $card->card_sequence }}</span>
                            </td>

                            <!-- CREATED -->
                            <td>
                                {{ optional($card->created_at)->format('d M Y h:i A') }}
                            </td>

                            <!-- STATUS -->
                            <td>
                                <span class="badge bg-success custom-badge">Available</span>
                            </td>

                            <!-- ACTION -->
                            <td class="text-end">
                                <div class="action-buttons">
                                    <button type="button"
                                        class="action-btn toggle-btn assign-card-btn"
                                        data-card-id="{{ $card->id }}"
                                        data-card-number="{{ $card->card_number }}"
                                        data-card-qr="{{ $card->qr_code }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#assignCardModal"
                                        title="Assign to Student">
                                        <i class="bi bi-person-plus-fill"></i>
                                    </button>

                                    <a href="{{ route('admin.student-cards.show', $card->id) }}" 
                                       class="action-btn view-btn" title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5>No Available Cards Found</h5>
                                    <p>Generate new cards or change your search criteria.</p>
                                    <a href="{{ route('admin.student-cards.generate') }}" class="btn btn-primary custom-btn mt-3">
                                        <i class="bi bi-plus-circle"></i> Generate Cards Now
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($cards->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                <div class="text-muted">
                    Showing {{ $cards->firstItem() }} to {{ $cards->lastItem() }} 
                    of {{ $cards->total() }} results
                </div>
                <div>
                    {{ $cards->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

    </div>
</div>

<!-- Assign Card Modal -->
<div class="modal fade"
     id="assignCardModal"
     tabindex="-1"
     aria-labelledby="assignCardModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('admin.student-cards.assign') }}" id="assignForm">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title fw-bold" id="assignCardModalLabel">
                        <i class="bi bi-person-vcard-fill me-2"></i>
                        Assign Student Card
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Selected Card Info -->
                    <div class="alert alert-info custom-alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                            <div>
                                <strong>Assigning Card:</strong>
                                <span id="selectedCardInfo" class="fw-bold">-</span>
                                <span class="text-muted">|</span>
                                <span id="selectedCardQr" class="fw-bold">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Card Selection -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-credit-card me-1"></i>
                                Card
                                <span class="text-danger">*</span>
                            </label>
                            <select name="card_id"
                                    id="cardSelect"
                                    class="form-select custom-input select2"
                                    required>
                                <option value="">Select Card...</option>
                                @foreach($cards as $card)
                                    <option value="{{ $card->id }}" data-qr="{{ $card->qr_code }}">
                                        {{ $card->card_number }} - {{ $card->qr_code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('card_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Student Selection -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="bi bi-person me-1"></i>
                                Student
                                <span class="text-danger">*</span>
                            </label>
                            <select name="student_id"
                                    id="studentSelect"
                                    class="form-select custom-input select2"
                                    required>
                                <option value="">Select Student...</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                            data-has-card="{{ $student->currentCard ? 'true' : 'false' }}"
                                            data-card-number="{{ $student->currentCard?->card_number ?? '' }}">
                                        {{ $student->full_name }}
                                        @if($student->admission_no)
                                            ({{ $student->admission_no }})
                                        @endif
                                        @if($student->currentCard)
                                            <span class="text-warning">- Has Card: {{ $student->currentCard->card_number }}</span>
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Warning for students with current card -->
                    <div class="alert alert-warning mt-3 d-none custom-alert-warning" id="studentHasCardWarning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span id="studentHasCardMessage">This student already has a current card. Assigning a new card will replace the existing one.</span>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border custom-btn" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary custom-btn" id="assignSubmitBtn">
                        <i class="bi bi-check-circle"></i>
                        Assign Card
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        dropdownParent: $('#assignCardModal'),
        placeholder: 'Search...',
        allowClear: true,
        theme: 'bootstrap-5'
    });

    // Auto select card when Assign button is clicked
    $('.assign-card-btn').on('click', function () {
        let cardId = $(this).data('card-id');
        let cardNumber = $(this).data('card-number');
        let cardQr = $(this).data('card-qr');

        let cardOption = $('#cardSelect option[value="' + cardId + '"]');
        if (cardOption.length) {
            $('#cardSelect').val(cardId).trigger('change');
            $('#selectedCardInfo').text(cardNumber);
            $('#selectedCardQr').text(cardQr);
        }

        $('#studentSelect').val(null).trigger('change');
        $('#studentHasCardWarning').addClass('d-none');

        setTimeout(function() {
            $('#studentSelect').next('.select2-container').find('.select2-selection').click();
        }, 500);
    });

    // Check if student has current card
    $('#studentSelect').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        let hasCard = selectedOption.data('has-card') === 'true';
        let cardNumber = selectedOption.data('card-number') || '';

        if (hasCard) {
            $('#studentHasCardWarning').removeClass('d-none');
            $('#studentHasCardMessage').text(
                'This student already has a current card (' + cardNumber +
                '). Assigning a new card will replace the existing one.'
            );
        } else {
            $('#studentHasCardWarning').addClass('d-none');
        }
    });

    // Reset modal when closed
    $('#assignCardModal').on('hidden.bs.modal', function () {
        $('#cardSelect').val(null).trigger('change');
        $('#studentSelect').val(null).trigger('change');
        $('#studentHasCardWarning').addClass('d-none');
        $('#selectedCardInfo').text('-');
        $('#selectedCardQr').text('-');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    });

    // Form submit validation
    $('#assignForm').on('submit', function(e) {
        let cardId = $('#cardSelect').val();
        let studentId = $('#studentSelect').val();

        if (!cardId) {
            e.preventDefault();
            $('#cardSelect').addClass('is-invalid');
            if (!$('#cardSelect').next('.invalid-feedback').length) {
                $('#cardSelect').after(
                    '<div class="invalid-feedback d-block">Please select a card.</div>'
                );
            }
            return false;
        }

        if (!studentId) {
            e.preventDefault();
            $('#studentSelect').addClass('is-invalid');
            if (!$('#studentSelect').next('.invalid-feedback').length) {
                $('#studentSelect').after(
                    '<div class="invalid-feedback d-block">Please select a student.</div>'
                );
            }
            return false;
        }

        let submitBtn = $('#assignSubmitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html(`
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Assigning...
        `);
    });

    // Clear validation errors on change
    $('#cardSelect, #studentSelect').on('change', function() {
        $(this).removeClass('is-invalid');
        $(this).next('.invalid-feedback').remove();
    });
});

// Show modal on page load if there are errors
@if($errors->any())
    $(document).ready(function() {
        let modal = new bootstrap.Modal(document.getElementById('assignCardModal'));
        modal.show();

        @if(old('card_id'))
            $('#cardSelect').val('{{ old('card_id') }}').trigger('change');
            let selectedCard = $('#cardSelect option:selected');
            $('#selectedCardInfo').text(selectedCard.text().split(' - ')[0]);
            $('#selectedCardQr').text(selectedCard.text().split(' - ')[1] || '');
        @endif

        @if(old('student_id'))
            $('#studentSelect').val('{{ old('student_id') }}').trigger('change');
        @endif
    });
@endif
</script>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* ===== HALLS PAGE STYLES ===== */
    .halls-page {
        animation: fadeIn 0.4s ease;
    }

    /* ===== STATS CARDS ===== */
    .stats-card {
        background: #fff;
        border-radius: 24px;
        padding: 1.5rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .04);
        border: 1px solid #eef2f7;
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .green { background: linear-gradient(135deg, #10b981, #34d399); }
    .orange { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .red { background: linear-gradient(135deg, #ef4444, #f87171); }
    .purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }

    .stats-card h3 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 700;
        color: #0f172a;
    }
    .stats-card h6 {
        margin: 0;
        font-size: 1rem;
    }
    .stats-card p {
        margin: 0;
        color: #64748b;
        font-weight: 500;
        font-size: 0.9rem;
    }

    /* ===== MAIN CARD ===== */
    .main-card {
        background: #fff;
        border-radius: 28px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        margin-top: 20px;
    }

    /* ===== HEADER ===== */
    .main-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .main-card-header h4 {
        margin: 0;
        font-weight: 700;
        color: #0f172a;
        font-size: 1.3rem;
    }
    .main-card-header p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .header-buttons {
        display: flex;
        gap: .7rem;
        flex-wrap: wrap;
    }

    /* ===== CUSTOM BUTTONS ===== */
    .custom-btn {
        border-radius: 14px;
        padding: .7rem 1.2rem;
        font-weight: 600;
        border: none;
        transition: all .2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    .custom-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    /* ===== ALERTS ===== */
    .custom-alert {
        border-radius: 16px;
        border: none;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    .custom-alert i {
        font-size: 1.3rem;
    }

    .custom-alert-info {
        border-radius: 14px;
        border: 1px solid #e0f2fe;
        background: #f0f9ff;
        color: #0369a1;
    }

    .custom-alert-warning {
        border-radius: 14px;
        border: 1px solid #fef3c7;
        background: #fffbeb;
        color: #92400e;
    }

    /* ===== SEARCH CARD ===== */
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
        font-size: 1.1rem;
    }

    .custom-input {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        min-height: 48px;
        padding-left: 42px;
        background: #fff;
        transition: all .2s ease;
    }
    .custom-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
    }
    .custom-input[type="text"] {
        padding-left: 42px;
    }

    /* ===== TABLE ===== */
    .custom-table thead th {
        border: none;
        background: #f8fafc;
        color: #475569;
        font-size: .82rem;
        text-transform: uppercase;
        padding: 1rem;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .custom-table tbody tr {
        transition: .2s ease;
        border-bottom: 1px solid #f1f5f9;
    }
    .custom-table tbody tr:hover {
        background: #f8fafc;
    }

    .custom-table tbody td {
        padding: 1rem;
        border-color: #f1f5f9;
        vertical-align: middle;
    }

    .card-avatar {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        box-shadow: 0 8px 18px rgba(16, 185, 129, .20);
        flex-shrink: 0;
    }

    .hall-name {
        font-weight: 700;
        color: #0f172a;
    }

    .qr-code {
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.8rem;
        color: #1e293b;
        font-weight: 600;
    }

    .custom-badge {
        border-radius: 10px;
        padding: .5rem .7rem;
        font-size: .75rem;
        font-weight: 600;
    }

    /* ===== ACTION BUTTONS ===== */
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
        transition: all .2s ease;
        font-size: 0.9rem;
        cursor: pointer;
    }
    .action-btn:hover {
        transform: translateY(-2px);
    }

    .view-btn {
        background: #eff6ff;
        color: #2563eb;
    }
    .view-btn:hover {
        background: #2563eb;
        color: #fff;
    }

    .edit-btn {
        background: #fef3c7;
        color: #d97706;
    }
    .edit-btn:hover {
        background: #d97706;
        color: #fff;
    }

    .toggle-btn {
        background: #ecfdf5;
        color: #10b981;
    }
    .toggle-btn:hover {
        background: #10b981;
        color: #fff;
    }

    .delete-btn {
        background: #fef2f2;
        color: #ef4444;
    }
    .delete-btn:hover {
        background: #ef4444;
        color: #fff;
    }

    .assign-card-btn {
        background: #ecfdf5;
        color: #10b981;
    }
    .assign-card-btn:hover {
        background: #10b981;
        color: #fff;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state i {
        font-size: 3.5rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
        display: block;
    }
    .empty-state h5 {
        font-weight: 700;
        color: #1e293b;
    }
    .empty-state p {
        color: #64748b;
    }

    /* ===== MODAL ===== */
    .custom-modal-header {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #fff;
        border-radius: 16px 16px 0 0;
        padding: 1.5rem;
    }
    .custom-modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }

    /* ===== PAGINATION ===== */
    .pagination .page-link {
        border-radius: 10px !important;
        border: none;
        padding: 0.6rem 1rem;
        color: #475569;
        font-weight: 500;
        margin: 0 3px;
        transition: all .2s ease;
    }
    .pagination .page-link:hover {
        background: #eef2f7;
        color: #0f172a;
    }
    .pagination .page-item.active .page-link {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .30);
    }

    /* ===== SELECT2 OVERRIDES ===== */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 48px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 46px;
        padding-left: 15px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 46px;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-color: #e2e8f0;
        border-radius: 14px;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #0f172a;
    }

    /* ===== RESPONSIVE ===== */
    @media(max-width: 768px) {
        .main-card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .header-buttons {
            width: 100%;
        }

        .header-buttons a,
        .header-buttons button {
            flex: 1;
        }

        .stats-card {
            padding: 1rem;
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }

        .stats-card h3 {
            font-size: 1.3rem;
        }

        .action-buttons {
            justify-content: flex-start;
        }

        .main-card {
            padding: 1rem;
        }

        .search-card {
            padding: 0.8rem;
        }
    }

    @media(max-width: 576px) {
        .table-responsive {
            font-size: 0.85rem;
        }

        .card-avatar {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .custom-btn {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }

        .stats-card h3 {
            font-size: 1.1rem;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush