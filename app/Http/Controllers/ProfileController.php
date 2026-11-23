<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Profile/Index', [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nip ?: '-',
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Nama saja yang boleh diubah sendiri; email, NIP dan role dikelola Administrator.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $request->user()->update(['name' => trim($validated['name'])]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string|current_password',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.current_password' => 'Password saat ini salah.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        $request->user()->update(['password' => Hash::make($validated['password'])]);

        // Token sesi baru setelah ganti password; sesi aktif tetap login
        $request->session()->regenerate();

        return back()->with('success', 'Password berhasil diganti.');
    }
}
