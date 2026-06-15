<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Forgot Password - Minipalasa Education</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;



            font-family: 'Segoe UI', sans-serif;

            background:
                radial-gradient(circle at top left,
                    rgba(37, 99, 235, .24),
                    transparent 35%),

                radial-gradient(circle at bottom right,
                    rgba(59, 130, 246, .16),
                    transparent 35%),

                linear-gradient(135deg,
                    #020617,
                    #0f172a,
                    #1e3a8a);

            position: relative;
        }

        /* FLOATING BG */

        .floating-bg {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .floating-circle {

            position: absolute;

            border-radius: 50%;

            background: rgba(255, 255, 255, .05);

            animation: float 10s infinite ease-in-out;
        }

        .floating-circle:nth-child(1) {
            width: 220px;
            height: 220px;
            top: -50px;
            left: -50px;
        }

        .floating-circle:nth-child(2) {
            width: 160px;
            height: 160px;
            bottom: -40px;
            right: -30px;
            animation-delay: 2s;
        }

        .floating-circle:nth-child(3) {
            width: 110px;
            height: 110px;
            top: 50%;
            left: 10%;
            animation-delay: 1s;
        }

        .floating-circle:nth-child(4) {
            width: 90px;
            height: 90px;
            top: 18%;
            right: 18%;
            animation-delay: 3s;
        }

        @keyframes float {

            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-22px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        /* WRAPPER */

        .forgot-wrapper {

            width: 100%;
            max-width: 470px;

            position: relative;

            z-index: 5;

            padding: 20px;
        }

        /* CARD */

        .forgot-card {

            position: relative;

            overflow: hidden;

            border-radius: 34px;

            background: rgba(255, 255, 255, .08);

            border: 1px solid rgba(255, 255, 255, .12);

            backdrop-filter: blur(22px);

            box-shadow:
                0 20px 50px rgba(0, 0, 0, .45),
                inset 0 1px 0 rgba(255, 255, 255, .08);

            color: white;
        }

        .forgot-card::before {

            content: '';

            position: absolute;

            width: 280px;
            height: 280px;

            background: rgba(59, 130, 246, .14);

            border-radius: 50%;

            top: -120px;
            right: -120px;
        }

        /* HEADER */

        .forgot-header {

            position: relative;

            text-align: center;

            padding: 42px 35px 24px;
        }

        .icon-box {

            width: 90px;
            height: 90px;

            margin: auto;

            border-radius: 28px;

            background:
                linear-gradient(135deg,
                    #2563eb,
                    #3b82f6);

            display: flex;
            justify-content: center;
            align-items: center;

            box-shadow:
                0 12px 30px rgba(37, 99, 235, .35);

            margin-bottom: 24px;
        }

        .icon-box i {

            font-size: 38px;
            color: white;
        }

        .forgot-title {

            font-size: 30px;

            font-weight: 800;

            margin-bottom: 12px;
        }

        .forgot-subtitle {

            color: rgba(255, 255, 255, .72);

            font-size: 15px;

            line-height: 1.6;
        }

        /* BODY */

        .forgot-body {
            padding: 20px 35px 35px;
        }

        /* STEP */

        .step-box {

            display: flex;
            align-items: center;
            gap: 14px;

            margin-bottom: 24px;
        }

        .step-badge {

            width: 42px;
            height: 42px;

            border-radius: 16px;

            display: flex;
            justify-content: center;
            align-items: center;

            font-weight: 800;

            background:
                linear-gradient(135deg,
                    #2563eb,
                    #3b82f6);

            box-shadow:
                0 8px 20px rgba(37, 99, 235, .35);
        }

        .step-title {

            font-weight: 700;

            font-size: 16px;
        }

        .step-desc {

            color: rgba(255, 255, 255, .58);

            font-size: 13px;
        }

        /* LABELS */

        .custom-label {

            margin-bottom: 10px;

            font-weight: 700;

            color: #e2e8f0;
        }

        /* INPUTS */

        .input-group-box {

            background: rgba(255, 255, 255, .07);

            border: 1px solid rgba(255, 255, 255, .10);

            border-radius: 18px;

            overflow: hidden;

            transition: .25s ease;
        }

        .input-group-box:focus-within {

            border-color: rgba(59, 130, 246, .75);

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, .10);
        }

        .input-group-text {

            background: transparent !important;

            border: none !important;

            color: #cbd5e1;

            padding-left: 18px;
        }

        .custom-input {

            background: transparent !important;

            border: none !important;

            color: white !important;

            height: 56px;

            font-size: 15px;
        }

        .custom-input::placeholder {
            color: rgba(255, 255, 255, .42);
        }

        .custom-input:focus {
            box-shadow: none !important;
        }

        /* OTP */

        .otp-input {

            letter-spacing: 12px;

            text-align: center;

            font-size: 24px;

            font-weight: 800;
        }

        /* BUTTONS */

        .btn-primary-custom {

            width: 100%;

            height: 58px;

            border: none;

            border-radius: 18px;

            font-size: 16px;

            font-weight: 700;

            background:
                linear-gradient(135deg,
                    #2563eb,
                    #3b82f6);

            color: white;

            transition: .25s ease;

            box-shadow:
                0 12px 30px rgba(37, 99, 235, .35);
        }

        .btn-primary-custom:hover {

            transform: translateY(-2px);

            box-shadow:
                0 16px 35px rgba(37, 99, 235, .45);
        }

        .btn-outline-custom {

            width: 100%;

            height: 54px;

            border-radius: 18px;

            border: 1px solid rgba(255, 255, 255, .15);

            background: rgba(255, 255, 255, .04);

            color: white;

            font-weight: 600;

            transition: .2s;
        }

        .btn-outline-custom:hover {

            background: rgba(255, 255, 255, .08);
        }

        /* ALERT */

        .alert {

            border: none;

            border-radius: 18px;
        }

        /* LINKS */

        .custom-link {

            color: #bfdbfe;

            text-decoration: none;

            transition: .2s;
        }

        .custom-link:hover {
            color: white;
        }

        /* RESPONSIVE */

        @media(max-width:576px) {

            .forgot-card {
                border-radius: 28px;
            }

            .forgot-header {
                padding: 35px 24px 20px;
            }

            .forgot-body {
                padding: 20px 24px 28px;
            }

            .forgot-title {
                font-size: 25px;
            }
        }
    </style>

</head>

<body>

    @php

        $resetEmail = session('reset_email');

        $verifiedEmail = session('otp_verified_email');

        if ($verifiedEmail) {

            $step = 'reset';

        } elseif ($resetEmail) {

            $step = 'verify';

        } else {

            $step = 'send';
        }

    @endphp

    <!-- FLOATING -->
    <div class="floating-bg">

        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>

    </div>

    <!-- WRAPPER -->
    <div class="forgot-wrapper">

        <div class="forgot-card">

            <!-- HEADER -->
            <div class="forgot-header">

                <div class="icon-box">

                    <i class="fas fa-key"></i>

                </div>

                <h1 class="forgot-title">
                    Forgot Password
                </h1>

                <p class="forgot-subtitle">
                    Reset your password securely using OTP verification.
                </p>

            </div>

            <!-- BODY -->
            <div class="forgot-body">

                <!-- SUCCESS -->
                @if(session('success'))

                    <div class="alert alert-success mb-4">

                        {{ session('success') }}

                    </div>

                @endif

                <!-- ERRORS -->
                @if($errors->any())

                    <div class="alert alert-danger mb-4">

                        <ul class="mb-0 ps-3">

                            @foreach($errors->all() as $error)

                                <li>{{ $error }}</li>

                            @endforeach

                        </ul>

                    </div>

                @endif

                <!-- STEP 1 -->
                @if($step === 'send')

                    <div class="step-box">

                        <div class="step-badge">
                            1
                        </div>

                        <div>

                            <div class="step-title">
                                Enter Email
                            </div>

                            <div class="step-desc">
                                Send OTP to your email address
                            </div>

                        </div>

                    </div>

                    <form method="POST" action="{{ route('forgot_password.send_otp') }}">

                        @csrf

                        <div class="mb-4">

                            <label class="custom-label">
                                Email Address
                            </label>

                            <div class="input-group input-group-box">

                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>

                                <input type="email" name="email" class="form-control custom-input"
                                    placeholder="Enter your email" value="{{ old('email') }}" required>

                            </div>

                        </div>

                        <button type="submit" class="btn-primary-custom">

                            <i class="fas fa-paper-plane me-2"></i>

                            Send OTP

                        </button>

                    </form>

                @endif

                <!-- STEP 2 -->
                @if($step === 'verify' || $step === 'reset')

                    <div class="step-box">

                        <div class="step-badge">
                            2
                        </div>

                        <div>

                            <div class="step-title">
                                Verify OTP
                            </div>

                            <div class="step-desc">
                                Enter the code sent to your email
                            </div>

                        </div>

                    </div>

                    <form method="POST" action="{{ route('forgot_password.verify_otp') }}">

                        @csrf

                        <input type="hidden" name="email" value="{{ $resetEmail }}">

                        <div class="mb-4">

                            <label class="custom-label">
                                Email Address
                            </label>

                            <div class="input-group input-group-box">

                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>

                                <input type="email" class="form-control custom-input" value="{{ $resetEmail }}" readonly>

                            </div>

                        </div>

                        <div class="mb-4">

                            <label class="custom-label">
                                OTP Code
                            </label>

                            <div class="input-group input-group-box">

                                <span class="input-group-text">
                                    <i class="fas fa-shield-alt"></i>
                                </span>

                                <input type="text" name="otp" maxlength="6" class="form-control custom-input otp-input"
                                    placeholder="000000" required {{ $step === 'reset' ? 'disabled' : '' }}>

                            </div>

                        </div>

                        <button type="submit" class="btn-primary-custom mb-3" {{ $step === 'reset' ? 'disabled' : '' }}>

                            <i class="fas fa-check-circle me-2"></i>

                            Verify OTP

                        </button>

                    </form>

                    <form method="POST" action="{{ route('forgot_password.resend_otp') }}">

                        @csrf

                        <input type="hidden" name="email" value="{{ $resetEmail }}">

                        <button type="submit" class="btn-outline-custom">

                            <i class="fas fa-rotate me-2"></i>

                            Resend OTP

                        </button>

                    </form>

                @endif

                <!-- STEP 3 -->
                @if($step === 'reset')

                    <div class="step-box mt-4">

                        <div class="step-badge">
                            3
                        </div>

                        <div>

                            <div class="step-title">
                                Create New Password
                            </div>

                            <div class="step-desc">
                                Choose a strong new password
                            </div>

                        </div>

                    </div>

                    <form method="POST" action="{{ route('forgot_password.reset') }}">

                        @csrf

                        <input type="hidden" name="email" value="{{ $verifiedEmail }}">

                        <div class="mb-4">

                            <label class="custom-label">
                                New Password
                            </label>

                            <div class="input-group input-group-box">

                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>

                                <input type="password" name="password" class="form-control custom-input"
                                    placeholder="Enter new password" required>

                            </div>

                        </div>

                        <div class="mb-4">

                            <label class="custom-label">
                                Confirm Password
                            </label>

                            <div class="input-group input-group-box">

                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>

                                <input type="password" name="password_confirmation" class="form-control custom-input"
                                    placeholder="Confirm password" required>

                            </div>

                        </div>

                        <button type="submit" class="btn-primary-custom">

                            <i class="fas fa-key me-2"></i>

                            Reset Password

                        </button>

                    </form>

                @endif

                <!-- START AGAIN -->
                @if($step !== 'send')

                    <div class="text-center mt-4">

                        <a href="{{ route('forgot_password.form') }}" class="custom-link">

                            <i class="fas fa-arrow-left me-1"></i>

                            Start Again

                        </a>

                    </div>

                @endif

            </div>

        </div>

    </div>

</body>

</html>