<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\NotifikasiPegawai;
use App\Models\Pelaksanaan;
use App\Models\Penugasan;
use App\Models\Tupoksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today();
        $filterScope = $request->query('scope', $user->isVerifier() ? 'puskesmas' : 'saya');

        $penugasanQuery = Penugasan::query();

        if ($filterScope === 'saya' || ! $user->isVerifier()) {
            $penugasanQuery->where(function ($q) use ($user) {
                $q->where('penanggung_jawab_id', $user->id)
                    ->orWhereHas('anggotaPelaksana', fn ($sub) => $sub->where('users.id', $user->id));
            });
        }

        $ringkasanPenugasan = [
            'total' => (clone $penugasanQuery)->count(),
            'belum_dikerjakan' => (clone $penugasanQuery)->where('status', 'belum_dikerjakan')->count(),
            'sedang_berjalan' => (clone $penugasanQuery)->where('status', 'sedang_berjalan')->count(),
            'menunggu_verifikasi' => (clone $penugasanQuery)->where('status', 'menunggu_verifikasi')->count(),
            'perlu_perbaikan' => (clone $penugasanQuery)->where('status', 'perlu_perbaikan')->count(),
            'selesai' => (clone $penugasanQuery)->where('status', 'selesai')->count(),
        ];

        $jadwalHariIni = Kegiatan::query()
            ->with(['penanggungJawab', 'penugasans.anggotaPelaksana'])
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->orderBy('waktu_mulai')
            ->limit(6)
            ->get();

        $penugasanTerbaru = (clone $penugasanQuery)
            ->with(['kegiatan', 'penanggungJawab', 'pemberiTugas', 'tupoksi', 'anggotaPelaksana'])
            ->latest()
            ->limit(6)
            ->get();

        $kegiatanMendatang = Kegiatan::query()
            ->with(['penanggungJawab'])
            ->whereDate('tanggal_mulai', '>', $today)
            ->orderBy('tanggal_mulai')
            ->orderBy('waktu_mulai')
            ->limit(6)
            ->get();

        $notifikasiTerbaru = NotifikasiPegawai::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(6)
            ->get();

        $unreadNotifikasiCount = NotifikasiPegawai::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        $tupoksiRingkasan = Tupoksi::query()
            ->with('user')
            ->when($filterScope === 'saya' || ! $user->isVerifier(), fn ($q) => $q->where('user_id', $user->id))
            ->where('is_active', true)
            ->limit(5)
            ->get();

        $laporanMenungguVerifikasi = Pelaksanaan::query()
            ->with(['penugasan.kegiatan', 'pelapor', 'dokumentasis'])
            ->whereIn('status_laporan', ['dikirim', 'diterima'])
            ->latest()
            ->limit(5)
            ->get();

        $statistikPuskesmas = [
            'total_pegawai' => User::query()->where('is_active', true)->count(),
            'total_kegiatan_rpk' => Kegiatan::query()->count(),
            'total_pelaksanaan' => Pelaksanaan::query()->count(),
            'total_pagu_rpk' => (float) Kegiatan::query()->sum('pagu_anggaran'),
            'total_realisasi_rpk' => (float) Kegiatan::query()->sum('realisasi_anggaran'),
            'rata_capaian_tupoksi' => (float) (Tupoksi::query()
                ->where('target', '>', 0)
                ->get()
                ->avg(fn (Tupoksi $t) => $t->persentase_capaian) ?? 0),
        ];

        // Chart trends built dynamically from database records (Kegiatan, Penugasan & Pelaksanaan)
        $chartMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $chartDataOne = [];
        $chartDataTwo = [];

        $allKegiatans = Kegiatan::query()->get(['tanggal_mulai', 'target_kegiatan']);
        $allPenugasans = Penugasan::query()->get(['tanggal_mulai', 'target_pekerjaan']);
        $allPelaksanaans = Pelaksanaan::query()->get(['tanggal_mulai', 'jumlah_target', 'jumlah_realisasi']);

        foreach (range(1, 12) as $monthIndex) {
            $monthTarget = (int) $allKegiatans
                ->filter(fn (Kegiatan $k) => $k->tanggal_mulai && (int) $k->tanggal_mulai->format('n') === $monthIndex)
                ->sum('target_kegiatan')
                + (int) $allPenugasans
                    ->filter(fn (Penugasan $p) => $p->tanggal_mulai && (int) $p->tanggal_mulai->format('n') === $monthIndex)
                    ->sum('target_pekerjaan');

            $monthRealisasi = (int) $allPelaksanaans
                ->filter(fn (Pelaksanaan $l) => $l->tanggal_mulai && (int) $l->tanggal_mulai->format('n') === $monthIndex)
                ->sum('jumlah_realisasi');

            $chartDataOne[] = $monthTarget;
            $chartDataTwo[] = $monthRealisasi;
        }

        // Bottom table built dynamically from database Penugasan & Pelaksanaan records
        $patientRecords = (clone $penugasanQuery)
            ->with(['kegiatan', 'penanggungJawab', 'tupoksi', 'pelaksanaans'])
            ->latest('tanggal_mulai')
            ->limit(8)
            ->get()
            ->map(function (Penugasan $penugasan): array {
                $pj = $penugasan->penanggungJawab;
                $kegiatan = $penugasan->kegiatan;
                $latestLaporan = $penugasan->pelaksanaans->sortByDesc('id')->first();

                return [
                    'id' => $penugasan->id,
                    'nomor_surat' => $penugasan->nomor_surat_tugas,
                    'name' => $pj?->name ?? '-',
                    'nip' => $pj?->nip ?: ($pj?->username ?? '-'),
                    'jabatan' => $pj?->jabatan ?: ($pj?->role ?? 'Pegawai Puskesmas'),
                    'address' => $kegiatan?->lokasi ?? 'Wilayah Kerja Puskesmas',
                    'phone' => $pj?->phone ?: '-',
                    'email' => $pj?->email ?: '-',
                    'kegiatan_nama' => $kegiatan?->nama_kegiatan ?? $penugasan->instruksi_pekerjaan,
                    'program' => $kegiatan?->program ?? ($penugasan->tupoksi?->nama_tupoksi ?? '-'),
                    'target_text' => ($latestLaporan?->jumlah_realisasi ?? 0).' / '.$penugasan->target_pekerjaan.' '.$penugasan->satuan,
                    'tanggal' => $penugasan->tanggal_mulai ? $penugasan->tanggal_mulai->translatedFormat('d M Y') : '-',
                    'status' => $penugasan->status,
                ];
            })
            ->all();

        return view('dashboard.index', compact(
            'user',
            'today',
            'filterScope',
            'ringkasanPenugasan',
            'jadwalHariIni',
            'penugasanTerbaru',
            'kegiatanMendatang',
            'notifikasiTerbaru',
            'unreadNotifikasiCount',
            'tupoksiRingkasan',
            'laporanMenungguVerifikasi',
            'statistikPuskesmas',
            'chartMonths',
            'chartDataOne',
            'chartDataTwo',
            'patientRecords'
        ));
    }
}
