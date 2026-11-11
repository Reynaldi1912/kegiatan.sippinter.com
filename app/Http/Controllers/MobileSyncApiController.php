<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLine;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\Kegiatan;
use App\Models\NotifikasiPegawai;
use App\Models\Pelaksanaan;
use App\Models\Penugasan;
use App\Models\Tupoksi;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MobileSyncApiController extends Controller
{
    /**
     * Resolusi akun pengguna dari request Mobile App (`sippinter_mobile`).
     * Mendukung header `X-User-Id`, query/body `user_id`/`username`, Bearer token, dan session web.
     */
    private function resolveMobileUser(Request $request): ?User
    {
        $headerUserId = $request->header('X-User-Id') ?: $request->input('user_id') ?: $request->query('user_id');
        if ($headerUserId && is_numeric($headerUserId)) {
            $byId = User::query()->find((int) $headerUserId);
            if ($byId) {
                return $byId;
            }
        }

        $usernameOrNip = $request->header('X-Username') ?: $request->input('username') ?: $request->query('username') ?: $request->query('nip');
        if ($usernameOrNip) {
            $byIdent = User::query()
                ->where('username', $usernameOrNip)
                ->orWhere('nip', $usernameOrNip)
                ->orWhere('email', $usernameOrNip)
                ->first();
            if ($byIdent) {
                return $byIdent;
            }
        }

        $token = $request->bearerToken() ?: (string) ($request->input('token') ?: $request->query('token', ''));
        if ($token !== '') {
            $byToken = User::query()->where('api_token', $token)->first();
            if ($byToken) {
                return $byToken;
            }
        }

        return Auth::user() ?: User::query()->where('is_active', true)->first() ?: User::query()->first();
    }

    /**
     * Halaman Monitoring Sinkronisasi Web <-> Mobile (PRD Bab 4).
     */
    public function index(): View
    {
        $pelaksanaanSyncs = Pelaksanaan::query()
            ->with(['penugasan.kegiatan', 'pelapor', 'dokumentasis'])
            ->latest()
            ->limit(15)
            ->get();

        $syncStats = [
            'total_akun_aktif' => User::query()->where('is_active', true)->count(),
            'total_penugasan_aktif' => Penugasan::query()->count(),
            'total_laporan_tersinkron' => Pelaksanaan::query()->where('status_sinkronisasi', 'tersinkron')->count(),
            'laporan_mobile' => Pelaksanaan::query()->where('sumber_input', 'mobile_sync')->count(),
            'gagal_sinkron' => Pelaksanaan::query()->where('status_sinkronisasi', 'gagal_sinkron')->count(),
        ];

        return view('sinkronisasi.index', compact('pelaksanaanSyncs', 'syncStats'));
    }

    /**
     * Simulasi / Kirim Ulang (Retry) Sinkronisasi dari Web Dashboard (PRD 4.3).
     */
    public function retrySync(Pelaksanaan $pelaksanaan): RedirectResponse
    {
        $pelaksanaan->update([
            'status_sinkronisasi' => 'tersinkron',
            'synced_at' => now(),
        ]);

        return back()->with('success', 'Data laporan '.$pelaksanaan->kode_laporan.' berhasil disinkronkan ulang.');
    }

    /**
     * API Endpoint Login Mobile (PRD 3.2 & 6).
     */
    public function apiLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->orWhere('nip', $validated['login'])
            ->first();

        if (! $user || ! $user->verifyPassword($validated['password']) || $user->is_active === false) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial tidak valid atau akun tidak aktif.',
            ], 401);
        }

        if (! $user->api_token) {
            $user->update(['api_token' => 'sipp-mob-'.Str::random(32)]);
        }

        return response()->json([
            'success' => true,
            'token' => $user->api_token,
            'profil' => [
                'id' => $user->id,
                'nama' => $user->name,
                'username' => $user->username,
                'nip' => $user->nip,
                'jabatan' => $user->jabatan,
                'unit_program' => $user->unit_program,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * API Endpoint 4.1: Sinkronisasi Data Kegiatan, Penugasan, Tupoksi & Approval dari Web ke Mobile (`sippinter_mobile`).
     */
    public function apiPullBootstrap(Request $request): JsonResponse
    {
        $user = $this->resolveMobileUser($request);

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Ambil penugasan spesifik milik pegawai yang login
        $myPenugasans = Penugasan::query()
            ->with([
                'kegiatan',
                'tupoksi',
                'pemberiTugas:id,fullname,username,jabatan',
                'penanggungJawab:id,fullname,username,jabatan',
                'anggotaPelaksana:id,fullname,username,jabatan',
                'pelaksanaans' => fn ($q) => $q->latest()->limit(3),
            ])
            ->where(function ($q) use ($user) {
                $q->where('penanggung_jawab_id', $user->id)
                    ->orWhereHas('anggotaPelaksana', fn ($sub) => $sub->where('users.id', $user->id));
            })
            ->latest()
            ->get()
            ->map(function (Penugasan $item) {
                $arr = $item->toArray();
                $arr['is_my_assignment'] = true;

                return $arr;
            });

        // Ambil seluruh penugasan Puskesmas sebagai referensi tim / bila pegawai belum memiliki penugasan langsung
        $allPenugasans = Penugasan::query()
            ->with([
                'kegiatan',
                'tupoksi',
                'pemberiTugas:id,fullname,username,jabatan',
                'penanggungJawab:id,fullname,username,jabatan',
                'anggotaPelaksana:id,fullname,username,jabatan',
                'pelaksanaans' => fn ($q) => $q->latest()->limit(3),
            ])
            ->latest()
            ->limit(30)
            ->get()
            ->map(function (Penugasan $item) use ($user) {
                $isMine = (int) $item->penanggung_jawab_id === (int) $user->id
                    || $item->anggotaPelaksana->contains('id', $user->id);
                $arr = $item->toArray();
                $arr['is_my_assignment'] = $isMine;

                return $arr;
            });

        $penugasans = $myPenugasans->isNotEmpty() ? $myPenugasans : $allPenugasans;

        $jadwals = Kegiatan::query()
            ->with('penanggungJawab:id,fullname,username,jabatan')
            ->withCount('penugasans')
            ->orderBy('tanggal_mulai')
            ->get();

        $myTupoksis = Tupoksi::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $tupoksis = $myTupoksis->isNotEmpty()
            ? $myTupoksis
            : Tupoksi::query()->with('pegawai:id,fullname,username,jabatan')->where('is_active', true)->limit(20)->get();

        $pelaksanaans = Pelaksanaan::query()
            ->with(['kegiatan:id,kode_kegiatan,nama_kegiatan,lokasi', 'penugasan:id,kode_penugasan,judul_penugasan', 'pelapor:id,fullname,username,jabatan'])
            ->latest()
            ->limit(20)
            ->get();

        $notifikasis = NotifikasiPegawai::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(30)
            ->get();

        $approvalLines = ApprovalLine::query()
            ->with('approverUser:id,fullname,username,jabatan,role')
            ->where('is_active', true)
            ->where('allow_mobile', true)
            ->orderBy('modul')
            ->orderBy('step_order')
            ->get();

        $approvalRequests = ApprovalRequest::query()
            ->with([
                'kegiatan:id,kode_kegiatan,nama_kegiatan,pagu_anggaran,realisasi_anggaran',
                'pengaju:id,fullname,username,jabatan,role',
                'steps.assignedApprover:id,fullname,username,jabatan,role',
                'steps.actor:id,fullname,username,jabatan,role',
            ])
            ->latest()
            ->limit(30)
            ->get();

        $pendingApprovalsCount = ApprovalRequest::query()
            ->whereIn('status', ['menunggu', 'dalam_proses'])
            ->count();

        return response()->json([
            'success' => true,
            'synced_at' => now()->toIso8601String(),
            'data' => [
                'profil' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'fullname' => $user->fullname ?: $user->name,
                    'username' => $user->username,
                    'nip' => $user->nip,
                    'jabatan' => $user->jabatan,
                    'unit_program' => $user->unit_program,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
                'counts' => [
                    'my_penugasan_count' => $myPenugasans->count(),
                    'all_penugasan_count' => $allPenugasans->count(),
                    'pending_penugasan_count' => $penugasans->whereIn('status', ['belum_dikerjakan', 'sedang_dikerjakan', 'sedang_berjalan', 'perlu_perbaikan'])->count(),
                    'jadwal_count' => $jadwals->count(),
                    'tupoksi_count' => $tupoksis->count(),
                    'pending_approvals_count' => $pendingApprovalsCount,
                ],
                'penugasan' => $penugasans->values(),
                'all_penugasan' => $allPenugasans->values(),
                'jadwal' => $jadwals,
                'tupoksi' => $tupoksis,
                'pelaksanaan' => $pelaksanaans,
                'notifikasi' => $notifikasis,
                'approval_lines' => $approvalLines,
                'approval_requests' => $approvalRequests,
            ],
        ]);
    }

    /**
     * API Endpoint Mobile: Mulai Kerjakan Penugasan dari Aplikasi Mobile.
     */
    public function apiMulaiPenugasan(Request $request, Penugasan $penugasan): JsonResponse
    {
        $penugasan->update([
            'status' => 'sedang_dikerjakan',
        ]);

        if ($penugasan->kegiatan && $penugasan->kegiatan->status === 'terjadwal') {
            $penugasan->kegiatan->update(['status' => 'sedang_berjalan']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status penugasan diperbarui menjadi Sedang Dikerjakan.',
            'data' => $penugasan->fresh(['kegiatan', 'tupoksi', 'penanggungJawab:id,fullname,username,jabatan']),
        ]);
    }

    /**
     * API Endpoint Mobile: Daftar Pengajuan Approval Dana & Anggaran beserta Aturan Approval Line per Modul.
     */
    public function apiListApprovals(Request $request): JsonResponse
    {
        $modul = $request->query('modul');
        $status = $request->query('status');

        $rules = ApprovalLine::query()
            ->with('approverUser:id,fullname,username,jabatan,role')
            ->where('is_active', true)
            ->when($modul, fn ($q) => $q->where('modul', $modul))
            ->orderBy('modul')
            ->orderBy('step_order')
            ->get();

        $allRulesByModule = ApprovalLine::query()
            ->with('approverUser:id,fullname,username,jabatan,role')
            ->where('is_active', true)
            ->orderBy('step_order')
            ->get()
            ->groupBy('modul');

        $requests = ApprovalRequest::query()
            ->with([
                'kegiatan:id,kode_kegiatan,nama_kegiatan,pagu_anggaran,realisasi_anggaran',
                'pengaju:id,fullname,username,jabatan,role',
                'steps.assignedApprover:id,fullname,username,jabatan,role',
                'steps.actor:id,fullname,username,jabatan,role',
            ])
            ->when($modul, fn ($q) => $q->where('modul', $modul))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $moduleCatalog = [
            [
                'kode' => 'pencairan_dana',
                'nama_modul' => 'Pencairan Dana Kegiatan (BOK & BLUD)',
                'deskripsi' => 'Persetujuan pencairan dana kegiatan Puskesmas, uang transport petugas, dan belanja bahan operasional.',
                'tabel_utama' => 'pencairan_danas & approval_requests',
                'rules' => $allRulesByModule->get('pencairan_dana', collect())->values(),
                'requests_count' => ApprovalRequest::query()->where('modul', 'pencairan_dana')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'pencairan_dana')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
            [
                'kode' => 'kegiatan_rpk',
                'nama_modul' => 'Perencanaan RUK / RPK & Budget Plan BOK',
                'deskripsi' => 'Persetujuan dokumen RUK/RPK, rincian menu komponen BOK, kode rekening pembiayaan, dan pagu anggaran.',
                'tabel_utama' => 'budget_plans, budget_stages & kegiatans',
                'rules' => $allRulesByModule->get('kegiatan_rpk', collect())->values(),
                'requests_count' => ApprovalRequest::query()->where('modul', 'kegiatan_rpk')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'kegiatan_rpk')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
            [
                'kode' => 'pelaksanaan_spj',
                'nama_modul' => 'SPJ BOK, Kuitansi & Laporan Pelaksanaan',
                'deskripsi' => 'Verifikasi kuitansi SPJ BOK, rincian transport peserta, dan laporan pelaksanaan lapangan.',
                'tabel_utama' => 'bok_spjs, bok_spj_details & pelaksanaans',
                'rules' => $allRulesByModule->get('pelaksanaan_spj', collect())->values(),
                'requests_count' => ApprovalRequest::query()->where('modul', 'pelaksanaan_spj')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'pelaksanaan_spj')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
            [
                'kode' => 'penugasan',
                'nama_modul' => 'Surat Tugas & Aktivitas Lapangan BOK',
                'deskripsi' => 'Persetujuan surat tugas pegawai, titik lokasi kegiatan BOK, dan peserta kegiatan lapangan.',
                'tabel_utama' => 'penugasans, bok_activities & bok_participants',
                'rules' => $allRulesByModule->get('penugasan', collect())->values(),
                'requests_count' => ApprovalRequest::query()->where('modul', 'penugasan')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'penugasan')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
        ];

        $summary = [
            'total_aturan' => ApprovalLine::query()->where('is_active', true)->count(),
            'total_pengajuan' => ApprovalRequest::query()->count(),
            'menunggu' => ApprovalRequest::query()->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            'disetujui' => ApprovalRequest::query()->where('status', 'disetujui')->count(),
            'ditolak_revisi' => ApprovalRequest::query()->whereIn('status', ['ditolak', 'revisi'])->count(),
            'total_nominal_diajukan' => (float) ApprovalRequest::query()->sum('nominal_diajukan'),
            'total_nominal_disetujui' => (float) ApprovalRequest::query()->where('status', 'disetujui')->sum('nominal_disetujui'),
        ];

        $kegiatans = Kegiatan::query()
            ->orderBy('nama_kegiatan')
            ->get(['id', 'kode_kegiatan', 'nama_kegiatan', 'sumber_anggaran', 'pagu_anggaran', 'realisasi_anggaran']);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'module_catalog' => $moduleCatalog,
            'approval_lines' => $rules,
            'approval_requests' => $requests,
            'kegiatans' => $kegiatans,
        ]);
    }

    /**
     * API Endpoint Mobile: Ajukan Persetujuan Dana / Anggaran / SPJ Baru dari Aplikasi Mobile.
     */
    public function apiCreateApprovalRequest(Request $request): JsonResponse
    {
        $user = $this->resolveMobileUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'judul_pengajuan' => ['required', 'string', 'max:255'],
            'modul' => ['required', 'string', 'in:pencairan_dana,kegiatan_rpk,pelaksanaan_spj,penugasan'],
            'kegiatan_id' => ['nullable', 'exists:kegiatans,id'],
            'penugasan_id' => ['nullable', 'exists:penugasans,id'],
            'pelaksanaan_id' => ['nullable', 'exists:pelaksanaans,id'],
            'nominal_diajukan' => ['required', 'numeric', 'min:0'],
            'sumber_dana' => ['required', 'string', 'max:100'],
            'kode_rekening' => ['nullable', 'string', 'max:50'],
            'uraian_pengajuan' => ['required', 'string'],
        ]);

        $nominal = (float) $validated['nominal_diajukan'];

        $matchingRules = ApprovalLine::query()
            ->where('modul', $validated['modul'])
            ->where('is_active', true)
            ->where('nominal_minimal', '<=', $nominal)
            ->where(function ($q) use ($nominal) {
                $q->whereNull('nominal_maksimal')
                    ->orWhere('nominal_maksimal', '>=', $nominal);
            })
            ->orderBy('step_order')
            ->get();

        if ($matchingRules->isEmpty()) {
            $matchingRules = ApprovalLine::query()
                ->where('modul', $validated['modul'])
                ->where('is_active', true)
                ->orderBy('step_order')
                ->get();
        }

        $createdRequest = DB::transaction(function () use ($validated, $user, $nominal, $matchingRules) {
            $totalSteps = max(1, $matchingRules->count());
            $nomor = 'APR-MOB-'.now()->format('Ym').'-'.str_pad((string) (ApprovalRequest::query()->count() + 1), 4, '0', STR_PAD_LEFT);

            $pencairanDanaId = null;
            if ($validated['modul'] === 'pencairan_dana' && Schema::hasTable('pencairan_danas')) {
                $firstApproverId = $matchingRules->first()?->approver_user_id ?? $user->id;
                $pencairanDanaId = DB::table('pencairan_danas')->insertGetId([
                    'user_id' => $firstApproverId,
                    'file_pencairan_dana' => json_encode([
                        'nomor_pengajuan' => $nomor,
                        'nominal_diajukan' => $nominal,
                        'sumber_dana' => $validated['sumber_dana'],
                        'kode_rekening' => $validated['kode_rekening'] ?? null,
                    ]),
                    'tanggal_kirim' => now(),
                    'di_read_oleh_orang_nya' => 0,
                    'approve_oleh_orang_nya' => null,
                    'status' => 'pending',
                    'additional_text' => $validated['judul_pengajuan'].' — '.$validated['uraian_pengajuan'],
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $approvalRequest = ApprovalRequest::query()->create([
                'nomor_pengajuan' => $nomor,
                'judul_pengajuan' => $validated['judul_pengajuan'],
                'modul' => $validated['modul'],
                'referensi_tipe' => $pencairanDanaId ? 'pencairan_danas' : (! empty($validated['kegiatan_id']) ? Kegiatan::class : null),
                'referensi_id' => $pencairanDanaId ?: ($validated['kegiatan_id'] ?? null),
                'kegiatan_id' => $validated['kegiatan_id'] ?? null,
                'penugasan_id' => $validated['penugasan_id'] ?? null,
                'pelaksanaan_id' => $validated['pelaksanaan_id'] ?? null,
                'pengaju_id' => $user->id,
                'nominal_diajukan' => $nominal,
                'sumber_dana' => $validated['sumber_dana'],
                'kode_rekening' => $validated['kode_rekening'] ?? null,
                'uraian_pengajuan' => $validated['uraian_pengajuan'],
                'current_step' => 1,
                'total_steps' => $totalSteps,
                'status' => 'menunggu',
                'sumber_pengajuan' => 'mobile',
                'submitted_at' => now(),
            ]);

            if ($matchingRules->isNotEmpty()) {
                foreach ($matchingRules->values() as $idx => $rule) {
                    $stepOrder = $idx + 1;
                    ApprovalRequestStep::query()->create([
                        'approval_request_id' => $approvalRequest->id,
                        'approval_line_id' => $rule->id,
                        'step_order' => $stepOrder,
                        'nama_level' => $rule->nama_level,
                        'approver_role' => $rule->approver_role,
                        'assigned_approver_id' => $rule->approver_user_id,
                        'status' => 'menunggu',
                    ]);
                }
            } else {
                ApprovalRequestStep::query()->create([
                    'approval_request_id' => $approvalRequest->id,
                    'step_order' => 1,
                    'nama_level' => 'Persetujuan Kepala Puskesmas / KPA',
                    'approver_role' => 'kepala_puskesmas',
                    'status' => 'menunggu',
                ]);
            }

            return $approvalRequest->load([
                'kegiatan:id,kode_kegiatan,nama_kegiatan',
                'pengaju:id,fullname,username,jabatan,role',
                'steps.assignedApprover:id,fullname,username,jabatan,role',
                'steps.actor:id,fullname,username,jabatan,role',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan persetujuan dana berhasil dikirim melalui Mobile App.',
            'data' => $createdRequest,
        ], 201);
    }

    /**
     * API Endpoint Mobile: Proses Approve / Tolak / Revisi Pengajuan Dana dari Aplikasi Mobile.
     */
    public function apiActOnApproval(Request $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $actor = $this->resolveMobileUser($request);

        if (! $actor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'keputusan' => ['required', 'in:disetujui,ditolak,revisi'],
            'nominal_disetujui' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ]);

        $updated = ApprovalLineController::executeApprovalDecision(
            $approvalRequest,
            $actor,
            $validated['keputusan'],
            $validated['catatan'] ?? null,
            isset($validated['nominal_disetujui']) ? (float) $validated['nominal_disetujui'] : null,
            'mobile'
        );

        return response()->json([
            'success' => true,
            'message' => 'Keputusan approval berhasil diproses melalui Mobile API.',
            'data' => $updated->load([
                'kegiatan:id,kode_kegiatan,nama_kegiatan,pagu_anggaran,realisasi_anggaran',
                'pengaju:id,fullname,username,jabatan,role',
                'steps.assignedApprover:id,fullname,username,jabatan,role',
                'steps.actor:id,fullname,username,jabatan,role',
            ]),
        ]);
    }

    /**
     * API Endpoint 4.2 & 4.3: Sinkronisasi Data Pelaksanaan dari Mobile ke Web (dengan anti-duplikasi).
     */
    public function apiPushPelaksanaan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_sync_id' => ['required', 'string', 'max:100'],
            'penugasan_id' => ['required', 'exists:penugasans,id'],
            'pelapor_id' => ['nullable', 'exists:users,id'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'uraian_hasil' => ['required', 'string'],
            'jumlah_target' => ['required', 'integer', 'min:1'],
            'jumlah_realisasi' => ['required', 'integer', 'min:0'],
            'satuan' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'catatan_pegawai' => ['nullable', 'string'],
            'status_laporan' => ['nullable', 'string', 'in:draft,dikirim'],
        ]);

        // Mencegah duplikasi laporan (PRD 4.3 & 6)
        $existing = Pelaksanaan::query()->where('client_sync_id', $validated['client_sync_id'])->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'duplicate_prevented' => true,
                'message' => 'Laporan sudah tersinkronisasi sebelumnya.',
                'kode_laporan' => $existing->kode_laporan,
                'status_sinkronisasi' => $existing->status_sinkronisasi,
            ]);
        }

        /** @var Penugasan $penugasan */
        $penugasan = Penugasan::query()->with('kegiatan')->findOrFail($validated['penugasan_id']);
        $mobileUser = $this->resolveMobileUser($request);
        $pelaporId = $validated['pelapor_id'] ?? $mobileUser?->id ?? $penugasan->penanggung_jawab_id;
        $statusLaporan = $validated['status_laporan'] ?? 'dikirim';

        $nextNumber = Pelaksanaan::query()->count() + 1;
        $pelaksanaan = Pelaksanaan::query()->create([
            'kode_laporan' => sprintf('LPR-MOB-%s-%04d', now()->format('Ym'), $nextNumber),
            'client_sync_id' => $validated['client_sync_id'],
            'penugasan_id' => $penugasan->id,
            'kegiatan_id' => $penugasan->kegiatan_id,
            'tupoksi_id' => $penugasan->tupoksi_id,
            'pelapor_id' => $pelaporId,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'uraian_hasil' => $validated['uraian_hasil'],
            'jumlah_target' => $validated['jumlah_target'],
            'jumlah_realisasi' => $validated['jumlah_realisasi'],
            'satuan' => $validated['satuan'],
            'keterangan' => $validated['keterangan'] ?? null,
            'catatan_pegawai' => $validated['catatan_pegawai'] ?? null,
            'status_laporan' => $statusLaporan,
            'sumber_input' => 'mobile_sync',
            'status_sinkronisasi' => 'tersinkron',
            'synced_at' => now(),
        ]);

        if ($statusLaporan === 'dikirim') {
            $penugasan->update(['status' => 'menunggu_verifikasi']);
        }

        return response()->json([
            'success' => true,
            'duplicate_prevented' => false,
            'message' => 'Laporan pelaksanaan berhasil disinkronkan ke Web Application.',
            'kode_laporan' => $pelaksanaan->kode_laporan,
            'status_sinkronisasi' => $pelaksanaan->status_sinkronisasi,
            'data' => $pelaksanaan->load(['kegiatan:id,kode_kegiatan,nama_kegiatan', 'penugasan:id,kode_penugasan,judul_penugasan']),
        ], 201);
    }

    /**
     * Endpoint untuk Sidebar Badge Counts (`fetch('/api/sidebar-counts')` di app.blade.php).
     */
    public function sidebarCounts(): JsonResponse
    {
        $userId = Auth::id();

        return response()->json([
            'success' => true,
            'counts' => [
                'penugasan_pending' => Penugasan::query()->whereIn('status', ['belum_dikerjakan', 'sedang_dikerjakan', 'sedang_berjalan', 'perlu_perbaikan'])->count(),
                'verifikasi_pending' => Pelaksanaan::query()->whereIn('status_laporan', ['dikirim', 'diterima'])->count(),
                'notifikasi_unread' => $userId
                    ? NotifikasiPegawai::query()->where('user_id', $userId)->where('is_read', false)->count()
                    : 0,
            ],
        ]);
    }
}
