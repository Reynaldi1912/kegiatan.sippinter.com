<?php

namespace App\Http\Controllers;

use App\Models\Pelaksanaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    /**
     * Halaman Riwayat Kegiatan (PRD 3.8).
     */
    public function index(Request $request): View
    {
        $periodeBulan = $request->query('bulan');
        $periodeTahun = $request->query('tahun', now()->format('Y'));
        $statusVerifikasi = (string) $request->query('status_verifikasi', '');
        $pelaporId = $request->query('pelapor_id');

        $query = Pelaksanaan::query()
            ->with([
                'kegiatan',
                'penugasan.penanggungJawab',
                'penugasan.pemberiTugas',
                'tupoksi',
                'pelapor',
                'verifikator',
                'dokumentasis',
            ]);

        if ($periodeTahun) {
            $query->whereYear('tanggal_mulai', (int) $periodeTahun);
        }

        if ($periodeBulan) {
            $query->whereMonth('tanggal_mulai', (int) $periodeBulan);
        }

        if ($statusVerifikasi !== '') {
            $query->where('status_laporan', $statusVerifikasi);
        }

        if ($pelaporId) {
            $query->where('pelapor_id', (int) $pelaporId);
        }

        $riwayats = $query->orderBy('tanggal_selesai', 'desc')->get();
        $pegawais = User::query()->where('is_active', true)->orderBy('fullname')->get();

        return view('riwayat.index', compact(
            'riwayats',
            'pegawais',
            'periodeBulan',
            'periodeTahun',
            'statusVerifikasi',
            'pelaporId'
        ));
    }

    /**
     * Halaman Detail Riwayat Kegiatan (PRD 3.8).
     */
    public function show(Pelaksanaan $pelaksanaan): View
    {
        $pelaksanaan->load([
            'kegiatan.rekening',
            'penugasan.pemberiTugas',
            'penugasan.penanggungJawab',
            'penugasan.anggotaPelaksana',
            'tupoksi.user',
            'pelapor',
            'verifikator',
            'dokumentasis.uploader',
        ]);

        return view('riwayat.show', compact('pelaksanaan'));
    }
}
