@extends('layouts.app')

@section('title', 'Generate Student Cards')
@section('page-title', 'Generate Student Cards')

@section('content')

<div class="halls-page">

    <!-- STATS -->
    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="bi bi-person-vcard-fill"></i>
                </div>
                <div>
                    <h3>{{ \App\Models\StudentCard::count() }}</h3>
                    <p>Total Cards</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <h3>{{ \App\Models\StudentCard::where('status', 'available')->count() }}</h3>
                    <p>Available Cards</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon purple">
                    <i class="bi bi-sort-numeric-up"></i>
                </div>
                <div>
                    <h3>{{ (\App\Models\StudentCard::max('card_sequence') ?? 0) + 1 }}</h3>
                    <p>Next Card Sequence</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="main-card">

        <!-- HEADER -->
        <div class="main-card-header">
            <div>
                <h4>Generate Student Cards</h4>
                <p>Generate a new batch of student cards for the system</p>
            </div>

            <div class="header-buttons">
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

        <!-- GENERATION FORM -->
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="form-card">
                    <form method="POST" action="{{ route('admin.student-cards.generate.store') }}">
                        @csrf

                        <div class="form-body">
                            <!-- Quantity Input -->
                            <div class="form-group mb-4">
                                <label for="quantity" class="form-label fw-bold">
                                    <i class="bi bi-hash me-1"></i>
                                    Number of Cards
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="number"
                                       id="quantity"
                                       name="quantity"
                                       class="form-control custom-input @error('quantity') is-invalid @enderror"
                                       min="1"
                                       max="1000"
                                       value="{{ old('quantity', 100) }}"
                                       required>

                                @error('quantity')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Enter number of cards to generate (1-1000)
                                    </small>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        Estimated time: {{ ceil(($quantity ?? 100) / 10) }} seconds
                                    </small>
                                </div>
                            </div>

                            <!-- Quick Select Buttons -->
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-grid-3x3-gap-fill me-1"></i>
                                    Quick Select
                                </label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn quick-btn" onclick="setQuantity(10)">10</button>
                                    <button type="button" class="btn quick-btn" onclick="setQuantity(25)">25</button>
                                    <button type="button" class="btn quick-btn" onclick="setQuantity(50)">50</button>
                                    <button type="button" class="btn quick-btn active" onclick="setQuantity(100)">100</button>
                                    <button type="button" class="btn quick-btn" onclick="setQuantity(250)">250</button>
                                    <button type="button" class="btn quick-btn" onclick="setQuantity(500)">500</button>
                                    <button type="button" class="btn quick-btn" onclick="setQuantity(1000)">1000</button>
                                </div>
                            </div>

                            <!-- Card Format Preview -->
                            <div class="preview-card mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-eye me-2"></i>
                                    Card Format Preview
                                </h6>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="preview-item">
                                            <small class="text-muted d-block">Card Number</small>
                                            <strong class="text-primary" id="previewCardNumber">
                                                MIHISARA{{ str_pad(($lastSequence ?? 0) + 1, 6, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            <small class="text-muted d-block mt-1">
                                                Format: MIHISARA + 6-digit sequence
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="preview-item">
                                            <small class="text-muted d-block">QR Code</small>
                                            <strong class="text-success" id="previewQrCode">
                                                ST{{ str_pad(($lastSequence ?? 0) + 1, 3, '0', STR_PAD_LEFT) }}
                                            </strong>
                                            <small class="text-muted d-block mt-1">
                                                Format: ST + sequence (auto-increment)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Information Alert -->
                            <div class="alert custom-alert-info">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bi bi-info-circle-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading fw-bold">What happens next?</h6>
                                        <p class="mb-1">
                                            Each generated card will receive:
                                        </p>
                                        <ul class="mb-0 ps-3">
                                            <li>A unique <strong>Card Number</strong> (MIHISARA + sequence)</li>
                                            <li>A unique <strong>QR Code</strong> (ST + sequence)</li>
                                            <li>Status set to <span class="badge bg-success custom-badge">Available</span></li>
                                            <li>Added to the card inventory</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Warning for large batches -->
                            <div class="alert custom-alert-warning d-none" id="largeBatchWarning">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading fw-bold">Large Batch Notice</h6>
                                        <p class="mb-0">
                                            Generating a large number of cards may take a few moments.
                                            Please do not close the browser while processing.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <span class="text-muted" id="cardCountText">
                                        <i class="bi bi-card-list me-1"></i>
                                        Generating <strong id="cardCount">{{ old('quantity', 100) }}</strong> card(s)
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.student-cards.index') }}" class="btn btn-light border custom-btn">
                                        <i class="bi bi-x-circle"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary custom-btn" id="generateBtn">
                                        <i class="bi bi-plus-circle"></i>
                                        Generate Cards
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    // Set quantity function for quick select buttons
    function setQuantity(value) {
        const input = document.getElementById('quantity');
        input.value = value;
        updatePreview(value);
        checkBatchSize(value);
        
        // Update active state on quick buttons
        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.classList.remove('active');
            if (parseInt(btn.textContent) === parseInt(value)) {
                btn.classList.add('active');
            }
        });
    }

    // Update preview function
    function updatePreview(value) {
        const lastSequence = {{ \App\Models\StudentCard::max('card_sequence') ?? 0 }};
        const nextSequence = lastSequence + parseInt(value);
        
        // Update card number preview
        const cardNumberPreview = document.getElementById('previewCardNumber');
        const lastCardNumber = 'MIHISARA' + String(nextSequence).padStart(6, '0');
        cardNumberPreview.textContent = lastCardNumber;

        // Update QR code preview
        const qrCodePreview = document.getElementById('previewQrCode');
        const lastQrCode = 'ST' + String(nextSequence).padStart(3, '0');
        qrCodePreview.textContent = lastQrCode;

        // Update card count text
        const cardCount = document.getElementById('cardCount');
        cardCount.textContent = value;
    }

    // Check batch size warning
    function checkBatchSize(value) {
        const warning = document.getElementById('largeBatchWarning');
        if (parseInt(value) > 250) {
            warning.classList.remove('d-none');
        } else {
            warning.classList.add('d-none');
        }
    }

    // Real-time quantity change listener
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        
        quantityInput.addEventListener('input', function() {
            const value = this.value || 0;
            updatePreview(value);
            checkBatchSize(value);
            
            // Update active state on quick buttons
            document.querySelectorAll('.quick-btn').forEach(btn => {
                btn.classList.remove('active');
                if (parseInt(btn.textContent) === parseInt(value)) {
                    btn.classList.add('active');
                }
            });
        });

        // Update on load
        const initialValue = quantityInput.value || 100;
        updatePreview(initialValue);
        checkBatchSize(initialValue);
        
        // Set initial active button
        document.querySelectorAll('.quick-btn').forEach(btn => {
            if (parseInt(btn.textContent) === parseInt(initialValue)) {
                btn.classList.add('active');
            }
        });

        // Form submit loading state
        const form = document.querySelector('form');
        const generateBtn = document.getElementById('generateBtn');
        
        form.addEventListener('submit', function(e) {
            generateBtn.disabled = true;
            generateBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Generating...
            `;
        });
    });
</script>
@endpush

@push('styles')
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
        animation: fadeIn 0.3s ease-in;
    }

    /* ===== FORM CARD ===== */
    .form-card {
        background: #f8fafc;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #eef2f7;
    }

    .form-body {
        padding: 2rem;
    }

    .form-footer {
        background: #fff;
        padding: 1.5rem 2rem;
        border-top: 1px solid #eef2f7;
    }

    /* ===== FORM INPUTS ===== */
    .custom-input {
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        min-height: 52px;
        padding: 0 1rem;
        background: #fff;
        transition: all .2s ease;
        font-size: 1rem;
    }
    .custom-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
    }
    .custom-input.is-invalid {
        border-color: #ef4444;
    }
    .custom-input.is-invalid:focus {
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .10);
    }

    .form-label {
        color: #0f172a;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    /* ===== QUICK BUTTONS ===== */
    .quick-btn {
        border-radius: 12px;
        padding: 0.5rem 1.2rem;
        background: #fff;
        border: 2px solid #e2e8f0;
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all .2s ease;
        cursor: pointer;
    }
    .quick-btn:hover {
        border-color: #2563eb;
        color: #2563eb;
        transform: translateY(-2px);
    }
    .quick-btn.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .30);
    }

    /* ===== PREVIEW CARD ===== */
    .preview-card {
        background: #fff;
        border-radius: 16px;
        padding: 1.5rem;
        border: 2px dashed #e2e8f0;
    }

    .preview-item {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 12px;
        text-align: center;
        border: 1px solid #eef2f7;
        transition: all .2s ease;
    }
    .preview-item:hover {
        border-color: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, .08);
    }

    #previewCardNumber, #previewQrCode {
        font-size: 1.2rem;
        letter-spacing: 0.5px;
        display: block;
        padding: 0.3rem 0;
    }

    /* ===== BADGE ===== */
    .custom-badge {
        border-radius: 10px;
        padding: .4rem .7rem;
        font-size: .75rem;
        font-weight: 600;
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

        .main-card {
            padding: 1rem;
        }

        .form-body {
            padding: 1.5rem;
        }

        .form-footer {
            padding: 1rem 1.5rem;
        }

        .quick-btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
    }

    @media(max-width: 576px) {
        .stats-card h3 {
            font-size: 1.1rem;
        }

        .custom-btn {
            padding: 0.5rem 0.8rem;
            font-size: 0.8rem;
        }

        .preview-item {
            padding: 0.8rem;
        }

        #previewCardNumber, #previewQrCode {
            font-size: 1rem;
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