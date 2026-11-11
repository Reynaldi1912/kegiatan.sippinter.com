<?php

namespace Database\Seeders;

use App\Models\ApprovalLine;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\ChartOfAccount;
use App\Models\DokumentasiKegiatan;
use App\Models\Kegiatan;
use App\Models\NotifikasiPegawai;
use App\Models\Pelaksanaan;
use App\Models\Penugasan;
use App\Models\Tupoksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder data awal modul Kegiatan SIPPINTER.
 *
 * Menggunakan akun `users` yang sudah ada di database `sippinter_new` tanpa
 * membuat user baru. Fallback pembuatan user hanya dijalankan bila tabel
 * `users` benar-benar kosong (misalnya saat pengujian otomatis SQLite in-memory).
 */
class PuskesmasKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedChartOfAccounts();
        $this->syncExistingUsersWithMasterEmployees();

        $actors = $this->resolveExistingActors();
        $tupoksis = $this->seedTupoksis($actors);
        $kegiatans = $this->seedKegiatans($actors);
        $penugasans = $this->seedPenugasans($actors, $tupoksis, $kegiatans);
        $pelaksanaans = $this->seedPelaksanaansAndDokumentasi($actors, $tupoksis, $kegiatans, $penugasans);
        $this->seedApprovalLinesAndRequests($actors, $kegiatans, $penugasans, $pelaksanaans);
        $this->seedNotifikasis($actors, $kegiatans, $penugasans, $pelaksanaans);
    }

    /**
     * 1. Seed Master Kode Rekening (Chart of Accounts) untuk RUK/RPK/RBA.
     */
    private function seedChartOfAccounts(): void
    {
        $coas = [
            [
                'kode_akun' => '5.1.02.04.01.0003',
                'nama_akun' => 'Belanja Perjalanan Dinas Dalam Kota (BOK Puskesmas)',
                'kategori' => 'Belanja Operasional BOK',
                'level' => 6,
            ],
            [
                'kode_akun' => '5.1.02.01.01.0052',
                'nama_akun' => 'Belanja Makanan dan Minuman Aktivitas Lapangan & Posyandu',
                'kategori' => 'Belanja Operasional BOK',
                'level' => 6,
            ],
            [
                'kode_akun' => '5.1.02.01.01.0037',
                'nama_akun' => 'Belanja Obat-obatan & Bahan Medis Habis Pakai (BMHP)',
                'kategori' => 'Belanja Pelayanan BLUD',
                'level' => 6,
            ],
            [
                'kode_akun' => '5.1.02.02.01.0003',
                'nama_akun' => 'Honorarium Narasumber / Penyuluhan Kesehatan Masyarakat',
                'kategori' => 'Belanja Operasional BOK',
                'level' => 6,
            ],
            [
                'kode_akun' => '5.1.02.01.01.0024',
                'nama_akun' => 'Belanja Alat/Bahan untuk Kegiatan Kantor - Alat Tulis Kantor (ATK)',
                'kategori' => 'Belanja Operasional BOK',
                'level' => 6,
            ],
            [
                'kode_akun' => '5.1.02.01.01.0026',
                'nama_akun' => 'Belanja Alat/Bahan untuk Kegiatan Kantor - Bahan Cetak & Penggandaan',
                'kategori' => 'Belanja Operasional BOK',
                'level' => 6,
            ],
        ];

        foreach ($coas as $coa) {
            ChartOfAccount::query()->updateOrCreate(
                ['kode_akun' => $coa['kode_akun']],
                array_merge($coa, ['is_active' => true])
            );
        }
    }

    /**
     * Sinkronisasi profil kepegawaian (`nip`, `jabatan`, `unit_program`, `phone`)
     * dari tabel `master_employees` yang sudah ada di `sippinter_new` ke tabel `users`
     * tanpa menambah user baru.
     */
    private function syncExistingUsersWithMasterEmployees(): void
    {
        if (! Schema::hasTable('master_employees') || ! Schema::hasColumn('users', 'nip')) {
            return;
        }

        $employees = DB::table('master_employees')
            ->whereNotNull('name')
            ->get(['name', 'nip', 'jabatan', 'unit_penempatan', 'phone_number']);

        foreach ($employees as $emp) {
            $user = User::query()
                ->where('fullname', $emp->name)
                ->first();

            if (! $user) {
                continue;
            }

            $updates = [];

            if (empty($user->nip) && ! empty($emp->nip)) {
                $nipExists = User::query()
                    ->where('nip', $emp->nip)
                    ->where('id', '!=', $user->id)
                    ->exists();

                if (! $nipExists) {
                    $updates['nip'] = $emp->nip;
                }
            }

            if (empty($user->jabatan) && ! empty($emp->jabatan)) {
                $updates['jabatan'] = $emp->jabatan;
            }

            if (empty($user->unit_program) && ! empty($emp->unit_penempatan)) {
                $updates['unit_program'] = $emp->unit_penempatan;
            }

            if (empty($user->phone) && ! empty($emp->phone_number)) {
                $updates['phone'] = $emp->phone_number;
            }

            if ($updates !== []) {
                $user->update($updates);
            }
        }
    }

    /**
     * Ambil pegawai yang sudah ada di tabel `users` pada `sippinter_new`.
     * Tidak membuat user baru jika tabel `users` sudah memiliki data.
     *
     * @return array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}
     */
    private function resolveExistingActors(): array
    {
        if (! User::query()->exists()) {
            return $this->seedFallbackUsersForEmptyTestDatabase();
        }

        $fallbackUser = User::query()->orderBy('id')->firstOrFail();

        $kapus = $this->findExistingUser(['kapus', 'ZAHROTUL', 'super.abdillah', 'LIDYA'], ['kepala_puskesmas', 'super_admin', 'dokter']) ?? $fallbackUser;
        $ktu = $this->findExistingUser(['ktu', 'WENY', 'super.abdillah'], ['kepala_tata_usaha', 'admin', 'super_admin']) ?? $kapus;
        $bidan = $this->findExistingUser(['bidan_siti', 'ROSDA', 'ANI', 'NINIK', 'UMMUL'], ['pj_program', 'user']) ?? $fallbackUser;
        $perawat = $this->findExistingUser(['perawat_budi', 'ENY', 'MUSRIFA', 'WINDA'], ['perawat', 'pegawai']) ?? $fallbackUser;
        $gizi = $this->findExistingUser(['gizi_dina', 'AGIES', 'DINDA'], ['pengaduan', 'pegawai', 'user']) ?? $fallbackUser;
        $promkes = $this->findExistingUser(['promkes_arif', 'EGA', 'WIWIN'], ['adminkes', 'pegawai']) ?? $fallbackUser;

        return [
            'kapus' => $kapus,
            'ktu' => $ktu,
            'bidan' => $bidan,
            'perawat' => $perawat,
            'gizi' => $gizi,
            'promkes' => $promkes,
        ];
    }

    /**
     * Cari user yang sudah ada berdasarkan daftar username prioritas atau role.
     *
     * @param  list<string>  $usernames
     * @param  list<string>  $roles
     */
    private function findExistingUser(array $usernames, array $roles): ?User
    {
        foreach ($usernames as $username) {
            $found = User::query()->where('username', $username)->first();
            if ($found) {
                return $found;
            }
        }

        return User::query()->whereIn('role', $roles)->orderBy('id')->first();
    }

    /**
     * Fallback khusus ketika database benar-benar kosong (0 user), misalnya pada
     * pengujian otomatis PHPUnit dengan SQLite `:memory:`.
     *
     * @return array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}
     */
    private function seedFallbackUsersForEmptyTestDatabase(): array
    {
        $password = md5('password');

        $kapus = User::query()->create([
            'name' => 'dr. H. Hendra Wijaya, M.Kes',
            'username' => 'kapus',
            'nip' => '197805142006041008',
            'email' => 'admin@sippinter.com',
            'jabatan' => 'Kepala Puskesmas',
            'unit_program' => 'Manajemen Puskesmas',
            'phone' => '0812-3456-7801',
            'role' => 'kepala_puskesmas',
            'is_active' => true,
            'password' => $password,
            'api_token' => 'sipp-mob-kapus-token-2026',
        ]);

        $ktu = User::query()->create([
            'name' => 'Hj. Ratna Sari, SKM, M.Si',
            'username' => 'ktu',
            'nip' => '198209112009022004',
            'email' => 'ktu@sippinter.com',
            'jabatan' => 'Kepala Sub Bagian Tata Usaha',
            'unit_program' => 'Tata Usaha & Kepegawaian',
            'phone' => '0812-3456-7802',
            'role' => 'kepala_tata_usaha',
            'is_active' => true,
            'password' => $password,
            'api_token' => 'sipp-mob-ktu-token-2026',
        ]);

        $bidan = User::query()->create([
            'name' => 'Bdn. Siti Aminah, S.Tr.Keb',
            'username' => 'bidan_siti',
            'nip' => '198903212014032005',
            'email' => 'kia@sippinter.com',
            'jabatan' => 'Bidan Koordinator / PJ UKM KIA-KB',
            'unit_program' => 'KIA & KB',
            'phone' => '0813-4567-8901',
            'role' => 'pj_program',
            'is_active' => true,
            'password' => $password,
            'api_token' => 'sipp-mob-siti-token-2026',
        ]);

        $perawat = User::query()->create([
            'name' => 'Ns. Budi Santoso, S.Kep',
            'username' => 'perawat_budi',
            'nip' => '199107182017041002',
            'email' => 'p2p@sippinter.com',
            'jabatan' => 'Perawat Pelaksana / Surveilans P2P',
            'unit_program' => 'P2P & Imunisasi',
            'phone' => '0813-4567-8902',
            'role' => 'pegawai',
            'is_active' => true,
            'password' => $password,
            'api_token' => 'sipp-mob-budi-token-2026',
        ]);

        $gizi = User::query()->create([
            'name' => 'Dina Mariana, S.Gz',
            'username' => 'gizi_dina',
            'nip' => '199411052019032009',
            'email' => 'gizi@sippinter.com',
            'jabatan' => 'Nutrisionis Pelaksana',
            'unit_program' => 'Gizi Masyarakat',
            'phone' => '0813-4567-8903',
            'role' => 'pegawai',
            'is_active' => true,
            'password' => $password,
            'api_token' => 'sipp-mob-dina-token-2026',
        ]);

        $promkes = User::query()->create([
            'name' => 'Arif Rahman, SKM',
            'username' => 'promkes_arif',
            'nip' => '199302142018011003',
            'email' => 'promkes@sippinter.com',
            'jabatan' => 'Penyuluh Kesehatan Masyarakat & Kesling',
            'unit_program' => 'Promosi Kesehatan & Kesling',
            'phone' => '0813-4567-8904',
            'role' => 'pegawai',
            'is_active' => true,
            'password' => $password,
            'api_token' => 'sipp-mob-arif-token-2026',
        ]);

        return [
            'kapus' => $kapus,
            'ktu' => $ktu,
            'bidan' => $bidan,
            'perawat' => $perawat,
            'gizi' => $gizi,
            'promkes' => $promkes,
        ];
    }

    /**
     * 2. Seed Master & Target Tupoksi Pegawai (PRD 3.7).
     *
     * @param  array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}  $actors
     * @return array{kia: Tupoksi, gizi: Tupoksi, p2p: Tupoksi, promkes: Tupoksi, ktu: Tupoksi, ilp: Tupoksi}
     */
    private function seedTupoksis(array $actors): array
    {
        $kia = Tupoksi::query()->updateOrCreate(
            ['kode_tupoksi' => 'TPK-2026-001'],
            [
                'user_id' => $actors['bidan']->id,
                'nama_tupoksi' => 'Pelayanan ANC Terpadu & Kelas Ibu Hamil Wilayah Puskesmas',
                'uraian_tugas' => 'Melaksanakan pemeriksaan kehamilan terpadu (ANC 10T), pemantauan ibu hamil risiko tinggi, serta fasilitasi Kelas Ibu Hamil di desa binaan.',
                'target' => 120,
                'realisasi' => 45,
                'satuan' => 'Ibu Hamil',
                'indikator_keberhasilan' => 'Cakupan K4/K6 ibu hamil mencapai minimal 95% dan seluruh bumil risti terpantau.',
                'periode' => 'Tahun 2026',
                'is_active' => true,
            ]
        );

        $gizi = Tupoksi::query()->updateOrCreate(
            ['kode_tupoksi' => 'TPK-2026-002'],
            [
                'user_id' => $actors['gizi']->id,
                'nama_tupoksi' => 'Pemantauan Tumbuh Kembang Balita & Intervensi Stunting Posyandu',
                'uraian_tugas' => 'Melakukan penimbangan, pengukuran panjang/tinggi badan balita, pemberian PMT lokal bagi balita gizi kurang/stunting, dan edukasi PMBA.',
                'target' => 200,
                'realisasi' => 85,
                'satuan' => 'Balita',
                'indikator_keberhasilan' => 'Persentase balita ditimbang (D/S) >= 85% dan penurunan prevalensi balita stunting.',
                'periode' => 'Tahun 2026',
                'is_active' => true,
            ]
        );

        $p2p = Tupoksi::query()->updateOrCreate(
            ['kode_tupoksi' => 'TPK-2026-003'],
            [
                'user_id' => $actors['perawat']->id,
                'nama_tupoksi' => 'Penyelidikan Epidemiologi (PE) & Pengendalian Vektor DBD',
                'uraian_tugas' => 'Melaksanakan penyelidikan epidemiologi kasus demam berdarah dalam waktu <24 jam, pemeriksaan jentik berkala (PJB), dan koordinasi fogging fokus.',
                'target' => 60,
                'realisasi' => 30,
                'satuan' => 'Rumah / Lokasi',
                'indikator_keberhasilan' => 'Angka Bebas Jentik (ABJ) >= 95% dan 100% kasus DBD dilakukan PE tepat waktu.',
                'periode' => 'Tahun 2026',
                'is_active' => true,
            ]
        );

        $promkes = Tupoksi::query()->updateOrCreate(
            ['kode_tupoksi' => 'TPK-2026-004'],
            [
                'user_id' => $actors['promkes']->id,
                'nama_tupoksi' => 'Penyuluhan PHBS & Inspeksi Kesehatan Lingkungan Sekolah/Desa',
                'uraian_tugas' => 'Melakukan penyuluhan Perilaku Hidup Bersih dan Sehat (PHBS), kampanye GERMAS, serta inspeksi sanitasi sarana air bersih dan sekolah.',
                'target' => 40,
                'realisasi' => 15,
                'satuan' => 'Institusi / Desa',
                'indikator_keberhasilan' => 'Minimal 80% sekolah dan desa binaan memenuhi standar PHBS & sanitasi sehat.',
                'periode' => 'Tahun 2026',
                'is_active' => true,
            ]
        );

        $ktu = Tupoksi::query()->updateOrCreate(
            ['kode_tupoksi' => 'TPK-2026-005'],
            [
                'user_id' => $actors['ktu']->id,
                'nama_tupoksi' => 'Evaluasi Kinerja Pelaksanaan RPK & Administrasi Kepegawaian',
                'uraian_tugas' => 'Menyusun rencana pelaksanaan kegiatan (RPK) bulanan, memverifikasi laporan pelaksanaan kegiatan pegawai, dan evaluasi serapan anggaran BOK.',
                'target' => 12,
                'realisasi' => 10,
                'satuan' => 'Dokumen Evaluasi',
                'indikator_keberhasilan' => 'Ketepatan waktu pelaporan RPK dan verifikasi kinerja bulanan 100%.',
                'periode' => 'Tahun 2026',
                'is_active' => true,
            ]
        );

        $ilp = Tupoksi::query()->updateOrCreate(
            ['kode_tupoksi' => 'TPK-2026-006'],
            [
                'user_id' => $actors['kapus']->id,
                'nama_tupoksi' => 'Pendampingan Integrasi Layanan Primer (ILP) Posyandu Prima & Pustu',
                'uraian_tugas' => 'Melaksanakan supervisi, pembinaan, dan pendampingan pelaksanaan Integrasi Layanan Primer (ILP) siklus hidup pada Posyandu dan Pustu di 12 desa wilayah kerja.',
                'target' => 12,
                'realisasi' => 8,
                'satuan' => 'Desa / Posyandu ILP',
                'indikator_keberhasilan' => 'Seluruh 12 Posyandu ILP desa menjalankan pelayanan klaster siklus hidup sesuai standar.',
                'periode' => 'Tahun 2026',
                'is_active' => true,
            ]
        );

        return compact('kia', 'gizi', 'p2p', 'promkes', 'ktu', 'ilp');
    }

    /**
     * 3. Seed Kegiatan RUK / RPK / RBA & Jadwal Kegiatan (PRD 3.4 & 8).
     *
     * @param  array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}  $actors
     * @return array{k1: Kegiatan, k2: Kegiatan, k3: Kegiatan, k4: Kegiatan, k5: Kegiatan, k6: Kegiatan}
     */
    private function seedKegiatans(array $actors): array
    {
        $today = Carbon::today();

        $k1 = Kegiatan::query()->updateOrCreate(
            ['kode_kegiatan' => 'RPK-2026-001'],
            [
                'nama_kegiatan' => 'Pelayanan Posyandu Balita & Kelas Ibu Hamil Desa Sukamaju',
                'deskripsi' => 'Pelaksanaan pemeriksaan kehamilan ANC terpadu, imunisasi dasar lengkap, penimbangan balita, dan pemberian PMT penyuluhan di Posyandu Melati.',
                'program' => 'KIA & KB',
                'jenis_dokumen' => 'RPK',
                'sumber_dana' => 'BOK Puskesmas',
                'sumber_kegiatan' => 'RPK Bulanan',
                'kode_rekening' => '5.1.02.04.01.0003',
                'pagu_anggaran' => 2400000,
                'realisasi_anggaran' => 2400000,
                'status_keuangan' => 'dibayar',
                'tanggal_mulai' => $today->copy()->subDays(3),
                'tanggal_selesai' => $today->copy()->subDays(3),
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '12:30',
                'lokasi' => 'Posyandu Melati I, Desa Sukamaju',
                'penanggung_jawab_id' => $actors['bidan']->id,
                'target_kegiatan' => 45,
                'satuan_target' => 'Ibu Hamil',
                'indikator_keberhasilan' => 'Seluruh ibu hamil dan balita sasaran di Desa Sukamaju mendapatkan layanan kesehatan standar.',
                'status' => 'selesai',
            ]
        );

        $k2 = Kegiatan::query()->updateOrCreate(
            ['kode_kegiatan' => 'RPK-2026-002'],
            [
                'nama_kegiatan' => 'Penyelidikan Epidemiologi (PE) & Pemantauan Jentik DBD RW 04',
                'deskripsi' => 'Kunjungan lapangan tim surveilans P2P untuk investigasi kontak demam berdarah radius 100 meter, pemeriksaan jentik nyamuk, dan pembagian larvasida.',
                'program' => 'P2P & Imunisasi',
                'jenis_dokumen' => 'RPK',
                'sumber_dana' => 'BOK Puskesmas',
                'sumber_kegiatan' => 'Disposisi Pimpinan',
                'kode_rekening' => '5.1.02.04.01.0003',
                'pagu_anggaran' => 1800000,
                'realisasi_anggaran' => 900000,
                'status_keuangan' => 'proses_penagihan',
                'tanggal_mulai' => $today->copy(),
                'tanggal_selesai' => $today->copy(),
                'waktu_mulai' => '08:30',
                'waktu_selesai' => '13:00',
                'lokasi' => 'Lingkungan RW 04 Kelurahan Harapan Jaya',
                'penanggung_jawab_id' => $actors['perawat']->id,
                'target_kegiatan' => 30,
                'satuan_target' => 'Rumah / Lokasi',
                'indikator_keberhasilan' => '30 rumah di sekitar kasus indeks diperiksa jentik dan warga diedukasi PSN 3M Plus.',
                'status' => 'berjalan',
            ]
        );

        $k3 = Kegiatan::query()->updateOrCreate(
            ['kode_kegiatan' => 'RPK-2026-003'],
            [
                'nama_kegiatan' => 'Operasi Timbang & Pemberian PMT Lokal Balita Gizi Kurang',
                'deskripsi' => 'Pemantauan antropometri balita, konseling gizi bagi orang tua balita berisiko stunting, dan distribusi paket PMT berbahan pangan lokal.',
                'program' => 'Gizi Masyarakat',
                'jenis_dokumen' => 'RPK',
                'sumber_dana' => 'BOK Puskesmas',
                'sumber_kegiatan' => 'RPK Bulanan',
                'kode_rekening' => '5.1.02.01.01.0052',
                'pagu_anggaran' => 4500000,
                'realisasi_anggaran' => 0,
                'status_keuangan' => 'belum_ditagihkan',
                'tanggal_mulai' => $today->copy(),
                'tanggal_selesai' => $today->copy()->addDay(),
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '14:00',
                'lokasi' => 'Balai Desa Mekarsari & Posyandu Anggrek',
                'penanggung_jawab_id' => $actors['gizi']->id,
                'target_kegiatan' => 85,
                'satuan_target' => 'Balita',
                'indikator_keberhasilan' => 'Distribusi PMT tepat sasaran kepada 85 balita dan pencatatan e-PPGBM lengkap.',
                'status' => 'berjalan',
            ]
        );

        $k4 = Kegiatan::query()->updateOrCreate(
            ['kode_kegiatan' => 'RBA-2026-004'],
            [
                'nama_kegiatan' => 'Penyuluhan PHBS, UKS & Inspeksi Sanitasi Sekolah Dasar',
                'deskripsi' => 'Edukasi cuci tangan pakai sabun (CTPS), pemeriksaan kebersihan kantin sekolah, dan inspeksi kualitas air bersih sekolah.',
                'program' => 'Promosi Kesehatan & Kesling',
                'jenis_dokumen' => 'RBA',
                'sumber_dana' => 'BLUD',
                'sumber_kegiatan' => 'RPK Tahunan',
                'kode_rekening' => '5.1.02.02.01.0003',
                'pagu_anggaran' => 1500000,
                'realisasi_anggaran' => 0,
                'status_keuangan' => 'belum_ditagihkan',
                'tanggal_mulai' => $today->copy()->addDays(2),
                'tanggal_selesai' => $today->copy()->addDays(2),
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '11:30',
                'lokasi' => 'SDN 01 & SDN 02 Sukamaju',
                'penanggung_jawab_id' => $actors['promkes']->id,
                'target_kegiatan' => 15,
                'satuan_target' => 'Institusi / Desa',
                'indikator_keberhasilan' => 'Terlaksananya penyuluhan PHBS dan pemeriksaan sanitasi sekolah.',
                'status' => 'terjadwal',
            ]
        );

        $k5 = Kegiatan::query()->updateOrCreate(
            ['kode_kegiatan' => 'RUK-2026-005'],
            [
                'nama_kegiatan' => 'Lokakarya Mini Bulanan & Evaluasi Capaian SPM Puskesmas',
                'deskripsi' => 'Rapat evaluasi lintas program untuk meninjau capaian Standar Pelayanan Minimal (SPM), realisasi kegiatan BOK, dan tindak lanjut kendala lapangan.',
                'program' => 'Manajemen Puskesmas',
                'jenis_dokumen' => 'RUK',
                'sumber_dana' => 'BOK Puskesmas',
                'sumber_kegiatan' => 'Rutin Puskesmas',
                'kode_rekening' => '5.1.02.01.01.0052',
                'pagu_anggaran' => 3200000,
                'realisasi_anggaran' => 0,
                'status_keuangan' => 'belum_ditagihkan',
                'tanggal_mulai' => $today->copy()->addDays(5),
                'tanggal_selesai' => $today->copy()->addDays(5),
                'waktu_mulai' => '13:00',
                'waktu_selesai' => '16:00',
                'lokasi' => 'Aula Utama Puskesmas SIPPINTER',
                'penanggung_jawab_id' => $actors['ktu']->id,
                'target_kegiatan' => 1,
                'satuan_target' => 'Dokumen Evaluasi',
                'indikator_keberhasilan' => 'Tersusunnya matriks rencana tindak lanjut (RTL) program bulan berikutnya.',
                'status' => 'terjadwal',
            ]
        );

        $k6 = Kegiatan::query()->updateOrCreate(
            ['kode_kegiatan' => 'RPK-2026-006'],
            [
                'nama_kegiatan' => 'Pendampingan Pelaksanaan ILP di Posyandu Prima / Pustu dan Posyandu',
                'deskripsi' => 'Kunjungan rumah dan pendampingan pelaksanaan Integrasi Layanan Primer (ILP) oleh petugas Puskesmas dan kader di 12 Posyandu ILP desa wilayah kerja.',
                'program' => 'Manajemen Puskesmas',
                'jenis_dokumen' => 'RPK',
                'sumber_dana' => 'BOK Puskesmas',
                'sumber_kegiatan' => 'RPK Tahunan',
                'kode_rekening' => '5.1.02.04.01.0003',
                'pagu_anggaran' => 600000,
                'realisasi_anggaran' => 600000,
                'status_keuangan' => 'dibayar',
                'tanggal_mulai' => $today->copy()->subDays(1),
                'tanggal_selesai' => $today->copy()->addDays(3),
                'waktu_mulai' => '08:00',
                'waktu_selesai' => '13:00',
                'lokasi' => '12 Posyandu ILP (12 Desa Wilayah Kerja Puskesmas)',
                'penanggung_jawab_id' => $actors['kapus']->id,
                'target_kegiatan' => 12,
                'satuan_target' => 'Desa / Posyandu ILP',
                'indikator_keberhasilan' => 'Pendampingan ILP dan kunjungan rumah terlaksana pada 12 Posyandu ILP desa.',
                'status' => 'berjalan',
            ]
        );

        return compact('k1', 'k2', 'k3', 'k4', 'k5', 'k6');
    }

    /**
     * 4. Seed Penugasan & Anggota Pelaksana (PRD 3.5).
     *
     * @param  array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}  $actors
     * @param  array{kia: Tupoksi, gizi: Tupoksi, p2p: Tupoksi, promkes: Tupoksi, ktu: Tupoksi, ilp: Tupoksi}  $tupoksis
     * @param  array{k1: Kegiatan, k2: Kegiatan, k3: Kegiatan, k4: Kegiatan, k5: Kegiatan, k6: Kegiatan}  $kegiatans
     * @return array{p1: Penugasan, p2: Penugasan, p3: Penugasan, p4: Penugasan, p5: Penugasan}
     */
    private function seedPenugasans(array $actors, array $tupoksis, array $kegiatans): array
    {
        $today = Carbon::today();

        $p1 = Penugasan::query()->updateOrCreate(
            ['nomor_surat_tugas' => '800/ST-001/PKM/XI/2026'],
            [
                'kegiatan_id' => $kegiatans['k1']->id,
                'tupoksi_id' => $tupoksis['kia']->id,
                'pemberi_tugas_id' => $actors['kapus']->id,
                'penanggung_jawab_id' => $actors['bidan']->id,
                'instruksi_pekerjaan' => 'Laksanakan pemeriksaan ANC 10T, pastikan buku KIA terisi lengkap, dan unggah foto kegiatan serta daftar hadir peserta Kelas Ibu Hamil.',
                'sumber_kegiatan' => 'RPK Bulanan',
                'prioritas' => 'tinggi',
                'tanggal_mulai' => $today->copy()->subDays(3),
                'tenggat_waktu' => $today->copy()->subDays(2),
                'target_pekerjaan' => 45,
                'satuan' => 'Ibu Hamil',
                'indikator_keberhasilan' => '45 Ibu Hamil terlayani dan terdata di buku register KIA.',
                'status' => 'selesai',
            ]
        );
        $p1->anggotaPelaksana()->sync([
            $actors['bidan']->id => ['peran' => 'Penanggung Jawab'],
            $actors['gizi']->id => ['peran' => 'Anggota Pelaksana'],
        ]);

        $p2 = Penugasan::query()->updateOrCreate(
            ['nomor_surat_tugas' => '800/ST-002/PKM/XI/2026'],
            [
                'kegiatan_id' => $kegiatans['k2']->id,
                'tupoksi_id' => $tupoksis['p2p']->id,
                'pemberi_tugas_id' => $actors['kapus']->id,
                'penanggung_jawab_id' => $actors['perawat']->id,
                'instruksi_pekerjaan' => 'Segera turun ke RW 04 untuk Penyelidikan Epidemiologi kasus DBD, periksa minimal 30 rumah sekitar, dan laporkan indeks jentik untuk keputusan fogging.',
                'sumber_kegiatan' => 'Disposisi Pimpinan',
                'prioritas' => 'mendesak',
                'tanggal_mulai' => $today->copy(),
                'tenggat_waktu' => $today->copy()->addDay(),
                'target_pekerjaan' => 30,
                'satuan' => 'Rumah / Lokasi',
                'indikator_keberhasilan' => 'Laporan PE DBD dan pemetaan rumah positif jentik selesai dalam 24 jam.',
                'status' => 'menunggu_verifikasi',
            ]
        );
        $p2->anggotaPelaksana()->sync([
            $actors['perawat']->id => ['peran' => 'Penanggung Jawab'],
            $actors['promkes']->id => ['peran' => 'Anggota Pelaksana'],
        ]);

        $p3 = Penugasan::query()->updateOrCreate(
            ['nomor_surat_tugas' => '800/ST-003/PKM/XI/2026'],
            [
                'kegiatan_id' => $kegiatans['k3']->id,
                'tupoksi_id' => $tupoksis['gizi']->id,
                'pemberi_tugas_id' => $actors['ktu']->id,
                'penanggung_jawab_id' => $actors['gizi']->id,
                'instruksi_pekerjaan' => 'Lakukan pengukuran tinggi badan & berat badan balita, bagikan PMT lokal, serta dokumentasikan berita acara serah terima PMT.',
                'sumber_kegiatan' => 'RPK Bulanan',
                'prioritas' => 'tinggi',
                'tanggal_mulai' => $today->copy(),
                'tenggat_waktu' => $today->copy()->addDays(2),
                'target_pekerjaan' => 85,
                'satuan' => 'Balita',
                'indikator_keberhasilan' => '85 balita ditimbang dan menerima paket intervensi gizi.',
                'status' => 'sedang_berjalan',
            ]
        );
        $p3->anggotaPelaksana()->sync([
            $actors['gizi']->id => ['peran' => 'Penanggung Jawab'],
            $actors['bidan']->id => ['peran' => 'Pendamping'],
        ]);

        $p4 = Penugasan::query()->updateOrCreate(
            ['nomor_surat_tugas' => '800/ST-004/PKM/XI/2026'],
            [
                'kegiatan_id' => $kegiatans['k4']->id,
                'tupoksi_id' => $tupoksis['promkes']->id,
                'pemberi_tugas_id' => $actors['kapus']->id,
                'penanggung_jawab_id' => $actors['promkes']->id,
                'instruksi_pekerjaan' => 'Siapkan materi edukasi PHBS sekolah dan formulir inspeksi kesehatan lingkungan (IKL) sekolah dasar.',
                'sumber_kegiatan' => 'RPK Tahunan',
                'prioritas' => 'sedang',
                'tanggal_mulai' => $today->copy()->addDays(2),
                'tenggat_waktu' => $today->copy()->addDays(3),
                'target_pekerjaan' => 15,
                'satuan' => 'Institusi / Desa',
                'indikator_keberhasilan' => 'Formulir IKL terisi dan dokumentasi penyuluhan lengkap.',
                'status' => 'belum_dikerjakan',
            ]
        );
        $p4->anggotaPelaksana()->sync([
            $actors['promkes']->id => ['peran' => 'Penanggung Jawab'],
            $actors['perawat']->id => ['peran' => 'Anggota Pelaksana'],
        ]);

        $p5 = Penugasan::query()->updateOrCreate(
            ['nomor_surat_tugas' => '800/ST-005/PKM/XI/2026'],
            [
                'kegiatan_id' => $kegiatans['k6']->id,
                'tupoksi_id' => $tupoksis['ilp']->id,
                'pemberi_tugas_id' => $actors['kapus']->id,
                'penanggung_jawab_id' => $actors['kapus']->id,
                'instruksi_pekerjaan' => 'Laksanakan supervisi dan pendampingan pelaksanaan ILP serta kunjungan rumah bersama kader Posyandu di 12 desa binaan.',
                'sumber_kegiatan' => 'RPK Tahunan',
                'prioritas' => 'tinggi',
                'tanggal_mulai' => $today->copy()->subDays(1),
                'tenggat_waktu' => $today->copy()->addDays(3),
                'target_pekerjaan' => 12,
                'satuan' => 'Desa / Posyandu ILP',
                'indikator_keberhasilan' => '12 Posyandu ILP terfasilitasi dan laporan kunjungan rumah tersinkron.',
                'status' => 'sedang_berjalan',
            ]
        );
        $p5->anggotaPelaksana()->sync([
            $actors['kapus']->id => ['peran' => 'Penanggung Jawab'],
            $actors['bidan']->id => ['peran' => 'Anggota Pelaksana'],
            $actors['perawat']->id => ['peran' => 'Anggota Pelaksana'],
        ]);

        return compact('p1', 'p2', 'p3', 'p4', 'p5');
    }

    /**
     * 5. Seed Pelaksanaan Kegiatan & Dokumentasi (PRD 3.6, 3.8, 4.2).
     *
     * @param  array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}  $actors
     * @param  array{kia: Tupoksi, gizi: Tupoksi, p2p: Tupoksi, promkes: Tupoksi, ktu: Tupoksi, ilp: Tupoksi}  $tupoksis
     * @param  array{k1: Kegiatan, k2: Kegiatan, k3: Kegiatan, k4: Kegiatan, k5: Kegiatan, k6: Kegiatan}  $kegiatans
     * @param  array{p1: Penugasan, p2: Penugasan, p3: Penugasan, p4: Penugasan, p5: Penugasan}  $penugasans
     * @return array{l1: Pelaksanaan, l2: Pelaksanaan}
     */
    private function seedPelaksanaansAndDokumentasi(array $actors, array $tupoksis, array $kegiatans, array $penugasans): array
    {
        $today = Carbon::today();

        $l1 = Pelaksanaan::query()->updateOrCreate(
            ['kode_laporan' => 'LPR-MOB-202611-0001'],
            [
                'client_sync_id' => 'mob-sync-uuid-0001',
                'penugasan_id' => $penugasans['p1']->id,
                'kegiatan_id' => $kegiatans['k1']->id,
                'tupoksi_id' => $tupoksis['kia']->id,
                'pelapor_id' => $actors['bidan']->id,
                'tanggal_mulai' => $today->copy()->subDays(3),
                'tanggal_selesai' => $today->copy()->subDays(3),
                'uraian_hasil' => 'Telah dilaksanakan pelayanan ANC terpadu terhadap 45 ibu hamil di Posyandu Melati I Desa Sukamaju. Ditemukan 3 ibu hamil dengan KEK dan telah diberikan edukasi gizi serta rujukan pemeriksaan laboratorium Puskesmas.',
                'jumlah_target' => 45,
                'jumlah_realisasi' => 45,
                'satuan' => 'Ibu Hamil',
                'keterangan' => 'Target tercapai 100%, antusiasme peserta Kelas Ibu Hamil sangat baik.',
                'catatan_pegawai' => 'Perlu tindak lanjut kunjungan rumah untuk 3 ibu hamil risiko tinggi minggu depan.',
                'status_laporan' => 'disetujui',
                'sumber_input' => 'mobile_sync',
                'status_sinkronisasi' => 'tersinkron',
                'synced_at' => now()->subDays(2),
                'verified_by' => $actors['kapus']->id,
                'verified_at' => now()->subDays(2),
                'catatan_verifikasi' => 'Laporan lengkap, dokumentasi jelas, dan realisasi 45 ibu hamil telah disahkan masuk ke capaian Tupoksi KIA.',
            ]
        );

        DokumentasiKegiatan::query()->updateOrCreate(
            ['pelaksanaan_id' => $l1->id, 'nama_file' => 'Dokumentasi_ANC_Posyandu_Melati.jpg'],
            [
                'penugasan_id' => $penugasans['p1']->id,
                'uploaded_by' => $actors['bidan']->id,
                'jenis_dokumen' => 'foto',
                'file_path' => 'sneats/assets/img/backgrounds/18.jpg',
                'mime_type' => 'image/jpeg',
                'ukuran_kb' => 420,
                'keterangan' => 'Foto pemeriksaan tekanan darah & penyuluhan Kelas Ibu Hamil di Posyandu Melati I',
            ]
        );

        $l2 = Pelaksanaan::query()->updateOrCreate(
            ['kode_laporan' => 'LPR-MOB-202611-0002'],
            [
                'client_sync_id' => 'mob-sync-uuid-0002',
                'penugasan_id' => $penugasans['p2']->id,
                'kegiatan_id' => $kegiatans['k2']->id,
                'tupoksi_id' => $tupoksis['p2p']->id,
                'pelapor_id' => $actors['perawat']->id,
                'tanggal_mulai' => $today->copy(),
                'tanggal_selesai' => $today->copy(),
                'uraian_hasil' => 'Telah dilakukan Penyelidikan Epidemiologi (PE) pada 28 rumah di sekitar kasus DBD RW 04. Ditemukan 6 rumah positif jentik Aedes aegypti (HI: 21,4%) dan telah diberikan bubuk abate serta edukasi PSN.',
                'jumlah_target' => 30,
                'jumlah_realisasi' => 28,
                'satuan' => 'Rumah / Lokasi',
                'keterangan' => '2 rumah sedang kosong/terkunci saat kunjungan lapangan.',
                'catatan_pegawai' => 'Direkomendasikan pelaksanaan fogging fokus di RT 02 / RW 04.',
                'status_laporan' => 'dikirim',
                'sumber_input' => 'mobile_sync',
                'status_sinkronisasi' => 'tersinkron',
                'synced_at' => now()->subHours(1),
            ]
        );

        DokumentasiKegiatan::query()->updateOrCreate(
            ['pelaksanaan_id' => $l2->id, 'nama_file' => 'Pemeriksaan_Jentik_RW04.jpg'],
            [
                'penugasan_id' => $penugasans['p2']->id,
                'uploaded_by' => $actors['perawat']->id,
                'jenis_dokumen' => 'foto',
                'file_path' => 'sneats/assets/img/elements/18.jpg',
                'mime_type' => 'image/jpeg',
                'ukuran_kb' => 385,
                'keterangan' => 'Pemeriksaan tempat penampungan air & pembagian larvasida di RW 04',
            ]
        );

        $tupoksis['kia']->recalculateRealisasi();

        return compact('l1', 'l2');
    }

    /**
     * 5b. Seed Aturan Approval Line Berjenjang & Contoh Pengajuan Persetujuan Dana (Web & Mobile).
     *
     * @param  array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}  $actors
     * @param  array{k1: Kegiatan, k2: Kegiatan, k3: Kegiatan, k4: Kegiatan, k5: Kegiatan, k6: Kegiatan}  $kegiatans
     * @param  array{p1: Penugasan, p2: Penugasan, p3: Penugasan, p4: Penugasan, p5: Penugasan}  $penugasans
     * @param  array{l1: Pelaksanaan, l2: Pelaksanaan}  $pelaksanaans
     */
    private function seedApprovalLinesAndRequests(array $actors, array $kegiatans, array $penugasans, array $pelaksanaans): void
    {
        if (! Schema::hasTable('approval_lines') || ! Schema::hasTable('approval_requests')) {
            return;
        }

        $rules = [
            [
                'kode_aturan' => 'APR-DANA-L1',
                'nama_aturan' => 'Alur Persetujuan Pencairan Dana BOK & BLUD Puskesmas',
                'modul' => 'pencairan_dana',
                'deskripsi' => 'Verifikasi kelengkapan RAB, kode rekening BOK, dan kesesuaian Surat Tugas oleh PJ Program / Verifikator Teknis.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 1,
                'nama_level' => 'Verifikasi Teknis PJ Program UKM',
                'approver_type' => 'role_or_user',
                'approver_role' => $actors['bidan']->role ?: 'pj_program',
                'approver_user_id' => $actors['bidan']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-DANA-L2',
                'nama_aturan' => 'Alur Persetujuan Pencairan Dana BOK & BLUD Puskesmas',
                'modul' => 'pencairan_dana',
                'deskripsi' => 'Pemeriksaan ketersediaan pagu DPA, bukti SPJ, dan validasi administrasi keuangan oleh Kepala Tata Usaha / Bendahara.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 2,
                'nama_level' => 'Validasi Keuangan KTU / Bendahara BOK',
                'approver_type' => 'role_or_user',
                'approver_role' => $actors['ktu']->role ?: 'kepala_tata_usaha',
                'approver_user_id' => $actors['ktu']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-DANA-L3',
                'nama_aturan' => 'Alur Persetujuan Pencairan Dana BOK & BLUD Puskesmas',
                'modul' => 'pencairan_dana',
                'deskripsi' => 'Otorisasi akhir pencairan dana kegiatan oleh Kepala Puskesmas / Super Admin KPA.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 3,
                'nama_level' => 'Otorisasi Kepala Puskesmas / KPA',
                'approver_type' => 'role_or_user',
                'approver_role' => 'super_admin',
                'approver_user_id' => $actors['kapus']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-RPK-L1',
                'nama_aturan' => 'Persetujuan Dokumen Perencanaan RUK / RPK / RBA',
                'modul' => 'kegiatan_rpk',
                'deskripsi' => 'telaah pagu indikatif dan keselarasan indikator SPM oleh Tim Perencanaan / KTU.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 1,
                'nama_level' => 'Telaah Anggaran Tim Perencanaan (KTU)',
                'approver_type' => 'role_or_user',
                'approver_role' => $actors['ktu']->role ?: 'kepala_tata_usaha',
                'approver_user_id' => $actors['ktu']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-RPK-L2',
                'nama_aturan' => 'Persetujuan Dokumen Perencanaan RUK / RPK / RBA',
                'modul' => 'kegiatan_rpk',
                'deskripsi' => 'Pengesahan RPK Tahunan / Bulanan oleh Kepala Puskesmas.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 2,
                'nama_level' => 'Pengesahan Kepala Puskesmas',
                'approver_type' => 'role_or_user',
                'approver_role' => 'super_admin',
                'approver_user_id' => $actors['kapus']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-SPJ-L1',
                'nama_aturan' => 'Verifikasi SPJ & Kuitansi BOK (bok_spjs)',
                'modul' => 'pelaksanaan_spj',
                'deskripsi' => 'Verifikasi Pelaksana Kegiatan (Signatory Left pada bok_spjs) atas rincian transport & uang saku peserta.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 1,
                'nama_level' => 'Verifikasi Pelaksana Teknis (Signatory Left)',
                'approver_type' => 'role_or_user',
                'approver_role' => $actors['bidan']->role ?: 'user',
                'approver_user_id' => $actors['bidan']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-SPJ-L2',
                'nama_aturan' => 'Verifikasi SPJ & Kuitansi BOK (bok_spjs)',
                'modul' => 'pelaksanaan_spj',
                'deskripsi' => 'Validasi Bendahara Pengeluaran BOK (Signatory Center pada bok_spjs).',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 2,
                'nama_level' => 'Validasi Bendahara Pengeluaran (Signatory Center)',
                'approver_type' => 'role_or_user',
                'approver_role' => $actors['ktu']->role ?: 'admin',
                'approver_user_id' => $actors['ktu']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-SPJ-L3',
                'nama_aturan' => 'Verifikasi SPJ & Kuitansi BOK (bok_spjs)',
                'modul' => 'pelaksanaan_spj',
                'deskripsi' => 'Pengesahan Pejabat Pelaksana Teknis / Kepala Puskesmas (Signatory Right pada bok_spjs).',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 3,
                'nama_level' => 'Pengesahan Pimpinan / KPA (Signatory Right)',
                'approver_type' => 'role_or_user',
                'approver_role' => 'super_admin',
                'approver_user_id' => $actors['kapus']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-TUGAS-L1',
                'nama_aturan' => 'Persetujuan Surat Tugas & Aktivitas Lapangan (penugasans & bok_activities)',
                'modul' => 'penugasan',
                'deskripsi' => 'Verifikasi jadwal, sasaran lokasi desa, dan daftar petugas pelaksana oleh KTU.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 1,
                'nama_level' => 'Verifikasi Administrasi Surat Tugas (KTU)',
                'approver_type' => 'role_or_user',
                'approver_role' => $actors['ktu']->role ?: 'admin',
                'approver_user_id' => $actors['ktu']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'kode_aturan' => 'APR-TUGAS-L2',
                'nama_aturan' => 'Persetujuan Surat Tugas & Aktivitas Lapangan (penugasans & bok_activities)',
                'modul' => 'penugasan',
                'deskripsi' => 'Penandatanganan Surat Tugas oleh Kepala Puskesmas.',
                'nominal_minimal' => 0,
                'nominal_maksimal' => null,
                'step_order' => 2,
                'nama_level' => 'Otorisasi Surat Tugas Kepala Puskesmas',
                'approver_type' => 'role_or_user',
                'approver_role' => 'super_admin',
                'approver_user_id' => $actors['kapus']->id,
                'allow_web' => true,
                'allow_mobile' => true,
                'is_mandatory' => true,
                'is_active' => true,
            ],
        ];

        $ruleModels = [];
        foreach ($rules as $r) {
            $ruleModels[$r['kode_aturan']] = ApprovalLine::query()->updateOrCreate(
                ['kode_aturan' => $r['kode_aturan']],
                $r
            );
        }

        // Pengajuan 1: Sudah Disetujui Penuh (3 Tahap)
        $req1 = ApprovalRequest::query()->updateOrCreate(
            ['nomor_pengajuan' => 'APR-202611-0001'],
            [
                'judul_pengajuan' => 'Pencairan Dana Transport & Konsumsi Posyandu Balita & Kelas Ibu Hamil',
                'modul' => 'pencairan_dana',
                'referensi_tipe' => Kegiatan::class,
                'referensi_id' => $kegiatans['k1']->id,
                'kegiatan_id' => $kegiatans['k1']->id,
                'penugasan_id' => $penugasans['p1']->id,
                'pelaksanaan_id' => $pelaksanaans['l1']->id,
                'pengaju_id' => $actors['bidan']->id,
                'nominal_diajukan' => 4500000,
                'nominal_disetujui' => 4500000,
                'sumber_dana' => 'BOK Puskesmas',
                'kode_rekening' => '5.1.02.04.01.0003',
                'uraian_pengajuan' => 'Pencairan transport kader & petugas kesehatan (12 Posyandu ILP) beserta konsumsi penyuluhan Kelas Ibu Hamil.',
                'current_step' => 3,
                'total_steps' => 3,
                'status' => 'disetujui',
                'sumber_pengajuan' => 'web',
                'submitted_at' => now()->subDays(2),
                'completed_at' => now()->subDay(),
                'catatan_akhir' => 'Seluruh dokumen SPJ dan daftar hadir lengkap, disetujui untuk dicairkan.',
            ]
        );

        foreach ([1 => 'APR-DANA-L1', 2 => 'APR-DANA-L2', 3 => 'APR-DANA-L3'] as $stepOrder => $kodeRule) {
            $rule = $ruleModels[$kodeRule];
            ApprovalRequestStep::query()->updateOrCreate(
                ['approval_request_id' => $req1->id, 'step_order' => $stepOrder],
                [
                    'approval_line_id' => $rule->id,
                    'nama_level' => $rule->nama_level,
                    'approver_role' => $rule->approver_role,
                    'assigned_approver_id' => $rule->approver_user_id,
                    'acted_by' => $rule->approver_user_id,
                    'status' => 'disetujui',
                    'acted_via' => $stepOrder === 2 ? 'mobile' : 'web',
                    'catatan' => 'Sesuai pagu RPK & bukti kegiatan terverifikasi.',
                    'acted_at' => now()->subHours(30 - ($stepOrder * 6)),
                ]
            );
        }

        // Pengajuan 2: Sedang Dalam Proses (Tahap 1 Disetujui via Mobile, Menunggu Tahap 2 & 3)
        $req2 = ApprovalRequest::query()->updateOrCreate(
            ['nomor_pengajuan' => 'APR-202611-0002'],
            [
                'judul_pengajuan' => 'Pencairan Dana Operasional PE DBD & Larvasidasi RW 04',
                'modul' => 'pencairan_dana',
                'referensi_tipe' => Kegiatan::class,
                'referensi_id' => $kegiatans['k2']->id,
                'kegiatan_id' => $kegiatans['k2']->id,
                'penugasan_id' => $penugasans['p2']->id,
                'pelaksanaan_id' => $pelaksanaans['l2']->id,
                'pengaju_id' => $actors['perawat']->id,
                'nominal_diajukan' => 3200000,
                'nominal_disetujui' => null,
                'sumber_dana' => 'BOK Puskesmas',
                'kode_rekening' => '5.1.02.04.01.0003',
                'uraian_pengajuan' => 'Pengajuan dana transport tim surveilans epidemiologi & operasional fogging fokus RW 04 Kelurahan Harapan Jaya.',
                'current_step' => 2,
                'total_steps' => 3,
                'status' => 'dalam_proses',
                'sumber_pengajuan' => 'mobile',
                'submitted_at' => now()->subHours(5),
                'completed_at' => null,
                'catatan_akhir' => 'Tahap 1 telah diverifikasi oleh PJ Program.',
            ]
        );

        foreach ([1 => 'APR-DANA-L1', 2 => 'APR-DANA-L2', 3 => 'APR-DANA-L3'] as $stepOrder => $kodeRule) {
            $rule = $ruleModels[$kodeRule];
            ApprovalRequestStep::query()->updateOrCreate(
                ['approval_request_id' => $req2->id, 'step_order' => $stepOrder],
                [
                    'approval_line_id' => $rule->id,
                    'nama_level' => $rule->nama_level,
                    'approver_role' => $rule->approver_role,
                    'assigned_approver_id' => $rule->approver_user_id,
                    'acted_by' => $stepOrder === 1 ? $rule->approver_user_id : null,
                    'status' => $stepOrder === 1 ? 'disetujui' : 'menunggu',
                    'acted_via' => $stepOrder === 1 ? 'mobile' : null,
                    'catatan' => $stepOrder === 1 ? 'Verifikasi teknis lapangan sesuai.' : null,
                    'acted_at' => $stepOrder === 1 ? now()->subHours(2) : null,
                ]
            );
        }

        // Pengajuan 3: Menunggu Tahap 1 (Pengajuan PMT Lokal Gizi)
        $req3 = ApprovalRequest::query()->updateOrCreate(
            ['nomor_pengajuan' => 'APR-202611-0003'],
            [
                'judul_pengajuan' => 'Pencairan Belanja Bahan PMT Lokal Balita Gizi Kurang (150 Balita)',
                'modul' => 'pencairan_dana',
                'referensi_tipe' => Kegiatan::class,
                'referensi_id' => $kegiatans['k3']->id,
                'kegiatan_id' => $kegiatans['k3']->id,
                'penugasan_id' => $penugasans['p3']->id,
                'pelaksanaan_id' => null,
                'pengaju_id' => $actors['gizi']->id,
                'nominal_diajukan' => 11250000,
                'nominal_disetujui' => null,
                'sumber_dana' => 'BOK Puskesmas',
                'kode_rekening' => '5.1.02.01.01.0052',
                'uraian_pengajuan' => 'Pengajuan dana belanja bahan makanan tambahan lokal berbasis pangan lokal tinggi protein hewani untuk 150 sasaran balita.',
                'current_step' => 1,
                'total_steps' => 3,
                'status' => 'menunggu',
                'sumber_pengajuan' => 'web',
                'submitted_at' => now()->subHour(),
                'completed_at' => null,
                'catatan_akhir' => null,
            ]
        );

        foreach ([1 => 'APR-DANA-L1', 2 => 'APR-DANA-L2', 3 => 'APR-DANA-L3'] as $stepOrder => $kodeRule) {
            $rule = $ruleModels[$kodeRule];
            ApprovalRequestStep::query()->updateOrCreate(
                ['approval_request_id' => $req3->id, 'step_order' => $stepOrder],
                [
                    'approval_line_id' => $rule->id,
                    'nama_level' => $rule->nama_level,
                    'approver_role' => $rule->approver_role,
                    'assigned_approver_id' => $rule->approver_user_id,
                    'acted_by' => null,
                    'status' => 'menunggu',
                    'acted_via' => null,
                    'catatan' => null,
                    'acted_at' => null,
                ]
            );
        }
    }

    /**
     * 6. Seed Notifikasi Pegawai secara idempotent (PRD 3.9).
     *
     * @param  array{kapus: User, ktu: User, bidan: User, perawat: User, gizi: User, promkes: User}  $actors
     * @param  array{k1: Kegiatan, k2: Kegiatan, k3: Kegiatan, k4: Kegiatan, k5: Kegiatan, k6: Kegiatan}  $kegiatans
     * @param  array{p1: Penugasan, p2: Penugasan, p3: Penugasan, p4: Penugasan, p5: Penugasan}  $penugasans
     * @param  array{l1: Pelaksanaan, l2: Pelaksanaan}  $pelaksanaans
     */
    private function seedNotifikasis(array $actors, array $kegiatans, array $penugasans, array $pelaksanaans): void
    {
        $notifications = [
            [
                'user_id' => $actors['kapus']->id,
                'penugasan_id' => $penugasans['p2']->id,
                'kegiatan_id' => $kegiatans['k2']->id,
                'pelaksanaan_id' => $pelaksanaans['l2']->id,
                'jenis_notifikasi' => 'laporan_diterima',
                'judul' => 'Laporan Baru Menunggu Verifikasi: PE & Pemantauan Jentik DBD RW 04',
                'pesan' => $actors['perawat']->name.' telah mengirimkan laporan pelaksanaan LPR-MOB-202611-0002 dari aplikasi untuk diverifikasi.',
                'url_tujuan' => '/pelaksanaan/'.$pelaksanaans['l2']->id,
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'user_id' => $actors['kapus']->id,
                'penugasan_id' => $penugasans['p3']->id,
                'kegiatan_id' => $kegiatans['k3']->id,
                'pelaksanaan_id' => null,
                'jenis_notifikasi' => 'pengingat_tenggat',
                'judul' => 'Monitoring Kegiatan Hari Ini: Operasi Timbang & PMT Lokal',
                'pesan' => 'Kegiatan Operasi Timbang & Pemberian PMT Lokal Balita Gizi Kurang sedang berjalan di Balai Desa Mekarsari.',
                'url_tujuan' => '/penugasan/'.$penugasans['p3']->id,
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'user_id' => $actors['bidan']->id,
                'penugasan_id' => $penugasans['p1']->id,
                'kegiatan_id' => $kegiatans['k1']->id,
                'pelaksanaan_id' => $pelaksanaans['l1']->id,
                'jenis_notifikasi' => 'laporan_disetujui',
                'judul' => 'Laporan Disetujui: Pelayanan Posyandu Balita & Kelas Ibu Hamil',
                'pesan' => 'Laporan LPR-MOB-202611-0001 telah disetujui oleh Kepala Puskesmas dan realisasi Tupoksi Anda bertambah +45 Ibu Hamil.',
                'url_tujuan' => '/pelaksanaan/'.$pelaksanaans['l1']->id,
                'is_read' => false,
                'read_at' => null,
            ],
            [
                'user_id' => $actors['perawat']->id,
                'penugasan_id' => $penugasans['p2']->id,
                'kegiatan_id' => $kegiatans['k2']->id,
                'pelaksanaan_id' => null,
                'jenis_notifikasi' => 'disposisi_baru',
                'judul' => 'Disposisi Baru (Mendesak): Penyelidikan Epidemiologi DBD RW 04',
                'pesan' => 'Kepala Puskesmas memberikan disposisi mendesak untuk pelaksanaan PE DBD di RW 04 Kelurahan Harapan Jaya.',
                'url_tujuan' => '/penugasan/'.$penugasans['p2']->id,
                'is_read' => true,
                'read_at' => now()->subHours(3),
            ],
            [
                'user_id' => $actors['promkes']->id,
                'penugasan_id' => $penugasans['p4']->id,
                'kegiatan_id' => $kegiatans['k4']->id,
                'pelaksanaan_id' => null,
                'jenis_notifikasi' => 'penugasan_baru',
                'judul' => 'Penugasan Baru: Penyuluhan PHBS & Inspeksi Sanitasi Sekolah Dasar',
                'pesan' => 'Anda ditugaskan sebagai Penanggung Jawab kegiatan Penyuluhan PHBS di SDN 01 & SDN 02 Sukamaju.',
                'url_tujuan' => '/penugasan/'.$penugasans['p4']->id,
                'is_read' => false,
                'read_at' => null,
            ],
        ];

        foreach ($notifications as $notif) {
            NotifikasiPegawai::query()->updateOrCreate(
                [
                    'user_id' => $notif['user_id'],
                    'jenis_notifikasi' => $notif['jenis_notifikasi'],
                    'judul' => $notif['judul'],
                ],
                $notif
            );
        }
    }
}
