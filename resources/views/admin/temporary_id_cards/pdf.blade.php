{{-- resources/views/admin/temporary_id_cards/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Temporary ID Cards PDF</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background: #ffffff;
        }

        /* PAGE */
        .page {
            width: 29.7cm;
            height: 20.95cm;

            padding-top: 0.18cm;
            padding-bottom: 0.18cm;

            display: block;
            position: relative;

            overflow: hidden;

            page-break-after: always;
            break-after: page;

            page-break-inside: avoid;
            break-inside: avoid;
        }

        .page:last-child {
            page-break-after: auto;
            break-after: auto;
        }

        /* TABLE */
        table.cards-table {
            width: 26.67cm;
            height: 20cm;

            margin: 0 auto;

            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;

            page-break-inside: avoid;
            break-inside: avoid;
        }

        tr {
            height: 5cm;
        }

        td.card-cell {
            width: 8.89cm;
            height: 5cm;
            padding: 0;
            vertical-align: top;
        }

        /* CARD */
        .id-card {
            width: 8.89cm;
            height: 5cm;

            position: relative;
            overflow: hidden;

            margin: 0;
            background: #ffffff;

            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* BACKGROUND IMAGE */
        .bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            display: block;
            z-index: 0;
            border-radius: 0 !important;
            pointer-events: none;
        }

        /* LOGO SECTION */
        .logo-section {
            position: absolute;
            top: 1.00cm;
            left: 0.38cm;
            z-index: 5;
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

        /* TEXT */
        .institute-name {
            position: absolute;
            left: 2.00cm;
            top: 0.55cm;
            width: 3.8cm;
            line-height: 1.2;
            z-index: 10;
        }

        .institute-name .title {
            font-size: 0.50cm;
            font-weight: 800;
            color: #08348f;
            letter-spacing: 0.01cm;
            white-space: nowrap;
        }

        .institute-name .sub {
            font-size: 0.28cm;
            font-weight: 800;
            color: #38a51e;
            letter-spacing: 0.01cm;
            margin-top: 0.05cm;
            white-space: nowrap;
        }

        /* WHITE NAME LINE */
        .name-line {
            width: 2.55cm;
            height: 3px;
            margin-top: 6px;
            border-radius: 999px;
            background: #6ee42a;
            display: block;
            position: relative;
            z-index: 999;
        }

        /* QR */
        .qr-wrapper {
            position: absolute;
            top: 1.24cm;
            right: 0.62cm;
            width: 2.35cm;
            height: 2.35cm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            z-index: 8;
        }

        .qr-wrapper img {
            width: 89.8%;
            height: 89.8%;
            object-fit: contain;
            display: block;
        }

        /* TMP CODE */
        .tmp-code {
            position: absolute;
            right: 0.32cm;
            bottom: 0.67cm;
            width: 2.35cm;
            height: 0.42cm;
            border-radius: 0.12cm;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.30cm;
            font-weight: 800;
            letter-spacing: 0.02cm;
            z-index: 10;
            text-shadow: 0 1px 2px rgba(0, 0, 0, .35);
            background: linear-gradient(90deg,
                    #0039c7 0%,
                    #0fa7b8 55%,
                    #79d11a 100%);
        }

        /* EMPTY CELL */
        .empty {
            width: 8.89cm;
            height: 5cm;
        }

        @media print {
            .bg {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    @php
        $bgBase64 = '';
        $bgPath = public_path('storage/id/temporary.png');

        if (file_exists($bgPath)) {
            $bgBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($bgPath));
        }

        $logoSrc = '';
        $logoPath = public_path('storage/logo/black_logo.png');

        if (file_exists($logoPath)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
    @endphp

    @foreach($codes->chunk(12) as $pageCodes)
        @php
            $pageCodes = $pageCodes->values();
        @endphp

        <div class="page">
            <table class="cards-table">
                @for($row = 0; $row < 4; $row++)
                    <tr>
                        @for($col = 0; $col < 3; $col++)
                            @php
                                $index = ($row * 3) + $col;
                                $item = $pageCodes[$index] ?? null;
                            @endphp

                            <td class="card-cell">
                                @if($item)
                                    <div class="id-card">
                                        @if($bgBase64)
                                            <img class="bg" src="{{ $bgBase64 }}" alt="Background">
                                        @endif

                                        <div class="logo-section">
                                            @if($logoSrc)
                                                <img src="{{ $logoSrc }}" class="logo-img" alt="Logo">
                                            @endif

                                            <div class="institute-name">
                                                <div class="title">MINIPALASA</div>
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
                                @else
                                    <div class="empty"></div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </table>
        </div>
    @endforeach

</body>

</html>