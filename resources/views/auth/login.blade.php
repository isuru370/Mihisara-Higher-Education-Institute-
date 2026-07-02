<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Nexora Education</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

            font-family: 'Segoe UI', sans-serif;

            background:
                radial-gradient(circle at top left,
                    rgba(37, 99, 235, .25),
                    transparent 35%),

                radial-gradient(circle at bottom right,
                    rgba(59, 130, 246, .18),
                    transparent 35%),

                linear-gradient(135deg,
                    #020617,
                    #0f172a,
                    #1e3a8a);

            display: flex;
            justify-content: center;
            align-items: center;

            overflow: hidden;

            position: relative;
        }

        /* FLOATING LIGHTS */

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
            width: 240px;
            height: 240px;
            top: -60px;
            left: -60px;
        }

        .floating-circle:nth-child(2) {
            width: 180px;
            height: 180px;
            bottom: -50px;
            right: -40px;
            animation-delay: 2s;
        }

        .floating-circle:nth-child(3) {
            width: 120px;
            height: 120px;
            top: 50%;
            left: 12%;
            animation-delay: 1s;
        }

        .floating-circle:nth-child(4) {
            width: 90px;
            height: 90px;
            top: 18%;
            right: 20%;
            animation-delay: 3s;
        }

        @keyframes float {

            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-25px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        /* LOGIN WRAPPER */

        .login-wrapper {
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 5;
            padding: 20px;
        }

        /* LOGIN CARD */

        .login-card {

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

        .login-card::before {

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

        .login-header {

            position: relative;

            padding: 45px 40px 25px;

            text-align: center;
        }

        .logo-box {

            width: 88px;
            height: 88px;

            margin: auto;

            border-radius: 28px;

            background:
                linear-gradient(135deg,
                    rgba(37, 99, 235, .95),
                    rgba(59, 130, 246, .95));

            display: flex;
            justify-content: center;
            align-items: center;

            box-shadow:
                0 10px 25px rgba(37, 99, 235, .45);

            margin-bottom: 24px;
        }

        .logo-box i {

            font-size: 38px;
            color: white;
        }

        .brand-title {

            font-size: 30px;

            font-weight: 800;

            letter-spacing: .5px;

            margin-bottom: 10px;
        }

        .brand-subtitle {

            color: rgba(255, 255, 255, .72);

            font-size: 15px;

            line-height: 1.6;
        }

        /* BODY */

        .login-body {
            padding: 20px 40px 40px;
        }

        /* LABELS */

        .login-label {

            font-weight: 700;

            margin-bottom: 12px;

            font-size: 14px;

            color: #e2e8f0;
        }

        /* INPUT GROUP */

        .custom-input-group {

            background: rgba(255, 255, 255, .07);

            border: 1px solid rgba(255, 255, 255, .10);

            border-radius: 18px;

            overflow: hidden;

            transition: .25s ease;
        }

        .custom-input-group:focus-within {

            border-color: rgba(59, 130, 246, .7);

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, .12);
        }

        .custom-input-group .input-group-text {

            background: transparent;

            border: none;

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
            color: rgba(255, 255, 255, .45);
        }

        .custom-input:focus {
            box-shadow: none !important;
        }

        /* PASSWORD BUTTON */

        .toggle-password {

            border: none;

            background: transparent;

            color: #cbd5e1;

            padding: 0 18px;
        }

        /* REMEMBER */

        .form-check-input {

            background-color: transparent;

            border: 1px solid rgba(255, 255, 255, .35);
        }

        .form-check-input:checked {

            background-color: #2563eb;

            border-color: #2563eb;
        }

        .form-check-label {
            color: #cbd5e1;
        }

        /* LOGIN BUTTON */

        .btn-login {

            width: 100%;

            height: 58px;

            border: none;

            border-radius: 18px;

            background:
                linear-gradient(135deg,
                    #2563eb,
                    #3b82f6);

            color: white;

            font-size: 16px;

            font-weight: 700;

            transition: .25s ease;

            box-shadow:
                0 12px 30px rgba(37, 99, 235, .35);
        }

        .btn-login:hover {

            transform: translateY(-2px);

            box-shadow:
                0 16px 35px rgba(37, 99, 235, .45);
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

        /* ALERT */

        .alert {

            border: none;

            border-radius: 18px;
        }

        /* FOOTER */

        .footer-text {

            margin-top: 28px;

            text-align: center;

            color: rgba(255, 255, 255, .55);

            font-size: 14px;
        }

        /* RESPONSIVE */

        @media(max-width:576px) {

            .login-card {
                border-radius: 28px;
            }

            .login-header {
                padding: 35px 25px 20px;
            }

            .login-body {
                padding: 20px 25px 30px;
            }

            .brand-title {
                font-size: 24px;
            }
        }
    </style>

</head>

<body>

    <!-- FLOATING -->
    <div class="floating-bg">

        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>

    </div>

    <!-- LOGIN WRAPPER -->
    <div class="login-wrapper">

        <div class="login-card">

            <!-- HEADER -->
            <div class="login-header">

                <div class="logo-box">
                    <i class="fas fa-graduation-cap"></i>
                </div>

                <h1 class="brand-title">
                    NEXORAIT EDU
                </h1>

                <p class="brand-subtitle">
                    Modern Student Management System
                    for smart educational institutes.
                </p>

            </div>

            <!-- BODY -->
            <div class="login-body">

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

                <!-- FORM -->
                <form method="POST" action="{{ route('login') }}">

                    @csrf

                    <!-- EMAIL -->
                    <div class="mb-4">

                        <label class="login-label">
                            Email Address
                        </label>

                        <div class="input-group custom-input-group">

                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>

                            <input type="email" name="email" class="form-control custom-input"
                                placeholder="Enter your email" required>

                        </div>

                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-4">

                        <label class="login-label">
                            Password
                        </label>

                        <div class="input-group custom-input-group">

                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>

                            <input type="password" name="password" id="password" class="form-control custom-input"
                                placeholder="Enter your password" required>

                            <button type="button" class="toggle-password" onclick="togglePassword()">

                                <i class="fas fa-eye" id="eyeIcon"></i>

                            </button>

                        </div>

                    </div>

                    <!-- REMEMBER -->
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div class="form-check">

                            <input type="checkbox" class="form-check-input" id="remember">

                            <label class="form-check-label" for="remember">

                                Remember Me

                            </label>

                        </div>

                        <a href="forgot-password" class="custom-link">

                            Forgot Password?

                        </a>

                    </div>

                    <!-- LOGIN BUTTON -->
                    <button type="submit" class="btn-login">

                        <i class="fas fa-sign-in-alt me-2"></i>

                        Login to Dashboard

                    </button>

                </form>

                <!-- FOOTER -->
                <div class="footer-text">

                    Need help?

                    <a href="{{ route('contact_administrator') }}" class="custom-link">

                        Contact Administrator

                    </a>

                </div>

            </div>

        </div>

    </div>

    <!-- PASSWORD TOGGLE -->
    <script>

        function togglePassword() {

            const password =
                document.getElementById('password');

            const eyeIcon =
                document.getElementById('eyeIcon');

            if (password.type === 'password') {

                password.type = 'text';

                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');

            } else {

                password.type = 'password';

                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

    </script>

</body>

</html>