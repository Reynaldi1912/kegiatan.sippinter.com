<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.index');
    }

    public function login(Request $request): RedirectResponse
    {
        if (! $request->filled('email') && $request->filled('username')) {
            $request->merge(['email' => $request->input('username')]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($credentials['email']);

        if (User::query()->count() === 0) {
            Artisan::call('db:seed', ['--force' => true]);
        }

        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('nip', $identifier)
            ->when($identifier === 'admin', fn ($q) => $q->orWhere('email', 'admin@sippinter.com'))
            ->first();

        if (! $user || ! $user->verifyPassword($credentials['password'])) {
            return back()
                ->withInput($request->only('email', 'username', 'remember'))
                ->with('error', 'Email/Username/NIP atau kata sandi tidak sesuai.');
        }

        if ($user->is_active === false) {
            return back()
                ->withInput($request->only('email', 'username'))
                ->with('error', 'Akun pegawai Anda sedang dinonaktifkan. Hubungi Administrator Web Puskesmas.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang kembali, '.$user->name.'!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    public function clearCache(): RedirectResponse
    {
        Artisan::call('optimize:clear');

        return back()->with('success', 'Cache aplikasi berhasil dibersihkan.');
    }
}
