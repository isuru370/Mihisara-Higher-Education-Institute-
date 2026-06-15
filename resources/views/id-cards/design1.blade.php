<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ID Card - {{ $student['custom_id'] ?? 'Student' }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        /* ================= FONT ================= */
        @font-face {
            font-family: 'Monbaiti';
            src: url('{{ asset('fonts/MONBAITI.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Monbaiti', serif;
            background: #eee;
            padding: 20px;
        }

        /* ================= ID CARD ================= */
        .id-card {
            width: 86mm;
            height: 54mm;
            background: url('{{ asset('uploads/id/idcard_bg.png') }}') no-repeat center;
            background-size: cover;
            border-radius: 3mm;
            padding: 3mm;
            box-shadow: 0 2mm 5mm rgba(0, 0, 0, .25);
        }

        /* ================= PROFILE IMAGE ================= */
        .profile-box {
            width: 18mm;
            height: 22mm;
            border: 0.3mm solid #ccc;
            border-radius: 1mm;
            overflow: hidden;
            background: #fff;
        }

        .profile-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            image-rendering: auto;
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        /* ================= TEXT ================= */
        .student-id {
            font-size: 4.5mm;
            font-weight: bold;
            line-height: 1.1;
        }

        .student-name {
            font-size: 4.3mm;
            line-height: 1.2;
        }

        .address {
            font-size: 3mm;
            line-height: 1.2;
        }

        /* ================= QR ================= */
        .qr-img {
            width: 18mm;
            height: 18mm;
            background: #fff;
            padding: 1mm;
            border-radius: 1mm;
            image-rendering: pixelated;
        }

        .logo {
            width: 14mm;
        }

        /* Buttons */
        .download-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .download-btn:hover {
            background: #0056b3;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, .8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="container">
        <div class="id-card" id="idCard">
            <div class="row h-100">

                <!-- LEFT -->
                <div class="col-8 d-flex flex-column">
                    <div class="profile-box mt-1 ms-1">
                        @php
                            $defaultImage = asset('uploads/logo/logo.png');
                            $studentImage = isset($student['img_url'])
                                ? (str_starts_with($student['img_url'], 'http')
                                    ? $student['img_url']
                                    : asset($student['img_url']))
                                : $defaultImage;
                        @endphp

                        <img src="{{ $studentImage }}?v={{ time() }}" alt="Student Photo" loading="eager"
                            decoding="sync" crossorigin="anonymous" onerror="this.src='{{ $defaultImage }}'">
                    </div>

                    <div class="ms-1 mt-3">
                        <div class="student-id">{{ $student['custom_id'] ?? 'N/A' }}</div>
                        <div class="student-name mt-1">
                            {{ ($student['fname'] ?? '') . ' ' . ($student['lname'] ?? '') }}
                        </div>
                        <div class="address mt-1">
                            {{ $student['address'] ?? 'Address not available' }}
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-4 d-flex flex-column align-items-center">
                    @php
                        $qrData = $student['custom_id'] ?? 'N/A';
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=' . urlencode($qrData);
                    @endphp

                    <img src="{{ $qrUrl }}" class="qr-img mt-1" alt="QR Code" crossorigin="anonymous">

                    <img src="{{ asset('uploads/logo/logo.png') }}" class="logo mt-auto mb-1" alt="Logo"
                        crossorigin="anonymous">
                </div>

            </div>
        </div>

        <!-- ACTIONS -->
        <div class="text-center mt-4">
            <button class="download-btn" onclick="downloadIDCard()">
                Download ID Card
            </button>

            <a href="{{ route('student-id-card.ganarateStudentId') }}" class="btn btn-secondary ms-2">
                Back
            </a>
        </div>
    </div>

    <script>
        function downloadIDCard() {
            showLoading();

            html2canvas(document.getElementById('idCard'), {
                scale: 4,                 // 🔥 HIGH DPI
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                onclone: function (doc) {
                    const style = doc.createElement('style');
                    style.innerHTML = `
                * {
                    image-rendering: crisp-edges !important;
                    image-rendering: -webkit-optimize-contrast !important;
                    font-family: 'Monbaiti', serif !important;
                }
            `;
                    doc.head.appendChild(style);
                }
            }).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png'); // 🔥 PNG = SHARP
                link.download = 'ID_{{ $student['custom_id'] ?? 'student' }}.png';
                link.click();
                hideLoading();
            }).catch(() => {
                alert('Download failed. Image source may block CORS.');
                hideLoading();
            });
        }

        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
    </script>

</body>

</html>