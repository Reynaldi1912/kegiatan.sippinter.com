<?php

namespace App\Http\Controllers;

use App\Models\Tupoksi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TupoksiController extends Controller
{
    /**
     * Halaman Utama Capaian Tupoksi (PRD 3.7).
     */
    public function index(Request $request): View
    {
        $userId = $request->query('user_id');
        $periode = (string) $request->query('periode', '');

        $query = Tupoksi::query()
            ->with(['user', 'penugasans.kegiatan', 'pelaksanaans'])
            ->where('is_active', true);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($periode !== '') {
            $query->where('periode', $periode);
        }

        $tupoksis = $query->orderBy('kode_tupoksi')->get();
        $pegawais = User::query()->where('is_active', true)->orderBy('fullname')->get();

        $ringkasanTupoksi = [
            'total_tupoksi' => $tupoksis->count(),
            'total_target' => (int) $tupoksis->sum('target'),
            'total_realisasi' => (int) $tupoksis->sum('realisasi'),
            'rata_capaian' => round($tupoksis->avg(fn (Tupoksi $t) => $t->persentase_capaian) ?? 0, 1),
        ];

        return view('tupoksi.index', compact('tupoksis', 'pegawais', 'userId', 'periode', 'ringkasanTupoksi'));
    }

    /**
     * Halaman Detail Capaian Tupoksi (PRD 3.7).
     */
    public function show(Tupoksi $tupoksi): View
    {
        $tupoksi->load([
            'user',
            'penugasans.kegiatan',
            'penugasans.penanggungJawab',
            'penugasans.anggotaPelaksana',
            'pelaksanaans.pelapor',
            'pelaksanaans.verifikator',
            'pelaksanaans.kegiatan',
            'pelaksanaans.dokumentasis',
        ]);

        return view('tupoksi.show', compact('tupoksi'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_tupoksi' => ['nullable', 'string', 'max:50', 'unique:tupoksis,kode_tupoksi'],
            'user_id' => ['required', 'exists:users,id'],
            'nama_tupoksi' => ['required', 'string', 'max:255'],
            'uraian_tugas' => ['required', 'string'],
            'target' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:100'],
            'indikator_keberhasilan' => ['nullable', 'string'],
            'periode' => ['required', 'string', 'max:100'],
        ]);

        if (empty($validated['kode_tupoksi'])) {
            $next = Tupoksi::query()->count() + 1;
            $validated['kode_tupoksi'] = sprintf('TPK-%s-%03d', now()->format('Y'), $next);
        }

        $validated['realisasi'] = 0;
        $validated['is_active'] = true;

        Tupoksi::query()->create($validated);

        return back()->with('success', 'Master & Target Tupoksi Pegawai berhasil ditambahkan.');
    }

    public function update(Request $request, Tupoksi $tupoksi): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'nama_tupoksi' => ['required', 'string', 'max:255'],
            'uraian_tugas' => ['required', 'string'],
            'target' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:100'],
            'indikator_keberhasilan' => ['nullable', 'string'],
            'periode' => ['required', 'string', 'max:100'],
        ]);

        $tupoksi->update($validated);
        $tupoksi->recalculateRealisasi();

        return back()->with('success', 'Data Tupoksi berhasil diperbarui.');
    }
}
