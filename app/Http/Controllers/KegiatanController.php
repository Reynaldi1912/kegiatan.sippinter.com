<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\Kegiatan;
use App\Models\NotifikasiPegawai;
use App\Models\Tupoksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    /**
     * Halaman Perencanaan Kegiatan (RUK, RPK, RBA) & Penganggaran Web Application.
     */
    public function index(Request $request): View
    {
        $query = Kegiatan::query()
            ->with(['penanggungJawab', 'penugasans.anggotaPelaksana', 'rekening']);

        if ($request->filled('program')) {
            $query->where('program', $request->string('program'));
        }

        if ($request->filled('jenis_dokumen')) {
            $query->where('jenis_dokumen', $request->string('jenis_dokumen'));
        }

        if ($request->filled('sumber_dana')) {
            $query->where('sumber_dana', $request->string('sumber_dana'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $kegiatans = $query->orderBy('tanggal_mulai', 'desc')->get();
        $pegawais = User::query()->where('is_active', true)->orderBy('fullname')->get();
        $rekenings = ChartOfAccount::query()->where('is_active', true)->orderBy('kode_akun')->limit(100)->get();

        $ringkasanRpk = [
            'total_kegiatan' => $kegiatans->count(),
            'total_pagu' => (float) $kegiatans->sum('pagu_anggaran'),
            'total_realisasi' => (float) $kegiatans->sum('realisasi_anggaran'),
            'selesai' => $kegiatans->where('status', 'selesai')->count(),
        ];

        return view('kegiatan.index', compact('kegiatans', 'pegawais', 'rekenings', 'ringkasanRpk'));
    }

    /**
     * Halaman Jadwal Kegiatan (PRD 3.4: Mode Kalender Bulanan & Daftar Jadwal).
     */
    public function jadwal(Request $request): View
    {
        $bulan = (int) $request->query('bulan', (int) now()->format('n'));
        $tahun = (int) $request->query('tahun', (int) now()->format('Y'));
        $mode = (string) $request->query('mode', 'kalender');
        $status = (string) $request->query('status', '');

        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $query = Kegiatan::query()
            ->with(['penanggungJawab', 'penugasans.anggotaPelaksana'])
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('tanggal_selesai', [$startOfMonth, $endOfMonth])
                    ->orWhere(function ($sub) use ($startOfMonth, $endOfMonth) {
                        $sub->where('tanggal_mulai', '<=', $startOfMonth)
                            ->where('tanggal_selesai', '>=', $endOfMonth);
                    });
            });

        if ($status !== '') {
            $query->where('status', $status);
        }

        $jadwals = $query->orderBy('tanggal_mulai')->orderBy('waktu_mulai')->get();

        $jadwalPerTanggal = [];
        foreach ($jadwals as $jadwal) {
            $cursor = $jadwal->tanggal_mulai->copy()->max($startOfMonth);
            $limit = $jadwal->tanggal_selesai->copy()->min($endOfMonth);

            while ($cursor->lte($limit)) {
                $dateKey = $cursor->format('Y-m-d');
                $jadwalPerTanggal[$dateKey][] = $jadwal;
                $cursor->addDay();
            }
        }

        $pegawais = User::query()->where('is_active', true)->orderBy('fullname')->get();

        return view('jadwal.index', compact(
            'jadwals',
            'jadwalPerTanggal',
            'bulan',
            'tahun',
            'mode',
            'status',
            'startOfMonth',
            'endOfMonth',
            'pegawais'
        ));
    }

    /**
     * Halaman Detail Jadwal & Kegiatan (PRD 3.4).
     */
    public function show(Kegiatan $kegiatan): View
    {
        $kegiatan->load([
            'penanggungJawab',
            'rekening',
            'penugasans.penanggungJawab',
            'penugasans.pemberiTugas',
            'penugasans.anggotaPelaksana',
            'penugasans.tupoksi',
            'pelaksanaans.pelapor',
            'pelaksanaans.dokumentasis',
        ]);

        $pegawais = User::query()->where('is_active', true)->orderBy('fullname')->get();
        $tupoksis = Tupoksi::query()->with('user')->where('is_active', true)->get();

        return view('jadwal.show', compact('kegiatan', 'pegawais', 'tupoksis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kegiatan' => ['nullable', 'string', 'max:50', 'unique:kegiatans,kode_kegiatan'],
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'program' => ['required', 'string', 'max:150'],
            'jenis_dokumen' => ['required', 'string', 'in:RUK,RPK,RBA'],
            'sumber_dana' => ['required', 'string', 'max:100'],
            'sumber_kegiatan' => ['required', 'string', 'max:100'],
            'kode_rekening' => ['nullable', 'string', 'max:50'],
            'pagu_anggaran' => ['nullable', 'numeric', 'min:0'],
            'realisasi_anggaran' => ['nullable', 'numeric', 'min:0'],
            'status_keuangan' => ['nullable', 'string', 'in:belum_ditagihkan,proses_penagihan,dibayar'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'waktu_mulai' => ['required', 'string', 'max:20'],
            'waktu_selesai' => ['required', 'string', 'max:20'],
            'lokasi' => ['required', 'string', 'max:255'],
            'penanggung_jawab_id' => ['required', 'exists:users,id'],
            'target_kegiatan' => ['required', 'integer', 'min:1'],
            'satuan_target' => ['required', 'string', 'max:100'],
            'indikator_keberhasilan' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:terjadwal,berjalan,selesai,dibatalkan'],
        ]);

        if (empty($validated['kode_kegiatan'])) {
            $nextNumber = Kegiatan::query()->count() + 1;
            $validated['kode_kegiatan'] = sprintf('%s-%s-%03d', $validated['jenis_dokumen'], now()->format('Y'), $nextNumber);
        }

        $validated['pagu_anggaran'] = $validated['pagu_anggaran'] ?? 0;
        $validated['realisasi_anggaran'] = $validated['realisasi_anggaran'] ?? 0;
        $validated['status_keuangan'] = $validated['status_keuangan'] ?? 'belum_ditagihkan';

        Kegiatan::query()->create($validated);

        return back()->with('success', 'Data Kegiatan & Jadwal RPK berhasil ditambahkan.');
    }

    public function update(Request $request, Kegiatan $kegiatan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'program' => ['required', 'string', 'max:150'],
            'jenis_dokumen' => ['required', 'string', 'in:RUK,RPK,RBA'],
            'sumber_dana' => ['required', 'string', 'max:100'],
            'sumber_kegiatan' => ['required', 'string', 'max:100'],
            'kode_rekening' => ['nullable', 'string', 'max:50'],
            'pagu_anggaran' => ['nullable', 'numeric', 'min:0'],
            'realisasi_anggaran' => ['nullable', 'numeric', 'min:0'],
            'status_keuangan' => ['required', 'string', 'in:belum_ditagihkan,proses_penagihan,dibayar'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'waktu_mulai' => ['required', 'string', 'max:20'],
            'waktu_selesai' => ['required', 'string', 'max:20'],
            'lokasi' => ['required', 'string', 'max:255'],
            'penanggung_jawab_id' => ['required', 'exists:users,id'],
            'target_kegiatan' => ['required', 'integer', 'min:1'],
            'satuan_target' => ['required', 'string', 'max:100'],
            'indikator_keberhasilan' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:terjadwal,berjalan,selesai,dibatalkan'],
        ]);

        $jadwalChanged = $kegiatan->tanggal_mulai->format('Y-m-d') !== $validated['tanggal_mulai']
            || $kegiatan->tanggal_selesai->format('Y-m-d') !== $validated['tanggal_selesai']
            || $kegiatan->waktu_mulai !== $validated['waktu_mulai'];

        $lokasiChanged = $kegiatan->lokasi !== $validated['lokasi'];

        $kegiatan->update($validated);

        if ($jadwalChanged || $lokasiChanged) {
            $kegiatan->load('penugasans.anggotaPelaksana');
            $recipientIds = collect([$kegiatan->penanggung_jawab_id]);

            foreach ($kegiatan->penugasans as $penugasan) {
                $recipientIds->push($penugasan->penanggung_jawab_id);
                foreach ($penugasan->anggotaPelaksana as $anggota) {
                    $recipientIds->push($anggota->id);
                }
            }

            foreach ($recipientIds->unique() as $userId) {
                if ($jadwalChanged) {
                    NotifikasiPegawai::query()->create([
                        'user_id' => $userId,
                        'kegiatan_id' => $kegiatan->id,
                        'jenis_notifikasi' => 'perubahan_jadwal',
                        'judul' => 'Perubahan Jadwal Kegiatan: '.$kegiatan->nama_kegiatan,
                        'pesan' => 'Jadwal kegiatan diperbarui menjadi '.Carbon::parse($validated['tanggal_mulai'])->translatedFormat('d M Y').' pukul '.$validated['waktu_mulai'].' - '.$validated['waktu_selesai'].' WIB.',
                        'url_tujuan' => route('jadwal.show', $kegiatan),
                    ]);
                }

                if ($lokasiChanged) {
                    NotifikasiPegawai::query()->create([
                        'user_id' => $userId,
                        'kegiatan_id' => $kegiatan->id,
                        'jenis_notifikasi' => 'perubahan_lokasi',
                        'judul' => 'Perubahan Lokasi Kegiatan: '.$kegiatan->nama_kegiatan,
                        'pesan' => 'Lokasi pelaksanaan kegiatan telah dipindahkan ke: '.$validated['lokasi'].'.',
                        'url_tujuan' => route('jadwal.show', $kegiatan),
                    ]);
                }
            }
        }

        return back()->with('success', 'Data Kegiatan & Jadwal berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan): RedirectResponse
    {
        $kegiatan->delete();

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}
