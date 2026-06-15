@extends('layouts.app')

@section('title', 'Temporary ID Cards Preview')
@section('page-title', 'Temporary ID Cards Preview')

@section('content')
    <style>
        :root {
            --blue: #0b3d91;
            --blue-dark: #052c74;
            --green: #79d11a;
            --green-dark: #38a51e;
            --teal: #0fa7b8;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        .preview-wrapper {
            background: #f1f5f9;
            padding: 24px;
            border-radius: 24px;
        }

        .a4-page {
            width: 29.7cm;
            min-height: 21cm;
            margin: 0 auto 30px;
            background: #fff;
            padding: 0.4cm;
            border-radius: 18px;
            box-shadow: var(--card-shadow);
            page-break-after: always;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .35cm;
        }

        .id-card {
            width: 9cm;
            height: 5.05cm;
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            background: url('{{ asset('storage/id/temporary.png') }}') center/cover no-repeat;
            margin: auto;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .12);
        }

        .logo-section {
            position: absolute;
            top: 1.08cm;
            left: .38cm;
            z-index: 2;
        }

        .logo-img {
            position: absolute;
            left: 0.05cm;
            top: 0.05cm;
            width: 2cm;
            height: 2cm;
            object-fit: contain;
            display: block;
        }

        .institute-name {
            position: absolute;
            left: 2.00cm;
            top: .55cm;
            line-height: 1.2;
            width: 3.8cm;
        }

        .institute-name .title {
            font-size: 0.50cm;
            font-weight: 800;
            color: #08348f;
            letter-spacing: .01cm;
            white-space: nowrap;
        }

        .institute-name .sub {
            font-size: .28cm;
            font-weight: 800;
            color: #38a51e;
            letter-spacing: .03cm;
            margin-top: .05cm;
            white-space: nowrap;
        }

        .institute-name .name-line {
            width: 2.55cm;
            height: .06cm;
            border-radius: 999px;
            background: linear-gradient(90deg, #79d11a 0%, #38a51e 50%, #0fa7b8 100%);
            margin-top: .10cm;
            box-shadow: 0 1px 4px rgba(56, 165, 30, .25);
        }

        .qr-wrapper {
            position: absolute;
            top: 1.10cm;
            right: .60cm;
            width: 2.45cm;
            height: 2.45cm;
            border-radius: .22cm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-sizing: border-box;
            z-index: 2;
            background: transparent;
            border: none;
        }

        .qr-wrapper img {
            width: 88%;
            height: 88%;
            object-fit: contain;
            display: block;
        }

        .tmp-code {
            position: absolute;
            right: .30cm;
            bottom: .61cm;
            width: 3.10cm;
            height: .70cm;
            border-radius: .18cm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: .40cm;
            font-weight: 800;
            letter-spacing: .02cm;
            z-index: 2;
        }

        .page-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 13px;
            color: #64748b;
        }

        .no-cards {
            background: #fff;
            border-radius: 18px;
            padding: 48px 24px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
        }

        .preview-form-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .05);
            border: 1px solid #eef2f7;
            padding: 1rem;
        }

        .custom-input {
            border-radius: 14px;
            min-height: 48px;
            border: 1px solid #e2e8f0;
            box-shadow: none;
        }

        .custom-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0.4cm;
            }

            body {
                margin: 0;
                background: #fff;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .preview-wrapper {
                padding: 0;
                background: #fff;
            }

            .a4-page {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
                width: auto;
                min-height: auto;
            }
        }
    </style>

    <div class="container-fluid py-3 no-print">

        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.temporary-id-cards.preview-generate') }}">
                    @csrf

                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Start TMP Number</label>
                            <input type="text" name="start" class="form-control custom-input" placeholder="TMP500"
                                value="{{ $start ?? old('start') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">End TMP Number</label>
                            <input type="text" name="end" class="form-control custom-input" placeholder="TMP620"
                                value="{{ $end ?? old('end') }}">
                        </div>

                        <div class="col-md-4 d-grid">
                            <button type="submit" class="btn btn-success rounded-pill px-4">
                                Generate
                            </button>
                        </div>
                    </div>
                </form>

                @if(session('error'))
                    <div class="alert alert-danger border-0 mt-3 mb-0">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success border-0 mt-3 mb-0">
                        {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>

        @if(isset($codes) && $codes->count())
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted">
                        <strong>Total Cards:</strong> {{ $codes->count() }}
                        |
                        <strong>Total Pages:</strong> {{ ceil($codes->count() / 12) }}
                        |
                        <strong>Layout:</strong> 3 × 4 Cards Per Page
                    </div>

                    <form method="POST" action="{{ route('admin.temporary-id-cards.download-pdf') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="start" value="{{ $start }}">
                        <input type="hidden" name="end" value="{{ $end }}">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            Download PDF
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="preview-wrapper">
        @if(isset($codes) && $codes->count())
            @foreach($codes->chunk(12) as $pageIndex => $pageCodes)
                <div class="a4-page">
                    <div class="cards-grid">
                        @foreach($pageCodes as $item)
                            <div class="id-card">
                                <div class="logo-section">
                                    <img src="{{ asset('storage/logo/black_logo.png') }}" class="logo-img" alt="Logo">

                                    <div class="institute-name">
                                        <div class="title">MINIPAHANA</div>
                                        <div class="sub">EDUCATION CENTRE</div>
                                        <div class="name-line"></div>
                                    </div>
                                </div>

                                <div class="qr-wrapper">
                                    <img src="{{ $item['qr_base64'] }}" alt="QR Code">
                                </div>

                                <div class="tmp-code">
                                    {{ $item['code'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="page-footer no-print">
                        Page {{ $pageIndex + 1 }} of {{ ceil($codes->count() / 12) }}
                    </div>
                </div>
            @endforeach
        @else
            <div class="no-cards">
                <h4 class="fw-bold mb-2">No preview cards yet</h4>
                <p class="text-muted mb-0">Enter a Start TMP Number and End TMP Number, then click Generate.</p>
            </div>
        @endif
    </div>
@endsection