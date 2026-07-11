<div class="id-card-preview-wrap">
    <div class="id-scale-box">

        @php
            $initialName = strtoupper($studentData['initial_name'] ?? $studentData['name']);

            // Format: N.M.D.P.S.SURANGIKA -> N.M.D.P.S.<br>SURANGIKA
            if (preg_match('/^(.*\.)([^.]+)$/', $initialName, $match)) {
                $displayName = $match[1] . '<br>' . $match[2];
            } else {
                // Format: N M D P S SURANGIKA -> N M D P S<br>SURANGIKA
                $parts = preg_split('/\s+/', $initialName);

                if (count($parts) > 2) {
                    $last = array_pop($parts);
                    $displayName = implode(' ', $parts) . '<br>' . $last;
                } else {
                    $displayName = $initialName;
                }
            }
        @endphp

        <div class="student-id-card" id="id-card-{{ $studentData['custom_id'] }}">

            <!-- Background -->
            <img src="{{ asset('storage/id/idcard_bg.png') }}" class="card-bg" alt="Background">

            <!-- Student ID -->
            <div class="student-id">
                {{ $studentData['custom_id'] }}
            </div>

            <!-- Student Initial Name -->
            <div class="student-name">
                {!! $displayName !!}
            </div>

            <!-- QR -->
            <div class="card-qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data={{ urlencode($qrData) }}"
                    alt="QR">
            </div>

        </div>

    </div>
</div>

<style>
    .student-id-card {
        position: relative;
        width: 85.6mm;
        height: 54mm;
        overflow: hidden;
    }

    /* Background */

    .card-bg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* =========================
   STUDENT ID
========================= */

    .student-id {

        position: absolute;

        /* ↑ ID උඩට */
        top: 20.8mm;

        /* ← → */
        left: 33mm;

        width: 40mm;

        font-size: 5mm;
        font-weight: 700;

        color: #143d8d;

        text-align: left;

        z-index: 20;
    }


    /* =========================
   STUDENT NAME
========================= */

    .student-name {

        position: absolute;

        /* ↑ ↓ */
        top: 31.5mm;

        /* ← → */
        left: 33mm;

        width: 42mm;

        min-height: 8mm;

        font-size: 3.2mm;
        font-weight: 700;

        color: #222;

        text-transform: uppercase;

        line-height: 1.2;

        white-space: normal;

        word-break: break-word;

        overflow-wrap: break-word;

        z-index: 20;
    }


    /* =========================
   QR
========================= */

    .card-qr {

        position: absolute;

        /* ↑ ↓ */
        top: 18mm;

        /* ← → */
        right: 4mm;

        width: 24mm;
        height: 24mm;

        background: #fff;

        padding: .8mm;

        border: .5mm solid #2450d4;

        border-radius: 1.2mm;

        z-index: 20;
    }

    .card-qr img {

        width: 100%;
        height: 100%;
        display: block;
    }


    /* Mobile */

    @media(max-width:768px) {

        .student-id {
            font-size: 3mm;
        }

        .student-name {
            font-size: 3mm;
        }

        .card-qr {
            width: 22mm;
            height: 22mm;
        }

    }
</style>
