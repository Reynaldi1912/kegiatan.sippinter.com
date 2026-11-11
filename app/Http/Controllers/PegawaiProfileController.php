<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PegawaiProfileController extends Controller
{
    /**
     * Halaman Profil Pegawai (PRD 3.10).
     */
    public function profile(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $user->load(['tupoksis', 'penugasans.kegiatan', 'pelaksanaans.kegiatan']);

        if (! $user->api_token) {
            $user->update(['api_token' => 'sipp-mob-'.Str::random(32)]);
        }

        return view('profil.index', compact('user'));
    }

    /**
     * Update profil terbatas sesuai hak akses pegawai (PRD 3.10).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Informasi kontak & profil Anda berhasil diperbarui.');
    }

    /**
     * Ubah Password (PRD 3.10).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! $user->verifyPassword($validated['current_password'])) {
            return back()->with('error', 'Kata sandi lama tidak sesuai.');
        }

        $user->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Regenerate Mobile Sync API Token.
     */
    public function regenerateToken(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->update(['api_token' => 'sipp-mob-'.Str::random(32)]);

        return back()->with('success', 'Token otentikasi sinkronisasi Mobile berhasil diperbarui.');
    }

    /**
     * Halaman Manajemen Akun & Data Kepegawaian Web Application (PRD 3.2 & 3.10).
     */
    public function pegawaiIndex(Request $request): View
    {
        $unit = (string) $request->query('unit_program', '');

        $query = User::query()->withCount(['tupoksis', 'penugasans', 'pelaksanaans']);

        if ($unit !== '') {
            $query->where('unit_program', $unit);
        }

        $pegawais = $query->orderBy('fullname')->get();

        return view('pegawai.index', compact('pegawais', 'unit'));
    }

    public function pegawaiStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:users,nip'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'jabatan' => ['required', 'string', 'max:150'],
            'unit_program' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'in:super_admin,kepala_puskesmas,kepala_tata_usaha,pj_program,pegawai'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $validated['is_active'] = true;
        $validated['api_token'] = 'sipp-mob-'.Str::random(32);

        User::query()->create($validated);

        return back()->with('success', 'Akun Pegawai Puskesmas baru berhasil didaftarkan.');
    }

    public function pegawaiUpdate(Request $request, User $pegawai): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($pegawai->id)],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nip')->ignore($pegawai->id)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($pegawai->id)],
            'jabatan' => ['required', 'string', 'max:150'],
            'unit_program' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'in:super_admin,kepala_puskesmas,kepala_tata_usaha,pj_program,pegawai'],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $pegawai->update($validated);

        return back()->with('success', 'Data kepegawaian '.$pegawai->name.' berhasil diperbarui.');
    }
}
