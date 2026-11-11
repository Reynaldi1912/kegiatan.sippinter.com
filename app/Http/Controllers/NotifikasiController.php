<?php

namespace App\Http\Controllers;

use App\Models\NotifikasiPegawai;
use App\Models\Penugasan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    /**
     * Halaman Notifikasi (PRD 3.9).
     */
    public function index(Request $request): View
    {
        $jenis = (string) $request->query('jenis', '');
        $statusBaca = (string) $request->query('status', '');

        $query = NotifikasiPegawai::query()
            ->with(['user', 'penugasan.kegiatan', 'kegiatan', 'pelaksanaan'])
            ->where('user_id', Auth::id());

        if ($jenis !== '') {
            $query->where('jenis_notifikasi', $jenis);
        }

        if ($statusBaca === 'unread') {
            $query->where('is_read', false);
        } elseif ($statusBaca === 'read') {
            $query->where('is_read', true);
        }

        $notifikasis = $query->latest()->get();

        $unreadCount = NotifikasiPegawai::query()
            ->where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return view('notifikasi.index', compact('notifikasis', 'jenis', 'statusBaca', 'unreadCount'));
    }

    /**
     * Tindakan: Buka Detail & Tandai Sudah Dibaca (PRD 3.9).
     */
    public function markReadAndOpen(NotifikasiPegawai $notifikasi): RedirectResponse
    {
        if ($notifikasi->user_id === Auth::id() && ! $notifikasi->is_read) {
            $notifikasi->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if ($notifikasi->url_tujuan) {
            return redirect($notifikasi->url_tujuan);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    /**
     * Tindakan: Tandai Semua Sudah Dibaca (PRD 3.9).
     */
    public function markAllRead(): RedirectResponse
    {
        NotifikasiPegawai::query()
            ->where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    /**
     * Kirim Pengingat Tenggat Waktu ke seluruh penugasan aktif.
     */
    public function kirimPengingatTenggat(): RedirectResponse
    {
        $penugasans = Penugasan::query()
            ->with(['kegiatan', 'anggotaPelaksana'])
            ->whereIn('status', ['belum_dikerjakan', 'sedang_berjalan', 'perlu_perbaikan'])
            ->get();

        $sentCount = 0;

        foreach ($penugasans as $penugasan) {
            $recipients = $penugasan->anggotaPelaksana->pluck('id')
                ->push($penugasan->penanggung_jawab_id)
                ->unique();

            foreach ($recipients as $userId) {
                NotifikasiPegawai::query()->create([
                    'user_id' => $userId,
                    'penugasan_id' => $penugasan->id,
                    'kegiatan_id' => $penugasan->kegiatan_id,
                    'jenis_notifikasi' => 'pengingat_tenggat',
                    'judul' => 'Pengingat Tenggat Waktu: '.$penugasan->kegiatan->nama_kegiatan,
                    'pesan' => 'Penugasan '.$penugasan->nomor_surat_tugas.' memiliki tenggat waktu pada '.$penugasan->tenggat_waktu->translatedFormat('d M Y').'. Segera laporkan hasil pelaksanaan.',
                    'url_tujuan' => route('penugasan.show', $penugasan),
                ]);
                $sentCount++;
            }
        }

        return back()->with('success', $sentCount.' notifikasi pengingat tenggat waktu berhasil dikirim ke pegawai.');
    }
}
