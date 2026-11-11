<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\NotifikasiPegawai;
use App\Models\Penugasan;
use App\Models\Tupoksi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PenugasanController extends Controller
{
    /**
     * Halaman Utama Penugasan (PRD 3.5).
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $kategori = (string) $request->query('kategori', 'semua');
        $prioritas = (string) $request->query('prioritas', '');
        $pegawaiId = $request->query('pegawai_id');

        $baseQuery = Penugasan::query()
            ->with([
                'kegiatan',
                'tupoksi',
                'pemberiTugas',
                'penanggungJawab',
                'anggotaPelaksana',
                'pelaksanaans',
            ]);

        if ($pegawaiId) {
            $baseQuery->where(function ($q) use ($pegawaiId) {
                $q->where('penanggung_jawab_id', $pegawaiId)
                    ->orWhereHas('anggotaPelaksana', fn ($sub) => $sub->where('users.id', $pegawaiId));
            });
        }

        if ($prioritas !== '') {
            $baseQuery->where('prioritas', $prioritas);
        }

        $counts = [
            'semua' => (clone $baseQuery)->count(),
            'belum_dikerjakan' => (clone $baseQuery)->where('status', 'belum_dikerjakan')->count(),
            'sedang_berjalan' => (clone $baseQuery)->where('status', 'sedang_berjalan')->count(),
            'menunggu_verifikasi' => (clone $baseQuery)->where('status', 'menunggu_verifikasi')->count(),
            'perlu_perbaikan' => (clone $baseQuery)->where('status', 'perlu_perbaikan')->count(),
            'selesai' => (clone $baseQuery)->where('status', 'selesai')->count(),
        ];

        $query = clone $baseQuery;
        if ($kategori !== 'semua' && array_key_exists($kategori, $counts)) {
            $query->where('status', $kategori);
        }

        $penugasans = $query->latest()->get();

        $kegiatans = Kegiatan::query()->orderBy('tanggal_mulai', 'desc')->get();
        $pegawais = User::query()->where('is_active', true)->orderBy('fullname')->get();
        $tupoksis = Tupoksi::query()->with('user')->where('is_active', true)->get();

        return view('penugasan.index', compact(
            'penugasans',
            'counts',
            'kategori',
            'prioritas',
            'pegawaiId',
            'kegiatans',
            'pegawais',
            'tupoksis'
        ));
    }

    /**
     * Halaman Detail Penugasan (PRD 3.5).
     */
    public function show(Penugasan $penugasan): View
    {
        $penugasan->load([
            'kegiatan.rekening',
            'tupoksi.user',
            'pemberiTugas',
            'penanggungJawab',
            'anggotaPelaksana',
            'pelaksanaans.pelapor',
            'pelaksanaans.verifikator',
            'pelaksanaans.dokumentasis',
            'dokumentasis.uploader',
        ]);

        $pegawais = User::query()->where('is_active', true)->orderBy('fullname')->get();
        $tupoksis = Tupoksi::query()->with('user')->where('is_active', true)->get();

        return view('penugasan.show', compact('penugasan', 'pegawais', 'tupoksis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_surat_tugas' => ['nullable', 'string', 'max:100', 'unique:penugasans,nomor_surat_tugas'],
            'kegiatan_id' => ['required', 'exists:kegiatans,id'],
            'tupoksi_id' => ['nullable', 'exists:tupoksis,id'],
            'penanggung_jawab_id' => ['required', 'exists:users,id'],
            'anggota_ids' => ['nullable', 'array'],
            'anggota_ids.*' => ['exists:users,id'],
            'instruksi_pekerjaan' => ['required', 'string'],
            'sumber_kegiatan' => ['required', 'string', 'max:100'],
            'prioritas' => ['required', 'string', 'in:rendah,sedang,tinggi,mendesak'],
            'tanggal_mulai' => ['required', 'date'],
            'tenggat_waktu' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'target_pekerjaan' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:100'],
            'indikator_keberhasilan' => ['nullable', 'string'],
        ]);

        if (empty($validated['nomor_surat_tugas'])) {
            $next = Penugasan::query()->count() + 1;
            $validated['nomor_surat_tugas'] = sprintf('800/ST-%03d/PKM/%s/%s', $next, now()->format('m'), now()->format('Y'));
        }

        $penugasan = Penugasan::query()->create([
            'nomor_surat_tugas' => $validated['nomor_surat_tugas'],
            'kegiatan_id' => $validated['kegiatan_id'],
            'tupoksi_id' => $validated['tupoksi_id'] ?? null,
            'pemberi_tugas_id' => Auth::id(),
            'penanggung_jawab_id' => $validated['penanggung_jawab_id'],
            'instruksi_pekerjaan' => $validated['instruksi_pekerjaan'],
            'sumber_kegiatan' => $validated['sumber_kegiatan'],
            'prioritas' => $validated['prioritas'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tenggat_waktu' => $validated['tenggat_waktu'],
            'target_pekerjaan' => $validated['target_pekerjaan'],
            'satuan' => $validated['satuan'],
            'indikator_keberhasilan' => $validated['indikator_keberhasilan'] ?? null,
            'status' => 'belum_dikerjakan',
        ]);

        $syncData = [
            (int) $validated['penanggung_jawab_id'] => ['peran' => 'Penanggung Jawab'],
        ];

        foreach ($validated['anggota_ids'] ?? [] as $anggotaId) {
            $id = (int) $anggotaId;
            if (! isset($syncData[$id])) {
                $syncData[$id] = ['peran' => 'Anggota Pelaksana'];
            }
        }

        $penugasan->anggotaPelaksana()->sync($syncData);
        $penugasan->load('kegiatan');

        $jenisNotif = str_contains(strtolower($validated['sumber_kegiatan']), 'disposisi')
            ? 'disposisi_baru'
            : 'penugasan_baru';

        foreach (array_keys($syncData) as $recipientId) {
            NotifikasiPegawai::query()->create([
                'user_id' => $recipientId,
                'penugasan_id' => $penugasan->id,
                'kegiatan_id' => $penugasan->kegiatan_id,
                'jenis_notifikasi' => $jenisNotif,
                'judul' => ($jenisNotif === 'disposisi_baru' ? 'Disposisi Baru: ' : 'Penugasan Baru: ').$penugasan->kegiatan->nama_kegiatan,
                'pesan' => 'Anda menerima penugasan nomor '.$penugasan->nomor_surat_tugas.' dengan tenggat waktu '.$penugasan->tenggat_waktu->translatedFormat('d M Y').'.',
                'url_tujuan' => route('penugasan.show', $penugasan),
            ]);
        }

        return redirect()->route('penugasan.show', $penugasan)
            ->with('success', 'Penugasan berhasil diterbitkan dan notifikasi telah dikirim ke pegawai.');
    }

    public function update(Request $request, Penugasan $penugasan): RedirectResponse
    {
        $validated = $request->validate([
            'tupoksi_id' => ['nullable', 'exists:tupoksis,id'],
            'penanggung_jawab_id' => ['required', 'exists:users,id'],
            'anggota_ids' => ['nullable', 'array'],
            'anggota_ids.*' => ['exists:users,id'],
            'instruksi_pekerjaan' => ['required', 'string'],
            'sumber_kegiatan' => ['required', 'string', 'max:100'],
            'prioritas' => ['required', 'string', 'in:rendah,sedang,tinggi,mendesak'],
            'tanggal_mulai' => ['required', 'date'],
            'tenggat_waktu' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'target_pekerjaan' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:100'],
            'indikator_keberhasilan' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:belum_dikerjakan,sedang_berjalan,menunggu_verifikasi,perlu_perbaikan,selesai'],
        ]);

        $instruksiChanged = $penugasan->instruksi_pekerjaan !== $validated['instruksi_pekerjaan'];

        $penugasan->update([
            'tupoksi_id' => $validated['tupoksi_id'] ?? null,
            'penanggung_jawab_id' => $validated['penanggung_jawab_id'],
            'instruksi_pekerjaan' => $validated['instruksi_pekerjaan'],
            'sumber_kegiatan' => $validated['sumber_kegiatan'],
            'prioritas' => $validated['prioritas'],
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tenggat_waktu' => $validated['tenggat_waktu'],
            'target_pekerjaan' => $validated['target_pekerjaan'],
            'satuan' => $validated['satuan'],
            'indikator_keberhasilan' => $validated['indikator_keberhasilan'] ?? null,
            'status' => $validated['status'],
        ]);

        $syncData = [
            (int) $validated['penanggung_jawab_id'] => ['peran' => 'Penanggung Jawab'],
        ];

        foreach ($validated['anggota_ids'] ?? [] as $anggotaId) {
            $id = (int) $anggotaId;
            if (! isset($syncData[$id])) {
                $syncData[$id] = ['peran' => 'Anggota Pelaksana'];
            }
        }

        $penugasan->anggotaPelaksana()->sync($syncData);
        $penugasan->load('kegiatan');

        if ($instruksiChanged) {
            foreach (array_keys($syncData) as $recipientId) {
                NotifikasiPegawai::query()->create([
                    'user_id' => $recipientId,
                    'penugasan_id' => $penugasan->id,
                    'kegiatan_id' => $penugasan->kegiatan_id,
                    'jenis_notifikasi' => 'perubahan_instruksi',
                    'judul' => 'Perubahan Instruksi Pekerjaan: '.$penugasan->kegiatan->nama_kegiatan,
                    'pesan' => 'Instruksi pekerjaan pada penugasan '.$penugasan->nomor_surat_tugas.' telah diperbarui oleh pemberi tugas.',
                    'url_tujuan' => route('penugasan.show', $penugasan),
                ]);
            }
        }

        return back()->with('success', 'Data penugasan berhasil diperbarui.');
    }

    /**
     * Tindakan: Mulai Pelaksanaan (PRD 3.5).
     */
    public function mulaiPelaksanaan(Penugasan $penugasan): RedirectResponse
    {
        $penugasan->update(['status' => 'sedang_berjalan']);
        $penugasan->kegiatan()->where('status', 'terjadwal')->update(['status' => 'berjalan']);

        return back()->with('success', 'Status penugasan diubah menjadi Sedang Berjalan. Silakan laporkan hasil pelaksanaan.');
    }

    public function destroy(Penugasan $penugasan): RedirectResponse
    {
        $penugasan->delete();

        return redirect()->route('penugasan.index')->with('success', 'Penugasan berhasil dihapus.');
    }
}
