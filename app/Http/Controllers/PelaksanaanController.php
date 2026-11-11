<?php

namespace App\Http\Controllers;

use App\Models\DokumentasiKegiatan;
use App\Models\NotifikasiPegawai;
use App\Models\Pelaksanaan;
use App\Models\Penugasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PelaksanaanController extends Controller
{
    /**
     * Halaman Daftar Pelaksanaan Kegiatan & Verifikasi Laporan (PRD 3.6).
     */
    public function index(Request $request): View
    {
        $statusLaporan = (string) $request->query('status_laporan', '');

        $query = Pelaksanaan::query()
            ->with([
                'penugasan.kegiatan',
                'penugasan.penanggungJawab',
                'tupoksi',
                'pelapor',
                'verifikator',
                'dokumentasis',
            ]);

        if ($statusLaporan !== '') {
            $query->where('status_laporan', $statusLaporan);
        }

        $pelaksanaans = $query->latest()->get();

        $ringkasanLaporan = [
            'semua' => Pelaksanaan::query()->count(),
            'draft' => Pelaksanaan::query()->where('status_laporan', 'draft')->count(),
            'dikirim' => Pelaksanaan::query()->whereIn('status_laporan', ['dikirim', 'diterima'])->count(),
            'perlu_perbaikan' => Pelaksanaan::query()->where('status_laporan', 'perlu_perbaikan')->count(),
            'disetujui' => Pelaksanaan::query()->where('status_laporan', 'disetujui')->count(),
        ];

        $penugasanTersedia = Penugasan::query()
            ->with(['kegiatan', 'penanggungJawab', 'tupoksi'])
            ->whereIn('status', ['belum_dikerjakan', 'sedang_berjalan', 'perlu_perbaikan', 'menunggu_verifikasi'])
            ->latest()
            ->get();

        return view('pelaksanaan.index', compact(
            'pelaksanaans',
            'ringkasanLaporan',
            'statusLaporan',
            'penugasanTersedia'
        ));
    }

    /**
     * Halaman Form Pelaksanaan Kegiatan (PRD 3.6).
     */
    public function create(Request $request): View
    {
        $selectedPenugasanId = $request->query('penugasan_id');

        $penugasans = Penugasan::query()
            ->with(['kegiatan', 'penanggungJawab', 'tupoksi', 'anggotaPelaksana'])
            ->latest()
            ->get();

        $selectedPenugasan = $selectedPenugasanId
            ? $penugasans->firstWhere('id', (int) $selectedPenugasanId)
            : $penugasans->first();

        return view('pelaksanaan.create', compact('penugasans', 'selectedPenugasan'));
    }

    /**
     * Simpan Draft / Kirim Laporan Pelaksanaan & Dokumentasi (PRD 3.6 & 4.3).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'penugasan_id' => ['required', 'exists:penugasans,id'],
            'client_sync_id' => ['nullable', 'string', 'max:100'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'uraian_hasil' => ['required', 'string'],
            'jumlah_target' => ['required', 'integer', 'min:1'],
            'jumlah_realisasi' => ['required', 'integer', 'min:0'],
            'satuan' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'catatan_pegawai' => ['nullable', 'string'],
            'aksi_simpan' => ['required', 'string', 'in:draft,kirim'],
            'foto_dokumentasi' => ['nullable', 'array'],
            'foto_dokumentasi.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'dokumen_pendukung' => ['nullable', 'array'],
            'dokumen_pendukung.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
            'keterangan_dokumentasi' => ['nullable', 'string', 'max:255'],
        ]);

        // Pencegahan duplikasi laporan berdasarkan client_sync_id (PRD 4.3 & 6)
        if (! empty($validated['client_sync_id'])) {
            $existing = Pelaksanaan::query()->where('client_sync_id', $validated['client_sync_id'])->first();
            if ($existing) {
                return redirect()->route('pelaksanaan.show', $existing)
                    ->with('success', 'Laporan pelaksanaan sudah tercatat sebelumnya (pencegahan duplikasi aktif).');
            }
        }

        /** @var Penugasan $penugasan */
        $penugasan = Penugasan::query()->with(['kegiatan', 'tupoksi'])->findOrFail($validated['penugasan_id']);

        $statusLaporan = $validated['aksi_simpan'] === 'draft' ? 'draft' : 'dikirim';
        $nextNumber = Pelaksanaan::query()->count() + 1;
        $kodeLaporan = sprintf('LPR-%s-%04d', now()->format('Ym'), $nextNumber);

        $pelaksanaan = Pelaksanaan::query()->create([
            'kode_laporan' => $kodeLaporan,
            'client_sync_id' => $validated['client_sync_id'] ?? (string) Str::uuid(),
            'penugasan_id' => $penugasan->id,
            'kegiatan_id' => $penugasan->kegiatan_id,
            'tupoksi_id' => $penugasan->tupoksi_id,
            'pelapor_id' => Auth::id(),
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'uraian_hasil' => $validated['uraian_hasil'],
            'jumlah_target' => $validated['jumlah_target'],
            'jumlah_realisasi' => $validated['jumlah_realisasi'],
            'satuan' => $validated['satuan'],
            'keterangan' => $validated['keterangan'] ?? null,
            'catatan_pegawai' => $validated['catatan_pegawai'] ?? null,
            'status_laporan' => $statusLaporan,
            'sumber_input' => 'web',
            'status_sinkronisasi' => 'tersinkron',
            'synced_at' => now(),
        ]);

        if ($request->hasFile('foto_dokumentasi')) {
            foreach ($request->file('foto_dokumentasi') as $file) {
                $path = $file->store('dokumentasi/foto', 'public');
                DokumentasiKegiatan::query()->create([
                    'pelaksanaan_id' => $pelaksanaan->id,
                    'penugasan_id' => $penugasan->id,
                    'uploaded_by' => Auth::id(),
                    'jenis_dokumen' => 'foto',
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'ukuran_kb' => (int) ceil($file->getSize() / 1024),
                    'keterangan' => $validated['keterangan_dokumentasi'] ?? 'Foto Dokumentasi Pelaksanaan Kegiatan',
                ]);
            }
        }

        if ($request->hasFile('dokumen_pendukung')) {
            foreach ($request->file('dokumen_pendukung') as $file) {
                $path = $file->store('dokumentasi/dokumen', 'public');
                DokumentasiKegiatan::query()->create([
                    'pelaksanaan_id' => $pelaksanaan->id,
                    'penugasan_id' => $penugasan->id,
                    'uploaded_by' => Auth::id(),
                    'jenis_dokumen' => 'dokumen',
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'ukuran_kb' => (int) ceil($file->getSize() / 1024),
                    'keterangan' => $validated['keterangan_dokumentasi'] ?? 'Dokumen Lampiran Pelaksanaan Kegiatan',
                ]);
            }
        }

        if ($statusLaporan === 'dikirim') {
            $penugasan->update(['status' => 'menunggu_verifikasi']);
            $penugasan->kegiatan()->where('status', 'terjadwal')->update(['status' => 'berjalan']);

            NotifikasiPegawai::query()->create([
                'user_id' => Auth::id(),
                'penugasan_id' => $penugasan->id,
                'kegiatan_id' => $penugasan->kegiatan_id,
                'pelaksanaan_id' => $pelaksanaan->id,
                'jenis_notifikasi' => 'laporan_diterima',
                'judul' => 'Laporan Diterima Sistem: '.$penugasan->kegiatan->nama_kegiatan,
                'pesan' => 'Laporan pelaksanaan '.$pelaksanaan->kode_laporan.' telah diterima dan sedang menunggu proses verifikasi.',
                'url_tujuan' => route('pelaksanaan.show', $pelaksanaan),
            ]);
        } else {
            $penugasan->where('status', 'belum_dikerjakan')->update(['status' => 'sedang_berjalan']);
        }

        return redirect()->route('pelaksanaan.show', $pelaksanaan)
            ->with('success', $statusLaporan === 'draft'
                ? 'Laporan pelaksanaan berhasil disimpan sebagai Draft.'
                : 'Laporan pelaksanaan berhasil dikirim dan masuk ke antrean verifikasi.');
    }

    /**
     * Halaman Detail Laporan Pelaksanaan & Panel Verifikasi (PRD 3.6).
     */
    public function show(Pelaksanaan $pelaksanaan): View
    {
        $pelaksanaan->load([
            'penugasan.kegiatan.rekening',
            'penugasan.pemberiTugas',
            'penugasan.penanggungJawab',
            'penugasan.anggotaPelaksana',
            'tupoksi.user',
            'pelapor',
            'verifikator',
            'dokumentasis.uploader',
        ]);

        return view('pelaksanaan.show', compact('pelaksanaan'));
    }

    /**
     * Perbaiki / Perbarui Laporan Draft atau Revisi (PRD 3.6).
     */
    public function update(Request $request, Pelaksanaan $pelaksanaan): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'uraian_hasil' => ['required', 'string'],
            'jumlah_target' => ['required', 'integer', 'min:1'],
            'jumlah_realisasi' => ['required', 'integer', 'min:0'],
            'satuan' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'catatan_pegawai' => ['nullable', 'string'],
            'aksi_simpan' => ['required', 'string', 'in:draft,kirim'],
        ]);

        $statusLaporan = $validated['aksi_simpan'] === 'draft' ? 'draft' : 'dikirim';

        $pelaksanaan->update([
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'uraian_hasil' => $validated['uraian_hasil'],
            'jumlah_target' => $validated['jumlah_target'],
            'jumlah_realisasi' => $validated['jumlah_realisasi'],
            'satuan' => $validated['satuan'],
            'keterangan' => $validated['keterangan'] ?? null,
            'catatan_pegawai' => $validated['catatan_pegawai'] ?? null,
            'status_laporan' => $statusLaporan,
            'status_sinkronisasi' => 'tersinkron',
            'synced_at' => now(),
        ]);

        if ($statusLaporan === 'dikirim') {
            $pelaksanaan->penugasan()->update(['status' => 'menunggu_verifikasi']);
        }

        return back()->with('success', $statusLaporan === 'draft'
            ? 'Perubahan draft laporan berhasil disimpan.'
            : 'Perbaikan laporan pelaksanaan berhasil dikirim ulang untuk diverifikasi.');
    }

    /**
     * Proses Verifikasi Laporan oleh Web Application (PRD 3.6, 3.7, 3.9).
     */
    public function verifikasi(Request $request, Pelaksanaan $pelaksanaan): RedirectResponse
    {
        $validated = $request->validate([
            'status_laporan' => ['required', 'string', 'in:diterima,perlu_perbaikan,disetujui'],
            'catatan_verifikasi' => ['nullable', 'string'],
        ]);

        $pelaksanaan->update([
            'status_laporan' => $validated['status_laporan'],
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $pelaksanaan->load(['penugasan.kegiatan', 'tupoksi']);
        $penugasan = $pelaksanaan->penugasan;

        if ($validated['status_laporan'] === 'disetujui') {
            $penugasan->update(['status' => 'selesai']);
            $penugasan->kegiatan()->update(['status' => 'selesai']);

            if ($pelaksanaan->tupoksi) {
                $pelaksanaan->tupoksi->recalculateRealisasi();
            }

            NotifikasiPegawai::query()->create([
                'user_id' => $pelaksanaan->pelapor_id,
                'penugasan_id' => $penugasan->id,
                'kegiatan_id' => $penugasan->kegiatan_id,
                'pelaksanaan_id' => $pelaksanaan->id,
                'jenis_notifikasi' => 'laporan_disetujui',
                'judul' => 'Laporan Disetujui: '.$penugasan->kegiatan->nama_kegiatan,
                'pesan' => 'Laporan pelaksanaan '.$pelaksanaan->kode_laporan.' telah disetujui dan capaian Tupoksi Anda otomatis diperbarui.',
                'url_tujuan' => route('pelaksanaan.show', $pelaksanaan),
            ]);
        } elseif ($validated['status_laporan'] === 'perlu_perbaikan') {
            $penugasan->update(['status' => 'perlu_perbaikan']);

            if ($pelaksanaan->tupoksi) {
                $pelaksanaan->tupoksi->recalculateRealisasi();
            }

            NotifikasiPegawai::query()->create([
                'user_id' => $pelaksanaan->pelapor_id,
                'penugasan_id' => $penugasan->id,
                'kegiatan_id' => $penugasan->kegiatan_id,
                'pelaksanaan_id' => $pelaksanaan->id,
                'jenis_notifikasi' => 'laporan_perlu_diperbaiki',
                'judul' => 'Laporan Perlu Diperbaiki: '.$penugasan->kegiatan->nama_kegiatan,
                'pesan' => 'Catatan Verifikator: '.($validated['catatan_verifikasi'] ?: 'Mohon lengkapi hasil dan dokumentasi pelaksanaan.'),
                'url_tujuan' => route('pelaksanaan.show', $pelaksanaan),
            ]);
        } else {
            $penugasan->update(['status' => 'menunggu_verifikasi']);

            NotifikasiPegawai::query()->create([
                'user_id' => $pelaksanaan->pelapor_id,
                'penugasan_id' => $penugasan->id,
                'kegiatan_id' => $penugasan->kegiatan_id,
                'pelaksanaan_id' => $pelaksanaan->id,
                'jenis_notifikasi' => 'laporan_diterima',
                'judul' => 'Laporan Diterima Verifikator: '.$penugasan->kegiatan->nama_kegiatan,
                'pesan' => 'Laporan '.$pelaksanaan->kode_laporan.' telah diterima dan sedang direview oleh verifikator.',
                'url_tujuan' => route('pelaksanaan.show', $pelaksanaan),
            ]);
        }

        return back()->with('success', 'Status verifikasi laporan berhasil diperbarui menjadi '.strtoupper(str_replace('_', ' ', $validated['status_laporan'])).'.');
    }

    /**
     * Upload dokumentasi tambahan pada halaman detail pelaksanaan.
     */
    public function uploadDokumentasi(Request $request, Pelaksanaan $pelaksanaan): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_dokumen' => ['required', 'string', 'in:foto,dokumen,lampiran_pendukung'],
            'file_dokumentasi' => ['required', 'file', 'max:10240'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file_dokumentasi');
        $folder = $validated['jenis_dokumen'] === 'foto' ? 'dokumentasi/foto' : 'dokumentasi/dokumen';
        $path = $file->store($folder, 'public');

        DokumentasiKegiatan::query()->create([
            'pelaksanaan_id' => $pelaksanaan->id,
            'penugasan_id' => $pelaksanaan->penugasan_id,
            'uploaded_by' => Auth::id(),
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'nama_file' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'ukuran_kb' => (int) ceil($file->getSize() / 1024),
            'keterangan' => $validated['keterangan'] ?? $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Dokumentasi kegiatan berhasil diunggah.');
    }
}
