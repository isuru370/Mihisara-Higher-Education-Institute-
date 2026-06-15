<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card Print</title>

    <style>
        @font-face {
            font-family: 'Monbaiti';
            src: url('{{ asset('fonts/monbaiti.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #dde6f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* CR80 Card: 85.725mm × 53.975mm */
        .student-id-card {
            width: 85.725mm;
            height: 53.975mm;
            border-radius: 3mm;
            position: relative;
            overflow: hidden;
            background: #f5ede8;
            box-shadow: 0 3mm 8mm rgba(2, 6, 23, 0.18);
            font-family: 'Monbaiti', Arial, Helvetica, sans-serif;
        }

        /* Full-bleed background image */
        .card-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        /* Content wrapper */
        .card-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: row;
            width: 100%;
            height: 100%;
            padding: 3mm;
        }

        /* Left section (70%) */
        .card-left {
            width: 70%;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        /* Right section (30%) */
        .card-right {
            width: 30%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Profile photo */
        .id-card-profile-box {
            width: 18mm;
            height: 22mm;
            border: 0.3mm solid rgba(255, 255, 255, 0.3);
            border-radius: 2mm;
            overflow: hidden;
            background: #c8d4e0;
            box-shadow: 0 0.5mm 2mm rgba(0, 0, 0, 0.15);
        }

        .id-card-profile-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Student details section */
        .student-details {
            margin-left: 1mm;
            margin-top: 3mm;
            text-align: left;
        }

        .id-card-student-id {
            font-family: 'Monbaiti', Arial, Helvetica, sans-serif;
            font-size: 4.5mm;
            font-weight: 900;
            line-height: 1.1;
            color: #03050a;
            letter-spacing: 0.3mm;
        }

        .id-card-student-name {
            font-family: 'Monbaiti', Arial, Helvetica, sans-serif;
            font-size: 4mm;
            font-weight: 600;
            line-height: 1.2;
            color: #03050a;
            margin-top: 1mm;
        }

        .id-card-address {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 2.8mm;
            line-height: 1.2;
            color: #03050a;
            margin-top: 1mm;
            max-width: 45mm;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            word-wrap: break-word;
        }

        /* QR Code */
        .id-card-qr-img {
            width: 18mm;
            height: 18mm;
            background: #fff;
            padding: 1mm;
            border-radius: 1.5mm;
            box-shadow: 0 0.5mm 2mm rgba(0, 0, 0, 0.14);
            margin-top: 1mm;
        }

        /* Logo */
        .id-card-logo {
            width: 22mm;
            margin-top: auto;
            margin-bottom: 1mm;
        }

        /* Institution header (optional overlay) */
        .card-header {
            position: absolute;
            top: 2.5mm;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 3;
            pointer-events: none;
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

        /* Print styles - exact physical CR80 */
        @media print {

            html,
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            .student-id-card {
                box-shadow: none;
                margin: 0 auto;
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    @php
        $student = $studentIdCard->student;

        $address = collect([
            $student?->address1,
            $student?->address2,
            $student?->address3,
        ])->filter()->implode(', ');

        $studentKey = $student?->custom_id ?? $studentIdCard->card_no ?? $studentIdCard->id;

        $defaultImage = asset('storage/logo/black_logo3.png');
        $studentImage = $student?->img_url ? $student->img_url : $defaultImage;

        if ($studentImage && !str_starts_with($studentImage, 'http')) {
            if (str_starts_with($studentImage, 'storage/')) {
                $studentImage = asset($studentImage);
            } elseif (str_starts_with($studentImage, 'uploads/')) {
                $studentImage = asset($studentImage);
            } else {
                $studentImage = asset('storage/' . ltrim($studentImage, '/'));
            }
        }

        $qrData = $student?->custom_id ?? $studentIdCard->card_no ?? 'N/A';
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=' . urlencode($qrData);
    @endphp

    <div class="student-id-card">
        {{-- Background image (same as index blade) --}}
        <img src="{{ asset('storage/id/idcard_bg.png') }}" class="card-bg" alt="Background">

        {{-- Optional header overlay (matches index blade style) --}}
        <div class="card-header">
            <div class="card-inst-name">MINIPALASA EDUCATION CENTRE</div>
            <div class="card-inst-sub">Student ID Card</div>
        </div>

        {{-- Main content --}}
        <div class="card-content">
            <div class="card-left">
                <div class="id-card-profile-box">
                    <img src="{{ $studentImage }}" alt="Student Photo"
                        onerror="this.onerror=null;this.src='{{ $defaultImage }}'">
                </div>

                <div class="student-details">
                    <div class="id-card-student-id">
                        {{ $studentKey }}
                    </div>

                    <div class="id-card-student-name">
                        {{ $student?->initial_name ?? 'Student Name' }}
                    </div>

                    <div class="id-card-address">
                        {{ $address ?: 'Address not available' }}
                    </div>
                </div>
            </div>

            <div class="card-right">
                <img src="{{ $qrUrl }}" class="id-card-qr-img" alt="QR Code"
                    onerror="this.onerror=null;this.src='{{ asset('storage/logo/black_logo3.png') }}'">

                <img src="{{ asset('storage/logo/black_logo3.png') }}" class="id-card-logo" alt="Logo">
            </div>
        </div>
    </div>
</body>

</html>