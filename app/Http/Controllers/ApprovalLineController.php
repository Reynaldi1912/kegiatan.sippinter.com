<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLine;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\ChartOfAccount;
use App\Models\Kegiatan;
use App\Models\NotifikasiPegawai;
use App\Models\Pelaksanaan;
use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ApprovalLineController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $modulFilter = $request->query('modul');
        $statusFilter = $request->query('status');
        $activeTab = $request->query('tab', 'modul');

        $approvalLines = ApprovalLine::query()
            ->with('approverUser')
            ->when($modulFilter, fn ($q) => $q->where('modul', $modulFilter))
            ->orderBy('modul')
            ->orderBy('step_order')
            ->get();

        $allRulesByModule = ApprovalLine::query()
            ->with('approverUser')
            ->where('is_active', true)
            ->orderBy('step_order')
            ->get()
            ->groupBy('modul');

        $approvalRequests = ApprovalRequest::query()
            ->with(['kegiatan', 'penugasan', 'pelaksanaan', 'pengaju', 'steps.assignedApprover', 'steps.actor'])
            ->when($modulFilter, fn ($q) => $q->where('modul', $modulFilter))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->latest()
            ->get();

        // Daftar Modul beserta pemetaan kolom tabel asli sippinter_new dan alur Approval Line-nya
        $moduleCatalog = [
            'pencairan_dana' => [
                'kode' => 'pencairan_dana',
                'nama_modul' => 'Pencairan Dana Kegiatan (BOK & BLUD)',
                'icon' => 'bx-wallet-alt',
                'badge_color' => 'primary',
                'deskripsi' => 'Persetujuan pencairan dana kegiatan Puskesmas, uang transport petugas, dan belanja bahan operasional.',
                'tabel_utama' => 'pencairan_danas & approval_requests',
                'kolom_database' => ['id', 'user_id', 'file_pencairan_dana', 'tanggal_kirim', 'di_read_oleh_orang_nya', 'approve_oleh_orang_nya', 'status', 'approved_at', 'alasan_penolakan', 'additional_text', 'created_by'],
                'rules' => $allRulesByModule->get('pencairan_dana', collect()),
                'requests_count' => ApprovalRequest::query()->where('modul', 'pencairan_dana')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'pencairan_dana')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
            'kegiatan_rpk' => [
                'kode' => 'kegiatan_rpk',
                'nama_modul' => 'Perencanaan RUK / RPK & Budget Plan BOK',
                'icon' => 'bx-spreadsheet',
                'badge_color' => 'info',
                'deskripsi' => 'Persetujuan dokumen RUK/RPK, rincian menu komponen BOK, kode rekening pembiayaan, dan pagu anggaran.',
                'tabel_utama' => 'budget_plans, budget_stages & kegiatans',
                'kolom_database' => ['id', 'fiscal_year', 'sub_bidang', 'menu', 'rincian_menu', 'komponen', 'kode_rekening_pembiayaan', 'harga_satuan', 'total_amount', 'sumber_anggaran', 'status', 'approved_by', 'approved_at'],
                'rules' => $allRulesByModule->get('kegiatan_rpk', collect()),
                'requests_count' => ApprovalRequest::query()->where('modul', 'kegiatan_rpk')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'kegiatan_rpk')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
            'pelaksanaan_spj' => [
                'kode' => 'pelaksanaan_spj',
                'nama_modul' => 'SPJ BOK, Kuitansi & Laporan Pelaksanaan',
                'icon' => 'bx-receipt',
                'badge_color' => 'success',
                'deskripsi' => 'Verifikasi kuitansi SPJ BOK (signatories left/center/right), rincian transport peserta, dan laporan pelaksanaan lapangan.',
                'tabel_utama' => 'bok_spjs, bok_spj_details & pelaksanaans',
                'kolom_database' => ['id', 'bok_activity_id', 'receipt_number', 'received_from', 'amount', 'purpose', 'execution_date', 'signatories (left/center/right)', 'created_by'],
                'rules' => $allRulesByModule->get('pelaksanaan_spj', collect()),
                'requests_count' => ApprovalRequest::query()->where('modul', 'pelaksanaan_spj')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'pelaksanaan_spj')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
            'penugasan' => [
                'kode' => 'penugasan',
                'nama_modul' => 'Surat Tugas & Aktivitas Lapangan BOK',
                'icon' => 'bx-user-check',
                'badge_color' => 'warning',
                'deskripsi' => 'Persetujuan surat tugas pegawai, titik lokasi kegiatan BOK, dan peserta kegiatan lapangan.',
                'tabel_utama' => 'penugasans, bok_activities & bok_participants',
                'kolom_database' => ['id', 'nomor_surat_tugas', 'kegiatan_id', 'pemberi_tugas_id', 'penanggung_jawab_id', 'tanggal_mulai', 'tenggat_waktu', 'target_pekerjaan', 'status'],
                'rules' => $allRulesByModule->get('penugasan', collect()),
                'requests_count' => ApprovalRequest::query()->where('modul', 'penugasan')->count(),
                'pending_count' => ApprovalRequest::query()->where('modul', 'penugasan')->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            ],
        ];

        // Baca langsung baris dari tabel-tabel asli di sippinter_new (`budget_plans`, `bok_spjs`, `pencairan_danas`, `edit_approval_requests`)
        $sharedDbRecords = $this->readSharedDatabaseApprovalTables();

        $summary = [
            'total_aturan' => ApprovalLine::query()->where('is_active', true)->count(),
            'total_pengajuan' => ApprovalRequest::query()->count(),
            'menunggu' => ApprovalRequest::query()->whereIn('status', ['menunggu', 'dalam_proses'])->count(),
            'disetujui' => ApprovalRequest::query()->where('status', 'disetujui')->count(),
            'ditolak_revisi' => ApprovalRequest::query()->whereIn('status', ['ditolak', 'revisi'])->count(),
            'total_nominal_diajukan' => (float) ApprovalRequest::query()->sum('nominal_diajukan'),
            'total_nominal_disetujui' => (float) ApprovalRequest::query()->where('status', 'disetujui')->sum('nominal_disetujui'),
        ];

        $kegiatans = Kegiatan::query()->orderBy('nama_kegiatan')->get();
        $penugasans = Penugasan::query()->with('kegiatan')->latest()->limit(50)->get();
        $pelaksanaans = Pelaksanaan::query()->with('kegiatan')->latest()->limit(50)->get();
        $rekenings = ChartOfAccount::query()->where('is_active', true)->orderBy('kode_akun')->get();
        $approverUsers = User::query()
            ->where('is_active', true)
            ->orderByRaw('CASE WHEN fullname IS NOT NULL AND fullname != "" THEN fullname ELSE username END')
            ->limit(150)
            ->get(['id', 'fullname', 'username', 'jabatan', 'role', 'nip']);

        $availableRoles = User::query()
            ->whereNotNull('role')
            ->where('role', '!=', '')
            ->distinct()
            ->pluck('role')
            ->merge(['super_admin', 'kepala_puskesmas', 'kepala_tata_usaha', 'adminkes', 'bendahara', 'pj_program'])
            ->unique()
            ->values();

        return view('approval.index', compact(
            'user',
            'activeTab',
            'modulFilter',
            'statusFilter',
            'approvalLines',
            'moduleCatalog',
            'sharedDbRecords',
            'approvalRequests',
            'summary',
            'kegiatans',
            'penugasans',
            'pelaksanaans',
            'rekenings',
            'approverUsers',
            'availableRoles'
        ));
    }

    /**
     * Membaca data & kolom langsung dari tabel-tabel finansial/approval bawaan database `sippinter_new`.
     *
     * @return array<string, mixed>
     */
    private function readSharedDatabaseApprovalTables(): array
    {
        $usersMap = User::query()
            ->get(['id', 'fullname', 'username', 'jabatan', 'role'])
            ->keyBy('id');

        $budgetPlans = collect();
        if (Schema::hasTable('budget_plans')) {
            $budgetPlans = DB::table('budget_plans')
                ->orderByDesc('id')
                ->limit(15)
                ->get()
                ->map(function ($row) use ($usersMap) {
                    $creator = $usersMap->get($row->created_by ?? null);
                    $approver = $usersMap->get($row->approved_by ?? null);

                    return [
                        'id' => $row->id,
                        'fiscal_year' => $row->fiscal_year ?? '-',
                        'sub_bidang' => $row->sub_bidang ?? '-',
                        'rincian_menu' => $row->rincian_menu ?? $row->menu ?? '-',
                        'kode_rekening' => $row->kode_rekening_pembiayaan ?? '-',
                        'detail_pembiayaan' => $row->detail_pembiayaan ?? '-',
                        'total_amount' => (float) ($row->total_amount ?? 0),
                        'sumber_anggaran' => $row->sumber_anggaran ?? 'BOK',
                        'lokasi' => $row->lokasi ?? '-',
                        'status' => $row->status ?? 'draft',
                        'created_by_name' => $creator?->name ?? ('User #'.($row->created_by ?? '-')),
                        'approved_by_name' => $approver?->name ?? ($row->approved_by ? 'User #'.$row->approved_by : '-'),
                        'approved_at' => $row->approved_at ?? null,
                    ];
                });
        }

        $bokSpjs = collect();
        if (Schema::hasTable('bok_spjs')) {
            $detailsBySpj = Schema::hasTable('bok_spj_details')
                ? DB::table('bok_spj_details')->get()->groupBy('bok_spj_id')
                : collect();

            $bokSpjs = DB::table('bok_spjs')
                ->orderByDesc('id')
                ->limit(15)
                ->get()
                ->map(function ($row) use ($usersMap, $detailsBySpj) {
                    $sig = json_decode((string) ($row->signatories ?? '{}'), true) ?: [];
                    $leftUser = isset($sig['left']) ? $usersMap->get((int) $sig['left']) : null;
                    $centerUser = isset($sig['center']) ? $usersMap->get((int) $sig['center']) : null;
                    $rightUser = isset($sig['right']) ? $usersMap->get((int) $sig['right']) : null;

                    return [
                        'id' => $row->id,
                        'receipt_number' => $row->receipt_number ?? '-',
                        'received_from' => $row->received_from ?? '-',
                        'amount' => (float) ($row->amount ?? 0),
                        'purpose' => $row->purpose ?? '-',
                        'destination' => $row->destination ?? '-',
                        'execution_date' => $row->execution_date ?? '-',
                        'signatory_left' => $leftUser ? $leftUser->name.' ('.($leftUser->jabatan ?: $leftUser->role).')' : '-',
                        'signatory_center' => $centerUser ? $centerUser->name.' ('.($centerUser->jabatan ?: $centerUser->role).')' : '-',
                        'signatory_right' => $rightUser ? $rightUser->name.' ('.($rightUser->jabatan ?: $rightUser->role).')' : '-',
                        'participants' => $detailsBySpj->get($row->id, collect()),
                    ];
                });
        }

        $pencairanDanas = collect();
        if (Schema::hasTable('pencairan_danas')) {
            $pencairanDanas = DB::table('pencairan_danas')
                ->orderByDesc('id')
                ->limit(15)
                ->get()
                ->map(function ($row) use ($usersMap) {
                    $targetUser = $usersMap->get($row->user_id ?? null);
                    $creator = $usersMap->get($row->created_by ?? null);

                    return [
                        'id' => $row->id,
                        'penerima' => $targetUser?->name ?? ('User #'.($row->user_id ?? '-')),
                        'pengirim' => $creator?->name ?? ('User #'.($row->created_by ?? '-')),
                        'tanggal_kirim' => $row->tanggal_kirim ?? '-',
                        'di_read' => (bool) ($row->di_read_oleh_orang_nya ?? false),
                        'approve_oleh_orang_nya' => $row->approve_oleh_orang_nya,
                        'status' => $row->status ?? 'pending',
                        'approved_at' => $row->approved_at ?? null,
                        'additional_text' => $row->additional_text ?? '-',
                        'alasan_penolakan' => $row->alasan_penolakan ?? null,
                    ];
                });
        }

        return [
            'budget_plans' => $budgetPlans,
            'bok_spjs' => $bokSpjs,
            'pencairan_danas' => $pencairanDanas,
        ];
    }

    /**
     * Tambah aturan Approval Line baru (Konfigurasi Level Berjenjang).
     */
    public function storeRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_aturan' => ['nullable', 'string', 'max:50'],
            'nama_aturan' => ['required', 'string', 'max:255'],
            'modul' => ['required', 'string', 'in:pencairan_dana,kegiatan_rpk,pelaksanaan_spj,penugasan'],
            'deskripsi' => ['nullable', 'string'],
            'nominal_minimal' => ['required', 'numeric', 'min:0'],
            'nominal_maksimal' => ['nullable', 'numeric', 'min:0'],
            'step_order' => ['required', 'integer', 'min:1', 'max:10'],
            'nama_level' => ['required', 'string', 'max:255'],
            'approver_type' => ['required', 'string', 'in:role,user,role_or_user'],
            'approver_role' => ['nullable', 'string', 'max:100'],
            'approver_user_id' => ['nullable', 'exists:users,id'],
            'allow_web' => ['nullable', 'boolean'],
            'allow_mobile' => ['nullable', 'boolean'],
            'is_mandatory' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $kode = $validated['kode_aturan'] ?: 'APR-RULE-'.strtoupper(substr($validated['modul'], 0, 4)).'-L'.$validated['step_order'].'-'.rand(100, 999);

        ApprovalLine::query()->updateOrCreate(
            ['kode_aturan' => $kode],
            [
                'nama_aturan' => $validated['nama_aturan'],
                'modul' => $validated['modul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'nominal_minimal' => $validated['nominal_minimal'],
                'nominal_maksimal' => $validated['nominal_maksimal'] ?? null,
                'step_order' => $validated['step_order'],
                'nama_level' => $validated['nama_level'],
                'approver_type' => $validated['approver_type'],
                'approver_role' => $validated['approver_role'] ?? null,
                'approver_user_id' => $validated['approver_user_id'] ?? null,
                'allow_web' => $request->boolean('allow_web', true),
                'allow_mobile' => $request->boolean('allow_mobile', true),
                'is_mandatory' => $request->boolean('is_mandatory', true),
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()->route('approval.index', ['tab' => 'aturan', 'modul' => $validated['modul']])
            ->with('success', 'Aturan Approval Line ('.$validated['nama_level'].') berhasil disimpan.');
    }

    /**
     * Perbarui aturan Approval Line.
     */
    public function updateRule(Request $request, ApprovalLine $approvalLine): RedirectResponse
    {
        $validated = $request->validate([
            'nama_aturan' => ['required', 'string', 'max:255'],
            'modul' => ['required', 'string', 'in:pencairan_dana,kegiatan_rpk,pelaksanaan_spj,penugasan'],
            'deskripsi' => ['nullable', 'string'],
            'nominal_minimal' => ['required', 'numeric', 'min:0'],
            'nominal_maksimal' => ['nullable', 'numeric', 'min:0'],
            'step_order' => ['required', 'integer', 'min:1', 'max:10'],
            'nama_level' => ['required', 'string', 'max:255'],
            'approver_role' => ['nullable', 'string', 'max:100'],
            'approver_user_id' => ['nullable', 'exists:users,id'],
            'allow_web' => ['nullable', 'boolean'],
            'allow_mobile' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $approvalLine->update([
            'nama_aturan' => $validated['nama_aturan'],
            'modul' => $validated['modul'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'nominal_minimal' => $validated['nominal_minimal'],
            'nominal_maksimal' => $validated['nominal_maksimal'] ?? null,
            'step_order' => $validated['step_order'],
            'nama_level' => $validated['nama_level'],
            'approver_role' => $validated['approver_role'] ?? null,
            'approver_user_id' => $validated['approver_user_id'] ?? null,
            'allow_web' => $request->boolean('allow_web', true),
            'allow_mobile' => $request->boolean('allow_mobile', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('approval.index', ['tab' => 'aturan', 'modul' => $validated['modul']])
            ->with('success', 'Aturan Approval Line '.$approvalLine->kode_aturan.' berhasil diperbarui.');
    }

    /**
     * Hapus aturan Approval Line.
     */
    public function destroyRule(ApprovalLine $approvalLine): RedirectResponse
    {
        $approvalLine->delete();

        return redirect()->route('approval.index', ['tab' => 'aturan'])
            ->with('success', 'Aturan Approval Line berhasil dihapus.');
    }

    /**
     * Buat pengajuan persetujuan dana / anggaran / SPJ baru dan bangun tahapan Approval Line otomatis
     * serta sinkronkan ke tabel `pencairan_danas` bila modul adalah `pencairan_dana`.
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

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

        DB::transaction(function () use ($validated, $user, $nominal, $matchingRules) {
            $totalSteps = max(1, $matchingRules->count());
            $nomor = 'APR-'.now()->format('Ym').'-'.str_pad((string) (ApprovalRequest::query()->count() + 1), 4, '0', STR_PAD_LEFT);

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
                'sumber_pengajuan' => 'web',
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

                    if ($stepOrder === 1 && $rule->approver_user_id) {
                        NotifikasiPegawai::query()->create([
                            'user_id' => $rule->approver_user_id,
                            'kegiatan_id' => $approvalRequest->kegiatan_id,
                            'penugasan_id' => $approvalRequest->penugasan_id,
                            'pelaksanaan_id' => $approvalRequest->pelaksanaan_id,
                            'jenis_notifikasi' => 'laporan_diterima',
                            'judul' => 'Permintaan Approval Dana Baru: '.$approvalRequest->nomor_pengajuan,
                            'pesan' => $approvalRequest->judul_pengajuan.' sebesar Rp '.number_format($nominal, 0, ',', '.').' menunggu persetujuan Level 1 ('.$rule->nama_level.').',
                            'url_tujuan' => '/approval-line?tab=pengajuan',
                            'is_read' => false,
                        ]);
                    }
                }
            } else {
                ApprovalRequestStep::query()->create([
                    'approval_request_id' => $approvalRequest->id,
                    'step_order' => 1,
                    'nama_level' => 'Persetujuan Pimpinan / Super Admin Puskesmas',
                    'approver_role' => 'super_admin',
                    'assigned_approver_id' => $user->id,
                    'status' => 'menunggu',
                ]);
            }
        });

        return redirect()->route('approval.index', ['tab' => 'pengajuan'])
            ->with('success', 'Pengajuan persetujuan dana berhasil dibuat dan masuk ke alur Approval Line.');
    }

    /**
     * Proses Approve / Tolak / Revisi dari Web.
     */
    public function processAction(Request $request, ApprovalRequest $approvalRequest): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'keputusan' => ['required', 'in:disetujui,ditolak,revisi'],
            'nominal_disetujui' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ]);

        self::executeApprovalDecision(
            $approvalRequest,
            $user,
            $validated['keputusan'],
            $validated['catatan'] ?? null,
            isset($validated['nominal_disetujui']) ? (float) $validated['nominal_disetujui'] : null,
            'web'
        );

        $label = match ($validated['keputusan']) {
            'disetujui' => 'disetujui',
            'ditolak' => 'ditolak',
            default => 'dikembalikan untuk revisi',
        };

        return redirect()->route('approval.index', ['tab' => 'pengajuan'])
            ->with('success', 'Pengajuan '.$approvalRequest->nomor_pengajuan.' berhasil '.$label.'.');
    }

    /**
     * Core logic eksekusi keputusan Approval Line (digunakan bersama oleh Web & Mobile API).
     */
    public static function executeApprovalDecision(
        ApprovalRequest $approvalRequest,
        User $actor,
        string $keputusan,
        ?string $catatan = null,
        ?float $nominalDisetujui = null,
        string $channel = 'web'
    ): ApprovalRequest {
        DB::transaction(function () use ($approvalRequest, $actor, $keputusan, $catatan, $nominalDisetujui, $channel) {
            $approvalRequest->load('steps');
            $activeStep = $approvalRequest->activeStep();

            if ($activeStep) {
                $activeStep->update([
                    'acted_by' => $actor->id,
                    'status' => $keputusan,
                    'acted_via' => $channel,
                    'catatan' => $catatan,
                    'acted_at' => now(),
                ]);
            }

            if ($keputusan === 'disetujui') {
                $nextStep = $approvalRequest->steps()
                    ->where('status', 'menunggu')
                    ->orderBy('step_order')
                    ->first();

                if ($nextStep) {
                    $approvalRequest->update([
                        'current_step' => $nextStep->step_order,
                        'status' => 'dalam_proses',
                        'catatan_akhir' => $catatan,
                    ]);

                    if ($nextStep->assigned_approver_id) {
                        NotifikasiPegawai::query()->create([
                            'user_id' => $nextStep->assigned_approver_id,
                            'kegiatan_id' => $approvalRequest->kegiatan_id,
                            'penugasan_id' => $approvalRequest->penugasan_id,
                            'pelaksanaan_id' => $approvalRequest->pelaksanaan_id,
                            'jenis_notifikasi' => 'laporan_diterima',
                            'judul' => 'Giliran Approval Level '.$nextStep->step_order.': '.$approvalRequest->nomor_pengajuan,
                            'pesan' => $approvalRequest->judul_pengajuan.' telah disetujui tahap sebelumnya dan menunggu persetujuan Anda ('.$nextStep->nama_level.').',
                            'url_tujuan' => '/approval-line?tab=pengajuan',
                            'is_read' => false,
                        ]);
                    }
                } else {
                    $finalNominal = $nominalDisetujui ?? (float) $approvalRequest->nominal_diajukan;

                    $approvalRequest->update([
                        'nominal_disetujui' => $finalNominal,
                        'status' => 'disetujui',
                        'completed_at' => now(),
                        'catatan_akhir' => $catatan ?: 'Seluruh tahapan Approval Line telah disetujui.',
                    ]);

                    // Sinkronkan ke tabel `pencairan_danas` bila terhubung
                    if ($approvalRequest->referensi_tipe === 'pencairan_danas' && $approvalRequest->referensi_id && Schema::hasTable('pencairan_danas')) {
                        DB::table('pencairan_danas')
                            ->where('id', $approvalRequest->referensi_id)
                            ->update([
                                'di_read_oleh_orang_nya' => 1,
                                'read_at' => now(),
                                'approve_oleh_orang_nya' => 1,
                                'status' => 'approved',
                                'approved_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }

                    // Sinkronkan realisasi_anggaran / status_keuangan pada Kegiatan RPK bila terkait
                    if ($approvalRequest->kegiatan_id && $approvalRequest->kegiatan) {
                        $kegiatan = $approvalRequest->kegiatan;
                        if ($approvalRequest->modul === 'pencairan_dana' || $approvalRequest->modul === 'pelaksanaan_spj') {
                            $totalApproved = (float) ApprovalRequest::query()
                                ->where('kegiatan_id', $kegiatan->id)
                                ->where('status', 'disetujui')
                                ->sum('nominal_disetujui');

                            $kegiatan->update([
                                'realisasi_anggaran' => max((float) $kegiatan->realisasi_anggaran, $totalApproved),
                                'status_keuangan' => 'disetujui',
                            ]);
                        } elseif ($approvalRequest->modul === 'kegiatan_rpk') {
                            $kegiatan->update([
                                'status_keuangan' => 'disetujui',
                            ]);
                        }
                    }

                    NotifikasiPegawai::query()->create([
                        'user_id' => $approvalRequest->pengaju_id,
                        'kegiatan_id' => $approvalRequest->kegiatan_id,
                        'penugasan_id' => $approvalRequest->penugasan_id,
                        'pelaksanaan_id' => $approvalRequest->pelaksanaan_id,
                        'jenis_notifikasi' => 'laporan_disetujui',
                        'judul' => 'Pengajuan Dana Disetujui Penuh: '.$approvalRequest->nomor_pengajuan,
                        'pesan' => 'Pengajuan "'.$approvalRequest->judul_pengajuan.'" sebesar Rp '.number_format($finalNominal, 0, ',', '.').' telah disetujui melalui '.$channel.'.',
                        'url_tujuan' => '/approval-line?tab=pengajuan',
                        'is_read' => false,
                    ]);
                }
            } else {
                $approvalRequest->update([
                    'status' => $keputusan,
                    'completed_at' => now(),
                    'catatan_akhir' => $catatan,
                ]);

                if ($approvalRequest->referensi_tipe === 'pencairan_danas' && $approvalRequest->referensi_id && Schema::hasTable('pencairan_danas')) {
                    DB::table('pencairan_danas')
                        ->where('id', $approvalRequest->referensi_id)
                        ->update([
                            'di_read_oleh_orang_nya' => 1,
                            'read_at' => now(),
                            'approve_oleh_orang_nya' => 0,
                            'status' => 'rejected',
                            'alasan_penolakan' => $catatan,
                            'updated_at' => now(),
                        ]);
                }

                NotifikasiPegawai::query()->create([
                    'user_id' => $approvalRequest->pengaju_id,
                    'kegiatan_id' => $approvalRequest->kegiatan_id,
                    'penugasan_id' => $approvalRequest->penugasan_id,
                    'pelaksanaan_id' => $approvalRequest->pelaksanaan_id,
                    'jenis_notifikasi' => 'laporan_perlu_perbaikan',
                    'judul' => 'Status Approval '.$approvalRequest->nomor_pengajuan.': '.strtoupper($keputusan),
                    'pesan' => 'Pengajuan "'.$approvalRequest->judul_pengajuan.'" membutuhkan perhatian: '.($catatan ?: 'Silakan cek catatan verifikator.'),
                    'url_tujuan' => '/approval-line?tab=pengajuan',
                    'is_read' => false,
                ]);
            }
        });

        return $approvalRequest->fresh(['steps.assignedApprover', 'steps.actor', 'kegiatan', 'pengaju']);
    }
}
