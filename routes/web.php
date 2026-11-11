<?php

use App\Http\Controllers\ApprovalLineController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\MobileSyncApiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PegawaiProfileController;
use App\Http\Controllers\PelaksanaanController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\TupoksiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// API Endpoints untuk Sinkronisasi Mobile <-> Web (PRD Bab 4 & sippinter_mobile)
Route::prefix('api')->group(function () {
    Route::get('/sidebar-counts', [MobileSyncApiController::class, 'sidebarCounts'])->name('api.sidebar-counts');
    Route::post('/mobile/login', [MobileSyncApiController::class, 'apiLogin'])->name('api.mobile.login');
    Route::get('/mobile/bootstrap', [MobileSyncApiController::class, 'apiPullBootstrap'])->name('api.mobile.bootstrap');
    Route::post('/mobile/penugasan/{penugasan}/mulai', [MobileSyncApiController::class, 'apiMulaiPenugasan'])->name('api.mobile.penugasan.mulai');
    Route::post('/mobile/pelaksanaan/sync', [MobileSyncApiController::class, 'apiPushPelaksanaan'])->name('api.mobile.pelaksanaan.sync');
    Route::get('/mobile/approvals', [MobileSyncApiController::class, 'apiListApprovals'])->name('api.mobile.approvals.index');
    Route::post('/mobile/approvals/request', [MobileSyncApiController::class, 'apiCreateApprovalRequest'])->name('api.mobile.approvals.store');
    Route::post('/mobile/approvals/{approvalRequest}/action', [MobileSyncApiController::class, 'apiActOnApproval'])->name('api.mobile.approvals.action');
});

Route::middleware('auth')->group(function () {
    // 1. Dashboard / Beranda (PRD 3.3)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/system/clear-cache', [AuthController::class, 'clearCache'])->name('system.clear.cache');

    // 2. Perencanaan RUK / RPK / RBA & Jadwal Kegiatan (PRD 3.4 & 8)
    Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
    Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
    Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
    Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');

    Route::get('/jadwal', [KegiatanController::class, 'jadwal'])->name('jadwal.index');
    Route::get('/jadwal/{kegiatan}', [KegiatanController::class, 'show'])->name('jadwal.show');

    // 3. Penugasan & Disposisi Kegiatan (PRD 3.5)
    Route::get('/penugasan', [PenugasanController::class, 'index'])->name('penugasan.index');
    Route::post('/penugasan', [PenugasanController::class, 'store'])->name('penugasan.store');
    Route::get('/penugasan/{penugasan}', [PenugasanController::class, 'show'])->name('penugasan.show');
    Route::put('/penugasan/{penugasan}', [PenugasanController::class, 'update'])->name('penugasan.update');
    Route::post('/penugasan/{penugasan}/mulai', [PenugasanController::class, 'mulaiPelaksanaan'])->name('penugasan.mulai');
    Route::delete('/penugasan/{penugasan}', [PenugasanController::class, 'destroy'])->name('penugasan.destroy');

    // 4. Pelaksanaan Kegiatan, Dokumentasi & Verifikasi Laporan (PRD 3.6)
    Route::get('/pelaksanaan', [PelaksanaanController::class, 'index'])->name('pelaksanaan.index');
    Route::get('/pelaksanaan/lapor', [PelaksanaanController::class, 'create'])->name('pelaksanaan.create');
    Route::post('/pelaksanaan', [PelaksanaanController::class, 'store'])->name('pelaksanaan.store');
    Route::get('/pelaksanaan/{pelaksanaan}', [PelaksanaanController::class, 'show'])->name('pelaksanaan.show');
    Route::put('/pelaksanaan/{pelaksanaan}', [PelaksanaanController::class, 'update'])->name('pelaksanaan.update');
    Route::post('/pelaksanaan/{pelaksanaan}/verifikasi', [PelaksanaanController::class, 'verifikasi'])->name('pelaksanaan.verifikasi');
    Route::post('/pelaksanaan/{pelaksanaan}/dokumentasi', [PelaksanaanController::class, 'uploadDokumentasi'])->name('pelaksanaan.dokumentasi');

    // 5. Capaian & Master Tupoksi Pegawai (PRD 3.7)
    Route::get('/tupoksi', [TupoksiController::class, 'index'])->name('tupoksi.index');
    Route::post('/tupoksi', [TupoksiController::class, 'store'])->name('tupoksi.store');
    Route::get('/tupoksi/{tupoksi}', [TupoksiController::class, 'show'])->name('tupoksi.show');
    Route::put('/tupoksi/{tupoksi}', [TupoksiController::class, 'update'])->name('tupoksi.update');

    // 6. Riwayat Kegiatan (PRD 3.8)
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat/{pelaksanaan}', [RiwayatController::class, 'show'])->name('riwayat.show');

    // 7. Notifikasi Pegawai (PRD 3.9)
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/{notifikasi}/buka', [NotifikasiController::class, 'markReadAndOpen'])->name('notifikasi.buka');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'markAllRead'])->name('notifikasi.mark-all-read');
    Route::post('/notifikasi/pengingat-tenggat', [NotifikasiController::class, 'kirimPengingatTenggat'])->name('notifikasi.pengingat-tenggat');

    // 8. Profil Pegawai & Manajemen Kepegawaian (PRD 3.10)
    Route::get('/profil', [PegawaiProfileController::class, 'profile'])->name('profile.edit');
    Route::put('/profil', [PegawaiProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profil/password', [PegawaiProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/token', [PegawaiProfileController::class, 'regenerateToken'])->name('profile.token');

    Route::get('/pegawai', [PegawaiProfileController::class, 'pegawaiIndex'])->name('pegawai.index');
    Route::post('/pegawai', [PegawaiProfileController::class, 'pegawaiStore'])->name('pegawai.store');
    Route::put('/pegawai/{pegawai}', [PegawaiProfileController::class, 'pegawaiUpdate'])->name('pegawai.update');

    // 9. Pusat Sinkronisasi Data Web <-> Mobile (PRD Bab 4)
    Route::get('/sinkronisasi', [MobileSyncApiController::class, 'index'])->name('sinkronisasi.index');
    Route::post('/sinkronisasi/{pelaksanaan}/retry', [MobileSyncApiController::class, 'retrySync'])->name('sinkronisasi.retry');

    // 10. Aturan Approval Line & Persetujuan Dana / Anggaran (Web & Mobile)
    Route::get('/approval-line', [ApprovalLineController::class, 'index'])->name('approval.index');
    Route::post('/approval-line/rules', [ApprovalLineController::class, 'storeRule'])->name('approval.rules.store');
    Route::put('/approval-line/rules/{approvalLine}', [ApprovalLineController::class, 'updateRule'])->name('approval.rules.update');
    Route::delete('/approval-line/rules/{approvalLine}', [ApprovalLineController::class, 'destroyRule'])->name('approval.rules.destroy');
    Route::post('/approval-line/requests', [ApprovalLineController::class, 'storeRequest'])->name('approval.requests.store');
    Route::post('/approval-line/requests/{approvalRequest}/action', [ApprovalLineController::class, 'processAction'])->name('approval.requests.action');
});
