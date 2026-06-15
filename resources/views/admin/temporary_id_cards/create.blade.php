@extends('layouts.app')

@section('title', 'Generate Temporary ID Cards')
@section('page-title', 'Generate Temporary ID Cards')

@section('content')

    <style>
        .tmp-card {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .preview-card {
            width: 100%;
            max-width: 341px;
            margin: 0 auto;
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            border-radius: 12px;
            padding: 15px;
            color: white;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .qr-placeholder {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-placeholder::after {
            content: 'QR';
            color: #4e73df;
            font-weight: bold;
            font-size: 14px;
        }

        .calculation-box {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
            border-left: 4px solid #4e73df;
        }

        .number-highlight {
            font-size: 28px;
            font-weight: 700;
            color: #4e73df;
            line-height: 1.2;
            transition: .2s;
        }

        .input-group-custom .input-group-text {
            background-color: #4e73df;
            color: white;
            border: none;
            font-weight: 600;
        }

        .input-group-custom .form-control {
            border: 2px solid #e3e6f0;
            border-left: none;
        }

        .input-group-custom .form-control:focus {
            border-color: #4e73df;
            box-shadow: none;
        }

        .btn-generate {
            background: #4e73df;
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-generate:hover {
            background: #224abe;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
            color: white;
        }

        .info-badge {
            background: #f8f9fc;
            border-radius: 20px;
            padding: 8px 15px;
            color: #4e73df;
            font-size: 14px;
        }

        .warning-text {
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>

    <div class="container-fluid py-3">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h3 class="fw-bold mb-1">Generate Temporary ID Cards</h3>
                <p class="text-muted mb-0">
                    Generate temporary ID cards with QR codes and export PDF.
                </p>
            </div>

            <a href="{{ route('admin.temporary-id-cards.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
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

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">

            <div class="col-lg-8">

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">

                        <div class="alert alert-info border-0 bg-light d-flex align-items-start">
                            <i class="bi bi-info-circle-fill text-primary fs-4 me-3"></i>

                            <div>
                                <strong>Quick Guide</strong><br>
                                Enter number range like 001 to 9999. Unlimited generation supported.
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.temporary-id-cards.store') }}" id="generateForm">

                            @csrf

                            <div class="row g-4 mb-4">

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">
                                        Start Number
                                    </label>

                                    <div class="input-group input-group-custom">

                                        <span class="input-group-text">
                                            TMP
                                        </span>

                                        <input type="text" name="start"
                                            class="form-control form-control-lg @error('start') is-invalid @enderror"
                                            placeholder="001" value="{{ old('start', '001') }}" maxlength="6"
                                            pattern="[0-9]+" required>

                                    </div>

                                    <small class="text-muted">
                                        Example: 001
                                    </small>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">
                                        End Number
                                    </label>

                                    <div class="input-group input-group-custom">

                                        <span class="input-group-text">
                                            TMP
                                        </span>

                                        <input type="text" name="end"
                                            class="form-control form-control-lg @error('end') is-invalid @enderror"
                                            placeholder="012" value="{{ old('end', '012') }}" maxlength="6" pattern="[0-9]+"
                                            required>

                                    </div>

                                    <small class="text-muted">
                                        Must be greater than start
                                    </small>

                                </div>

                            </div>

                            <div class="calculation-box mb-4">

                                <div class="row text-center">

                                    <div class="col-4">
                                        <div class="text-muted small">
                                            Total Cards
                                        </div>

                                        <div class="number-highlight" id="totalCards">
                                            12
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="text-muted small">
                                            Pages
                                        </div>

                                        <div class="number-highlight text-success" id="totalPages">
                                            1
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="text-muted small">
                                            Per Page
                                        </div>

                                        <div class="number-highlight text-info">
                                            12
                                        </div>
                                    </div>

                                </div>

                                <div id="largeNumberWarning" class="warning-text mt-3" style="display:none;">
                                    Large amount of cards selected.
                                </div>

                            </div>

                            <button type="submit" class="btn btn-generate w-100" id="submitBtn">

                                <i class="bi bi-upc-scan me-2"></i>
                                Generate Temporary ID Cards

                            </button>

                        </form>

                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">

                        <h5 class="fw-bold mb-4">
                            Card Preview
                        </h5>

                        <div class="preview-card">

                            <div class="d-flex align-items-center mb-3">

                                <div class="bg-white rounded-circle p-2 me-2">
                                    <span class="text-primary fw-bold">
                                        🏢
                                    </span>
                                </div>

                                <span class="fw-bold">
                                    TEMP ID
                                </span>

                            </div>

                            <div class="d-flex gap-3">

                                <div class="qr-placeholder"></div>

                                <div>

                                    <div class="fw-bold fs-4">
                                        TMP001
                                    </div>

                                    <div class="small opacity-75">
                                        #0001
                                    </div>

                                    <div class="badge bg-white text-primary mt-2">
                                        Active
                                    </div>

                                </div>

                            </div>

                            <div class="text-center small mt-3 pt-2 border-top border-white border-opacity-25">
                                Temporary ID Card
                            </div>

                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">

                            <span class="info-badge">
                                QR Enabled
                            </span>

                            <span class="info-badge">
                                PDF Export
                            </span>

                            <span class="info-badge">
                                12 Per Page
                            </span>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const startInput = document.querySelector('input[name="start"]');

            const endInput = document.querySelector('input[name="end"]');

            const totalCardsSpan = document.getElementById('totalCards');

            const totalPagesSpan = document.getElementById('totalPages');

            const warningDiv = document.getElementById('largeNumberWarning');

            function updateCalculation() {

                let startVal = startInput.value.replace(/\D/g, '');

                let endVal = endInput.value.replace(/\D/g, '');

                startInput.value = startVal;

                endInput.value = endVal;

                let start = startVal ? parseInt(startVal) : 1;

                let end = endVal ? parseInt(endVal) : 12;

                if (isNaN(start) || start < 1) start = 1;

                if (isNaN(end) || end < start) end = start;

                const total = (end - start) + 1;

                const pages = Math.ceil(total / 12);

                totalCardsSpan.textContent = total.toLocaleString();

                totalPagesSpan.textContent = pages.toLocaleString();

                if (total > 1000) {
                    warningDiv.style.display = 'block';
                } else {
                    warningDiv.style.display = 'none';
                }
            }

            startInput.addEventListener('input', updateCalculation);

            endInput.addEventListener('input', updateCalculation);

            updateCalculation();
        });
    </script>

@endsection