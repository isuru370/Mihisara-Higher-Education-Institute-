@php
    $student = $card->student;
@endphp

<div class="modal fade" id="tmpPreviewModal{{ $card->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered tmp-preview-dialog">
        <div class="modal-content border-0 rounded-4 overflow-hidden tmp-preview-content">
            <div class="modal-header tmp-preview-header">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Temporary ID Preview</h5>
                    <small class="text-muted">
                        {{ $card->temporary_id_number }} / {{ $card->card_number }}
                    </small>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body tmp-preview-body">
                <div class="tmp-card-shell">
                    <div class="tmp-card-preview">
                        <div class="id-card">
                            <div class="logo-section">
                                <img src="{{ asset('storage/logo/black_logo.png') }}" class="logo-img" alt="Logo">

                                <div class="institute-name">
                                    <div class="title">MINIPALASA</div>

                                    <div class="sub">EDUCATION CENTRE</div>

                                    <div class="name-line"></div>
                                </div>
                            </div>

                            <div class="qr-wrapper">
                                <img src="{{ $card->qr_base64 }}" alt="QR Code">
                            </div>

                            <div class="tmp-code">
                                {{ $card->temporary_id_number }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tmp-meta mt-3">
                    <div class="tmp-meta-item">
                        <div class="label">Student Name</div>
                        <div class="value">{{ $student->initial_name ?? 'Not assigned yet' }}</div>
                    </div>

                    <div class="tmp-meta-item">
                        <div class="label">Grade</div>
                        <div class="value">{{ $student->grade->grade_name ?? '-' }}</div>
                    </div>

                    <div class="tmp-meta-item">
                        <div class="label">Status</div>
                        <div class="value">
                            <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                {{ ucfirst($card->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .tmp-preview-dialog {
                max-width: 760px;
            }

            .tmp-preview-content {
                border-radius: 24px;
                box-shadow: 0 25px 70px rgba(15, 23, 42, .18);
            }

            .tmp-preview-header {
                background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
                padding: 1rem 1.25rem;
                border-bottom: 1px solid #e9eef5;
            }

            .tmp-preview-body {
                background:
                    radial-gradient(circle at top left, rgba(121, 209, 26, .08), transparent 30%),
                    radial-gradient(circle at top right, rgba(11, 61, 145, .07), transparent 28%),
                    #f8fafc;
                padding: 1.25rem;
            }

            .tmp-card-shell {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: .75rem 0 1rem;
            }

            .tmp-card-preview {
                width: 9cm;
                height: 5.05cm;
                transform: scale(1);
                transform-origin: center center;
                filter: drop-shadow(0 18px 28px rgba(15, 23, 42, .14));
            }

            .tmp-card-preview .id-card {
                width: 100%;
                height: 100%;
                position: relative;
                overflow: hidden;
                border-radius: 18px;
                background: url('{{ asset('storage/id/temporary.png') }}') center/cover no-repeat;
            }

            .tmp-card-preview .logo-section {
                position: absolute;
                top: 1.06cm;
                left: .36cm;
                z-index: 2;
            }

            .tmp-card-preview .logo-img {
                position: absolute;
                left: 0.05cm;
                top: 0.05cm;
                width: 2cm;
                height: 2cm;
                object-fit: contain;
                display: block;
            }

            .tmp-card-preview .institute-name {
                position: absolute;
                left: 2.06cm;
                top: .56cm;
                line-height: 1.08;
                width: 4.1cm;
            }

            .tmp-card-preview .institute-name .title {
                font-size: 0.52cm;
                font-weight: 800;
                color: #08348f;
                letter-spacing: .01cm;
                white-space: nowrap;
            }

            .tmp-card-preview .institute-name .sub {
                font-size: .29cm;
                font-weight: 800;
                color: #38a51e;
                letter-spacing: .03cm;
                margin-top: .03cm;
                white-space: nowrap;
            }

            .tmp-card-preview .institute-name .name-line {
                width: 2.55cm;
                height: .06cm;

                border-radius: 999px;

                background: linear-gradient(90deg,
                        #79d11a 0%,
                        #38a51e 50%,
                        #0fa7b8 100%);

                margin-top: .10cm;

                box-shadow: 0 1px 4px rgba(56, 165, 30, .25);
            }

            .tmp-card-preview .qr-wrapper {
                position: absolute;
                top: 1.32cm;
                right: .70cm;
                width: 2.15cm;
                height: 2.15cm;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                z-index: 2;
                background: transparent;
                border: none;
            }

            .tmp-card-preview .qr-wrapper img {
                width: 100%;
                height: 100%;
                object-fit: contain;
                display: block;
            }

            .tmp-card-preview .tmp-code {
                position: absolute;
                right: .34cm;
                bottom: .58cm;
                width: 3.08cm;
                height: .72cm;
                border-radius: .20cm;
                color: #ffffff !important;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: .41cm;
                font-weight: 800;
                letter-spacing: .02cm;
                z-index: 2;
            }

            .tmp-meta {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: .75rem;
            }

            .tmp-meta-item {
                border: 1px solid #e8eef5;
                border-radius: 16px;
                padding: .8rem .9rem;
                text-align: center;
                box-shadow: 0 6px 18px rgba(15, 23, 42, .05);
            }

            .tmp-meta-item .label {
                font-size: .78rem;
                color: #64748b;
                font-weight: 700;
                margin-bottom: .25rem;
            }

            .tmp-meta-item .value {
                font-size: .95rem;
                font-weight: 800;
                color: #0f172a;
            }

            @media (max-width: 768px) {
                .tmp-preview-dialog {
                    max-width: 96vw;
                    margin: 0.5rem auto;
                }

                .tmp-card-preview {
                    width: 8.6cm;
                    height: 4.83cm;
                }

                .tmp-meta {
                    grid-template-columns: 1fr;
                }

                .tmp-preview-body {
                    padding: .9rem;
                }
            }
        </style>
    @endpush
@endonce