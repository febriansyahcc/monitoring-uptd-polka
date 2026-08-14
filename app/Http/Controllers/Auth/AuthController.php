<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = $credentials['login'];
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'nip';

        $user = User::where($fieldType, $loginInput)->first();

        if (!$user) {
            return back()->withErrors([
                'login' => 'Email atau NIP tidak ditemukan dalam sistem.',
            ]);
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'login' => 'Akun Anda sedang nonaktif. Silakan hubungi Administrator.',
            ]);
        }

        if (Auth::attempt([$fieldType => $loginInput, 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'))->with('success', "Selamat datang kembali, {$user->name}!");
        }

        return back()->withErrors([
            'password' => 'Password yang Anda masukkan salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari akun.');
    }

    public function showForgotPassword()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Mock simulation of reset link sent for local dev
        return back()->with('status', 'Tautan pemulihan password telah dikirim ke email Anda (atau hubungi Admin untuk reset cepat).');
    }
}
