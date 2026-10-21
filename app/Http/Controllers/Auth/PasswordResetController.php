<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class PasswordResetController extends Controller
{
    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return back()->withErrors(['email' => 'Email tidak terdaftar dalam sistem kami.']);
            }

            // Generate 6-digit OTP code
            $otp = rand(100000, 999999);
            
            // Save to database with 15 minutes expiration
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => now()->addMinutes(15),
            ]);

            // Simulate email dispatch by logging to local log
            Log::info("RESET PASSWORD OTP: Send OTP code [ {$otp} ] to email [ {$request->email} ]");

            return redirect()->route('password.reset', ['email' => $request->email])
                ->with('success', 'Kode OTP telah dikirimkan ke email Anda (Silakan cek berkas log Laravel Anda).');
        } catch (Exception $e) {
            Log::error('Send OTP error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses permintaan OTP Anda. Silakan coba kembali.');
        }
    }

    public function showResetPassword(Request $request)
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|numeric',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::where('email', $request->email)
                ->where('otp_code', $request->otp_code)
                ->first();

            if (!$user) {
                return back()->withErrors(['otp_code' => 'Kode OTP yang dimasukkan tidak valid atau salah.']);
            }

            // Check if OTP has expired
            if ($user->otp_expires_at && now()->gt($user->otp_expires_at)) {
                return back()->withErrors(['otp_code' => 'Kode OTP Anda telah kedaluwarsa. Silakan ajukan ulang.']);
            }

            // Reset password
            $user->update([
                'password' => Hash::make($request->password),
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);

            return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk kembali.');
        } catch (Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengatur ulang kata sandi Anda. Silakan coba lagi.');
        }
    }
}
