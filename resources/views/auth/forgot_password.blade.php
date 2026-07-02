<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - VISION  EDUCATION</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-box {
            width: 100%;
            max-width: 450px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .timer-text {
            font-size: 14px;
            color: #666;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="card p-4 card-box">
        <h3 class="text-center mb-3">Forgot Password</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $step = session('step', 'email');
            $email = session('email', old('email'));
        @endphp

        {{-- Step 1: Enter Email --}}
        @if($step === 'email')
            <form method="POST" action="{{ route('forgot_password.send_otp') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Enter Your Email</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                </div>
                <button type="submit" class="btn btn-primary w-100">Send OTP</button>
            </form>
        @endif

        {{-- Step 2: Enter OTP --}}
        @if($step === 'otp')
            <form method="POST" action="{{ route('forgot_password.verify_otp') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="{{ $email }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Enter 6-Digit OTP</label>
                    <input type="text" name="otp" class="form-control" maxlength="6" required>
                </div>

                <div class="mb-3 text-center">
                    <span class="timer-text">OTP expires in: <span id="countdown">03:00</span></span>
                </div>

                <button type="submit" class="btn btn-success w-100 mb-2">Verify OTP</button>
            </form>

            <form method="POST" action="{{ route('forgot_password.resend_otp') }}" id="resendForm" class="hidden">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button type="submit" class="btn btn-warning w-100">Resend OTP</button>
            </form>
        @endif

        {{-- Step 3: Reset Password --}}
        @if($step === 'reset')
            <form method="POST" action="{{ route('forgot_password.reset') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Change Password</button>
            </form>
        @endif

        <div class="text-center mt-3">
            <a href="{{ route('login') }}">Back to Login</a>
        </div>
    </div>

    @if($step === 'otp')
    <script>
        let timeLeft = 180;
        const countdown = document.getElementById('countdown');
        const resendForm = document.getElementById('resendForm');

        const timer = setInterval(() => {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;

            countdown.textContent =
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

            if (timeLeft <= 0) {
                clearInterval(timer);
                countdown.textContent = 'Expired';
                resendForm.classList.remove('hidden');
            }

            timeLeft--;
        }, 1000);
    </script>
    @endif
</body>
</html>