<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class LoginController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                
                $user = Auth::user();
                if ($user->role === 'admin') {
                    return redirect()->intended('/admin')->with('success', 'Selamat datang kembali, Administrator!');
                }
                return redirect()->intended('/')->with('success', 'Berhasil masuk.');
            }

            return back()->withErrors([
                'email' => 'Email atau password yang dimasukkan salah.',
            ])->onlyInput('email');
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat mencoba masuk. Silakan coba lagi.');
        }
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
            ]);

            // Auto-create cart for user
            Cart::create(['user_id' => $user->id]);

            Auth::login($user);

            return redirect()->route('customer.home')->with('success', 'Registrasi berhasil. Selamat bergabung!');
        } catch (Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mendaftarkan akun. Silakan coba lagi.');
        }
    }

    /**
     * Simulate Google Login locally for testing.
     */
    public function simulateGoogleLogin()
    {
        try {
            $email = 'google_user@example.com';
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => 'Google Test User',
                    'email' => $email,
                    'password' => null, // OAuth users don't have passwords
                    'role' => 'customer',
                    'provider_id' => 'google-mock-id-12345',
                    'provider_name' => 'google',
                ]);
                
                // Auto-create cart
                Cart::create(['user_id' => $user->id]);
            }

            Auth::login($user);

            return redirect()->route('customer.home')->with('success', 'Berhasil masuk menggunakan akun Google (Simulasi).');
        } catch (Exception $e) {
            Log::error('Simulate Google login error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Simulasi Google Login gagal.');
        }
    }

    /*
    // KODE REAL INTEGRASI GOOGLE SOCIALITE (DITEKAN/DI-COMMENT)
    public function redirectToGoogle()
    {
        try {
            return \Laravel\Socialite\Facades\Socialite::driver('google')->redirect();
        } catch (Exception $e) {
            Log::error('Redirect Google OAuth exception: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Gagal menyambung ke server Google.');
        }
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => null,
                    'role' => 'customer',
                    'provider_id' => $googleUser->getId(),
                    'provider_name' => 'google',
                ]);
                
                Cart::create(['user_id' => $user->id]);
            } else {
                // Link Google accounts if not already linked
                if (empty($user->provider_id)) {
                    $user->update([
                        'provider_id' => $googleUser->getId(),
                        'provider_name' => 'google',
                    ]);
                }
            }
            
            Auth::login($user);
            return redirect()->route('customer.home')->with('success', 'Berhasil masuk dengan Google.');
        } catch (Exception $e) {
            Log::error('Google callback exception: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Gagal otentikasi menggunakan akun Google.');
        }
    }
    */

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->with('success', 'Berhasil keluar.');
        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Terjadi masalah saat mengeluarkan akun.');
        }
    }
}
