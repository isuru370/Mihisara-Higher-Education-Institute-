<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ID Card - {{ $student->custom_id }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @font-face {
            font-family: 'Monbaiti';
            src: url('{{ asset('fonts/MONBAITI.ttf') }}') format('truetype');
        }

        body {
            font-family: 'Monbaiti', serif;
            background: #eee;
            padding: 20px;
            margin: 0;
        }

        .id-card {
            width: 86mm;
            height: 54mm;
            background: url('{{ $bgImage }}') no-repeat center;
            background-size: cover;
            border-radius: 3mm;
            padding: 3mm;
            box-shadow: 0 2mm 5mm rgba(0, 0, 0, .25);
            margin: 0 auto;
        }

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
        }

        .student-id {
            font-size: 4.5mm;
            font-weight: bold;
            line-height: 1.1;
        }

        .student-name {
            font-size: 3.6mm;
            line-height: 1.2;
        }

        .address {
            font-size: 3mm;
            line-height: 1.2;
        }

        .qr-img {
            width: 18mm;
            height: 18mm;
            background: #fff;
            padding: 1mm;
            border-radius: 1mm;
            border: 1px solid #ddd;
        }

        .logo {
            width: 14mm;
        }

        .action-buttons {
            max-width: 86mm;
            margin: 20px auto;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Action Buttons -->
    <div class="action-buttons">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="fas fa-id-card text-primary me-2"></i>
                    Student ID Card
                </h5>
                <p class="text-muted mb-0 small">{{ $student->custom_id }} - {{ $student->fname }} {{ $student->lname }}
                </p>
            </div>
            <div class="col-md-6 text-end">
                <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
                    <i class="fas fa-print me-1"></i> Print ID
                </button>
                <a href="{{ route('students.ganarateStudentId.bulk') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to All Students
                </a>
            </div>
        </div>
    </div>

    <!-- ID Card Container -->
    <div class="id-card">
        <div class="row h-100">
            <!-- LEFT SECTION -->
            <div class="col-8 d-flex flex-column">
                <div class="profile-box mt-1 ms-1">
                    @if($student->img_url)
                        <img src="{{ $student->img_url }}" alt="{{ $student->fname }} {{ $student->lname }}"
                            onerror="this.src='{{ asset('storage/uploads/logo/logo.png') }}'">
                    @else
                        <img src="{{ asset('storage/uploads/logo/logo.png') }}" alt="Default Profile">
                    @endif
                </div>

                <div class="ms-1 mt-3">
                    <div class="student-id">{{ $student->custom_id }}</div>
                    <div class="student-name mt-1">{{ $student->fname }} {{ $student->lname }}</div>
                    <div class="address mt-1">
                        {{ $student->address ?: 'Address not available' }}
                    </div>

                    @if($student->grade)
                        <div class="mt-2" style="font-size: 2.8mm;">
                            <strong>Grade:</strong> {{ $student->grade->grade_name }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- RIGHT SECTION -->
            <div class="col-4 d-flex flex-column align-items-center">
                <img src="{{ $qrCodeUrl }}" class="qr-img mt-1" alt="QR Code for {{ $student->custom_id }}">

                @if($logo)
                    <img src="{{ $logo }}" class="logo mt-auto mb-1" alt="School Logo" onerror="this.style.display='none'">
                @endif
            </div>
        </div>
    </div>

    <script>
        // Auto-print option (optional)
        window.onload = function () {
            // Uncomment below line to auto-print when page loads
            // window.print();
        };
    </script>
</body>

</html>