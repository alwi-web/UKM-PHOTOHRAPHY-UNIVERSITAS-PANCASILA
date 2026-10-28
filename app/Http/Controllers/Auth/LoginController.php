<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Halaman login terpisah dari website; setelah berhasil masuk diarahkan ke panel. */
class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->panelUrl());
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // maksimal 5 percobaan per menit untuk tiap email + IP
        $key = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        RateLimiter::clear($key);

        if (! Auth::user()->canAccessPanel(Filament::getPanel(Auth::user()->role->panel()))) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun kamu belum aktif. Hubungi Admin UKM.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->panelUrl());
    }

    /** Admin & member ke /admin, kurator ke /kurator, anggota UKM lain ke /anggota. */
    private function panelUrl(): string
    {
        return Filament::getPanel(Auth::user()->role->panel())->getUrl();
    }
}
