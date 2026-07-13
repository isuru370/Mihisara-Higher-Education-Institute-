{{-- ID Card Preview Component --}}
<div class="id-card-preview-wrap">
    <div class="id-scale-box">
        <div class="student-id-card" id="id-card-{{ $studentData['custom_id'] }}">
            <!-- Background Image -->
            <img src="{{ asset('storage/id/idcard_bg.png') }}" class="card-bg" alt="">

            <!-- Header Section -->
            <div class="card-header">
                <div class="card-inst-name">MINIPALASA EDUCATION CENTRE</div>
                <div class="card-inst-sub">Student ID Card</div>
            </div>

            <!-- Student Photo -->
            <div class="card-photo">
                <img src="{{ $studentImage }}" alt="Student Photo"
                    onerror="this.onerror=null;this.src='{{ $defaultImage }}'">
            </div>

            <!-- QR Code -->
            <div class="card-qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrData) }}"
                    alt="QR Code">
            </div>

            <!-- Student Details -->
            <div class="card-details">
                <div class="card-info-row">
                    <span class="card-info-value card-name">{{ $studentData['name'] }}</span>
                </div>
                <div class="card-info-row card-address-row">
                    <span
                        class="card-info-value card-address">{{ $studentData['address'] ?: 'No Address Provided' }}</span>
                </div>
            </div>

            <!-- ID Pill -->
            <div class="card-id-pill">{{ $studentData['custom_id'] }}</div>
        </div>
    </div>
</div>

<style>
    /* Card background */
    .card-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    /* Header styles with gradient */
    .card-header {
        position: absolute;
        top: 2.5mm;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 2;
    }

    .card-inst-name {
        font-size: 3.8mm;
        font-weight: 900;
        letter-spacing: 0.15mm;
        line-height: 1.1;
        background: linear-gradient(90deg, #0f2d7a 0%, #1d4ed8 50%, #38bdf8 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        margin-bottom: 1mm;
    }

    .card-inst-sub {
        font-size: 2.5mm;
        font-weight: 800;
        letter-spacing: 1mm;
        margin-top: 0.8mm;
        text-transform: uppercase;
        background: linear-gradient(90deg, #0f2d7a 0%, #1d4ed8 50%, #38bdf8 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    /* Photo styles */
    .card-photo {
        position: absolute;
        top: 14mm;
        left: 4mm;
        width: 19mm;
        height: 24mm;
        background: #c8d4e0;
        overflow: hidden;
        z-index: 2;
        box-shadow: 0 0.5mm 2mm rgba(0, 0, 0, 0.2);
        border-radius: 1mm;
    }

    .card-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* QR Code styles */
    .card-qr {
        position: absolute;
        top: 15mm;
        right: 4mm;
        width: 21mm;
        height: 21mm;
        border: 0.4mm solid #1d4ed8;
        border-radius: 2mm;
        padding: 1mm;
        box-shadow: 0 0.5mm 2mm rgba(0, 0, 0, 0.14);
        z-index: 2;
        background: #fff;
    }

    .card-qr img {
        width: 100%;
        height: 100%;
        display: block;
        border-radius: 1mm;
    }

    /* Details styles */
    .card-details {
        position: absolute;
        left: 4mm;
        top: 41mm;
        z-index: 2;
    }

    .card-info-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.2mm;
        line-height: 1.35;
    }

    .card-info-value {
        font-weight: 500;
        max-width: 50mm;
        white-space: normal;
        word-wrap: break-word;
        line-height: 1.2;
        color: #03050a;
    }

    /* Name styles */
    .card-name {
        font-size: 2.8mm;
        font-weight: 600;
        color: #0f2d7a;
    }

    /* Address styles - smaller font */
    .card-address {
        font-size: 2.2mm;
        line-height: 1.25;
        color: #475569;
    }

    .card-address-row {
        margin-bottom: 0;
    }

    /* HTML2Canvas capture optimization */
    .html2canvas-capture .card-inst-name,
    .html2canvas-capture .card-inst-sub {
        background: none !important;
        -webkit-background-clip: unset !important;
        background-clip: unset !important;
        color: #1d4ed8 !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-name {
            font-size: 2.5mm;
        }

        .card-address {
            font-size: 2mm;
        }

        .card-id-pill {
            font-size: 2.8mm;
            padding: 1mm 3.5mm;
        }
    }
</style>