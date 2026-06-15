<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
    <div style="max-width: 500px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px;">
        <h2 style="color: #333;">Password Reset Request</h2>

        <p>Hello {{ $userName ?? 'User' }},</p>

        <p>We received a request to reset your password.</p>

        <p>Your 6-digit OTP code is:</p>

        <div style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #4f46e5; margin: 20px 0;">
            {{ $otp }}
        </div>

        <p>This OTP is valid for <strong>{{ config('app.otp_expiry_minutes', 10) }} minutes</strong>.</p>

        <p>If you did not request this, please ignore this email.</p>

        <p>Thank you,<br>{{ config('app.name') }}</p>
    </div>
</body>

</html>