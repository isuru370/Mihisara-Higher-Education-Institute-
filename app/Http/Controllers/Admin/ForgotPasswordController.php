<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class ForgotPasswordController extends Controller
{
    public function index()
    {
        return view('forgot-password.index');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = strtolower(trim($request->email));
        $rateKey = 'password-reset-send:' . $email;

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            return back()
                ->withErrors([
                    'email' => 'Too many OTP requests. Please try again later.',
                ])
                ->withInput();
        }

        RateLimiter::hit($rateKey, 600);

        try {
            $user = User::where('email', $email)->firstOrFail();

            PasswordResetOtp::where('email', $email)
                ->whereNull('verified_at')
                ->delete();

            $otp = (string) random_int(100000, 999999);

            PasswordResetOtp::create([
                'email'      => $email,
                'otp'        => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($user->email)->send(new PasswordResetMail($otp, $user->name));

            return redirect()
                ->route('forgot_password.form')
                ->with([
                    'success' => 'OTP has been sent to your email address.',
                    'reset_email' => $email,
                ]);
        } catch (Throwable $e) {
            Log::error('Password reset OTP send failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors([
                    'email' => 'Unable to send OTP right now.',
                ])
                ->withInput();
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($request->email));

        try {
            $record = PasswordResetOtp::where('email', $email)
                ->latest()
                ->first();

            if (!$record) {
                return back()
                    ->withErrors(['otp' => 'OTP not found.'])
                    ->withInput();
            }

            if ($record->isExpired()) {
                return back()
                    ->withErrors(['otp' => 'OTP has expired.'])
                    ->withInput();
            }

            if (!Hash::check($request->otp, $record->otp)) {
                return back()
                    ->withErrors(['otp' => 'Invalid OTP.'])
                    ->withInput();
            }

            $record->update([
                'verified_at' => now(),
            ]);

            return redirect()
                ->route('forgot_password.form')
                ->with([
                    'success' => 'OTP verified successfully.',
                    'reset_email' => $email,
                    'otp_verified_email' => $email,
                ]);
        } catch (Throwable $e) {
            Log::error('OTP verification failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['otp' => 'Unable to verify OTP right now.'])
                ->withInput();
        }
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = strtolower(trim($request->email));

        try {
            $user = User::where('email', $email)->firstOrFail();

            PasswordResetOtp::where('email', $email)
                ->whereNull('verified_at')
                ->delete();

            $otp = (string) random_int(100000, 999999);

            PasswordResetOtp::create([
                'email'      => $email,
                'otp'        => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
            ]);

            Mail::to($user->email)->send(new PasswordResetMail($otp, $user->name));

            return redirect()
                ->route('forgot_password.form')
                ->with([
                    'success' => 'OTP has been resent to your email address.',
                    'reset_email' => $email,
                ]);
        } catch (Throwable $e) {
            Log::error('Password reset OTP resend failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors([
                    'email' => 'Unable to resend OTP right now.',
                ])
                ->withInput();
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = strtolower(trim($request->email));

        try {
            $otpRecord = PasswordResetOtp::where('email', $email)
                ->latest()
                ->first();

            if (!$otpRecord || !$otpRecord->isVerified()) {
                return back()
                    ->withErrors([
                        'email' => 'OTP verification required.',
                    ])
                    ->withInput();
            }

            if ($otpRecord->isExpired()) {
                return back()
                    ->withErrors([
                        'email' => 'OTP session expired.',
                    ])
                    ->withInput();
            }

            $user = User::where('email', $email)->firstOrFail();

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            PasswordResetOtp::where('email', $email)->delete();

            return redirect()
                ->route('login')
                ->with('success', 'Password updated successfully.');
        } catch (Throwable $e) {
            Log::error('Password update failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors([
                    'password' => 'Unable to update password.',
                ])
                ->withInput();
        }
    }
}
