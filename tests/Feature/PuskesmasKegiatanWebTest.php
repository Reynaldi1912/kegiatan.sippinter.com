<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\Kegiatan;
use App\Models\Pelaksanaan;
use App\Models\Penugasan;
use App\Models\Tupoksi;
use App\Models\User;
use Database\Seeders\PuskesmasKegiatanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PuskesmasKegiatanWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pegawai_can_login_with_username_or_email_and_view_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'kapus',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $dashboard = $this->get(route('dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('dr. H. Hendra Wijaya, M.Kes');
        $dashboard->assertSee('Jadwal Kegiatan Hari Ini');
    }

    public function test_all_web_pages_render_successfully_for_authenticated_pegawai(): void
    {
        $user = User::query()->where('username', 'kapus')->firstOrFail();
        $this->actingAs($user);

        $kegiatan = Kegiatan::query()->firstOrFail();
        $penugasan = Penugasan::query()->firstOrFail();
        $pelaksanaan = Pelaksanaan::query()->firstOrFail();
        $tupoksi = Tupoksi::query()->firstOrFail();

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('jadwal.index', ['mode' => 'kalender']))->assertOk();
        $this->get(route('jadwal.index', ['mode' => 'daftar']))->assertOk();
        $this->get(route('jadwal.show', $kegiatan))->assertOk();
        $this->get(route('penugasan.index'))->assertOk();
        $this->get(route('penugasan.show', $penugasan))->assertOk();
        $this->get(route('pelaksanaan.index'))->assertOk();
        $this->get(route('pelaksanaan.create', ['penugasan_id' => $penugasan->id]))->assertOk();
        $this->get(route('pelaksanaan.show', $pelaksanaan))->assertOk();
        $this->get(route('tupoksi.index'))->assertOk();
        $this->get(route('tupoksi.show', $tupoksi))->assertOk();
        $this->get(route('riwayat.index'))->assertOk();
        $this->get(route('riwayat.show', $pelaksanaan))->assertOk();
        $this->get(route('notifikasi.index'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
        $this->get(route('pegawai.index'))->assertOk();
        $this->get(route('kegiatan.index'))->assertOk();
        $this->get(route('approval.index'))->assertOk();
        $this->get(route('sinkronisasi.index'))->assertOk();
    }

    public function test_submitting_pelaksanaan_and_approving_updates_tupoksi_realisasi(): void
    {
        Storage::fake('public');

        $budi = User::query()->where('username', 'perawat_budi')->firstOrFail();
        $kapus = User::query()->where('username', 'kapus')->firstOrFail();
        $tupoksiP2P = Tupoksi::query()->where('kode_tupoksi', 'TPK-2026-003')->firstOrFail();

        $penugasan = Penugasan::query()
            ->where('tupoksi_id', $tupoksiP2P->id)
            ->firstOrFail();

        $this->actingAs($budi);

        $storeResponse = $this->post(route('pelaksanaan.store'), [
            'penugasan_id' => $penugasan->id,
            'client_sync_id' => 'test-sync-uuid-999',
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->format('Y-m-d'),
            'uraian_hasil' => 'Pemeriksaan 30 rumah selesai dilaksanakan.',
            'jumlah_target' => 30,
            'jumlah_realisasi' => 30,
            'satuan' => 'Rumah / Lokasi',
            'keterangan' => 'Seluruh rumah sasaran terperiksa.',
            'catatan_pegawai' => 'Tidak ada kendala.',
            'aksi_simpan' => 'kirim',
            'foto_dokumentasi' => [
                UploadedFile::fake()->create('dokumentasi_pe.jpg', 250, 'image/jpeg'),
            ],
            'keterangan_dokumentasi' => 'Foto kegiatan PE lapangan',
        ]);

        $pelaksanaan = Pelaksanaan::query()->where('client_sync_id', 'test-sync-uuid-999')->firstOrFail();
        $storeResponse->assertRedirect(route('pelaksanaan.show', $pelaksanaan));
        $this->assertCount(1, $pelaksanaan->dokumentasis);

        // Verifikasi & Setujui oleh Kepala Puskesmas
        $this->actingAs($kapus);
        $this->post(route('pelaksanaan.verifikasi', $pelaksanaan), [
            'status_laporan' => 'disetujui',
            'catatan_verifikasi' => 'Disetujui dan masuk ke capaian Tupoksi P2P.',
        ])->assertRedirect();

        $this->assertSame('disetujui', $pelaksanaan->fresh()->status_laporan);
        $this->assertSame('selesai', $penugasan->fresh()->status);
        $this->assertSame(30, $tupoksiP2P->fresh()->realisasi);
    }

    public function test_mobile_sync_api_prevents_duplicate_reports(): void
    {
        $penugasan = Penugasan::query()->firstOrFail();

        $payload = [
            'client_sync_id' => 'unique-mobile-idempotency-key-001',
            'penugasan_id' => $penugasan->id,
            'tanggal_mulai' => now()->format('Y-m-d'),
            'tanggal_selesai' => now()->format('Y-m-d'),
            'uraian_hasil' => 'Sinkronisasi laporan pelaksanaan dari aplikasi mobile.',
            'jumlah_target' => 25,
            'jumlah_realisasi' => 25,
            'satuan' => 'Sasaran',
            'status_laporan' => 'dikirim',
        ];

        $first = $this->postJson(route('api.mobile.pelaksanaan.sync'), $payload);
        $first->assertCreated()
            ->assertJsonPath('duplicate_prevented', false);

        $second = $this->postJson(route('api.mobile.pelaksanaan.sync'), $payload);
        $second->assertOk()
            ->assertJsonPath('duplicate_prevented', true);

        $this->assertSame(
            1,
            Pelaksanaan::query()->where('client_sync_id', 'unique-mobile-idempotency-key-001')->count()
        );
    }

    public function test_seeder_reuses_existing_users_without_creating_new_users(): void
    {
        $userCountBefore = User::query()->count();
        $this->assertGreaterThan(0, $userCountBefore);

        $this->seed(PuskesmasKegiatanSeeder::class);

        $this->assertSame($userCountBefore, User::query()->count());
        $this->assertGreaterThanOrEqual(6, Kegiatan::query()->count());
        $this->assertGreaterThanOrEqual(6, Tupoksi::query()->count());
        $this->assertGreaterThanOrEqual(5, Penugasan::query()->count());
    }

    public function test_pegawai_can_login_with_md5_hashed_password(): void
    {
        $user = User::query()->create([
            'fullname' => 'Abdillah',
            'username' => 'super.abdillah',
            'role' => 'super_admin',
            'is_active' => true,
            'password' => md5('superadmin'),
        ]);

        $response = $this->post('/login', [
            'email' => 'super.abdillah',
            'password' => 'superadmin',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_approval_line_can_be_processed_via_web_and_mobile_api(): void
    {
        $kapus = User::query()->where('username', 'kapus')->firstOrFail();
        $kegiatan = Kegiatan::query()->firstOrFail();

        $this->actingAs($kapus);

        // 1. Buat pengajuan dana baru lewat Web
        $this->post(route('approval.requests.store'), [
            'judul_pengajuan' => 'Pencairan Dana Operasional Uji Coba Approval Line',
            'modul' => 'pencairan_dana',
            'kegiatan_id' => $kegiatan->id,
            'nominal_diajukan' => 2500000,
            'sumber_dana' => 'BOK Puskesmas',
            'kode_rekening' => '5.1.02.04.01.0003',
            'uraian_pengajuan' => 'Pengajuan dana transport dan konsumsi posyandu.',
        ])->assertRedirect();

        $approvalRequest = ApprovalRequest::query()
            ->where('judul_pengajuan', 'Pencairan Dana Operasional Uji Coba Approval Line')
            ->firstOrFail();

        $this->assertSame('menunggu', $approvalRequest->status);
        $this->assertSame(1, $approvalRequest->current_step);
        $this->assertSame(3, $approvalRequest->total_steps);

        // 2. Approve Tahap 1 lewat Web
        $this->post(route('approval.requests.action', $approvalRequest), [
            'keputusan' => 'disetujui',
            'nominal_disetujui' => 2500000,
            'catatan' => 'Tahap 1 disetujui via Web.',
        ])->assertRedirect();

        $approvalRequest->refresh();
        $this->assertSame('dalam_proses', $approvalRequest->status);
        $this->assertSame(2, $approvalRequest->current_step);

        // 3. Approve Tahap 2 & Tahap 3 lewat Mobile API
        $this->postJson(route('api.mobile.approvals.action', $approvalRequest), [
            'keputusan' => 'disetujui',
            'catatan' => 'Tahap 2 disetujui via Mobile.',
        ])->assertOk();

        $this->postJson(route('api.mobile.approvals.action', $approvalRequest), [
            'keputusan' => 'disetujui',
            'nominal_disetujui' => 2500000,
            'catatan' => 'Tahap 3 otorisasi penuh via Mobile.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'disetujui');

        $this->assertSame('disetujui', $approvalRequest->fresh()->status);
        $this->assertEquals(2500000, (float) $approvalRequest->fresh()->nominal_disetujui);

        // 4. Uji coba pengajuan baru dari sippinter_mobile menggunakan X-User-Id header
        $this->withHeader('X-User-Id', (string) $kapus->id)
            ->postJson(route('api.mobile.approvals.store'), [
                'judul_pengajuan' => 'Pengajuan Dana dari Mobile App SIPPINTER',
                'modul' => 'pencairan_dana',
                'nominal_diajukan' => 1750000,
                'sumber_dana' => 'BOK Puskesmas',
                'uraian_pengajuan' => 'Uji coba sinkronisasi pengajuan dari mobile.',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sumber_pengajuan', 'mobile');
    }
}
