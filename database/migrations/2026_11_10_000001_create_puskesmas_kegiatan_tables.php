<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kegiatan SIPPINTER module tables for the shared `sippinter_new` database.
 *
 * Only missing columns/tables are created. Existing SIPPINTER structures
 * (`users.username`, `users.fullname`, `users.role`, `app_settings`) are reused.
 */
return new class extends Migration
{
    /**
     * Columns this module adds to the shared `users` table.
     *
     * @var list<string>
     */
    private array $userColumns = [
        'email',
        'nip',
        'jabatan',
        'unit_program',
        'phone',
        'avatar',
        'is_active',
        'api_token',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addMissingUserColumns();

        if (! Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tupoksis')) {
            Schema::create('tupoksis', function (Blueprint $table) {
                $table->id();
                $table->string('kode_tupoksi', 50)->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('nama_tupoksi');
                $table->text('uraian_tugas');
                $table->unsignedInteger('target')->default(1);
                $table->unsignedInteger('realisasi')->default(0);
                $table->string('satuan', 100)->default('Kegiatan');
                $table->text('indikator_keberhasilan')->nullable();
                $table->string('periode', 100)->default('Tahun 2026');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'periode']);
            });
        }

        if (! Schema::hasTable('kegiatans')) {
            Schema::create('kegiatans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_kegiatan', 50)->unique();
                $table->string('nama_kegiatan');
                $table->text('deskripsi')->nullable();
                $table->string('program', 150);
                $table->string('jenis_dokumen', 50)->default('RPK')->comment('RUK, RPK, RBA');
                $table->string('sumber_dana', 100)->default('BOK Puskesmas');
                $table->string('sumber_kegiatan', 100)->default('RPK Tahunan');
                $table->string('kode_rekening', 50)->nullable();
                $table->decimal('pagu_anggaran', 15, 2)->default(0);
                $table->decimal('realisasi_anggaran', 15, 2)->default(0);
                $table->string('status_keuangan', 50)->default('belum_ditagihkan')->comment('belum_ditagihkan, proses_penagihan, dibayar');
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->string('waktu_mulai', 20)->default('08:00');
                $table->string('waktu_selesai', 20)->default('12:00');
                $table->string('lokasi');
                $table->foreignId('penanggung_jawab_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedInteger('target_kegiatan')->default(1);
                $table->string('satuan_target', 100)->default('Kegiatan');
                $table->text('indikator_keberhasilan')->nullable();
                $table->string('status', 50)->default('terjadwal')->comment('terjadwal, berjalan, selesai, dibatalkan');
                $table->timestamps();

                $table->index(['tanggal_mulai', 'status']);
                $table->index('program');
            });
        }

        if (! Schema::hasTable('penugasans')) {
            Schema::create('penugasans', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_surat_tugas', 100)->unique();
                $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
                $table->foreignId('tupoksi_id')->nullable()->constrained('tupoksis')->nullOnDelete();
                $table->foreignId('pemberi_tugas_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('penanggung_jawab_id')->constrained('users')->cascadeOnDelete();
                $table->text('instruksi_pekerjaan');
                $table->string('sumber_kegiatan', 100)->default('RPK Puskesmas');
                $table->string('prioritas', 50)->default('sedang')->comment('rendah, sedang, tinggi, mendesak');
                $table->date('tanggal_mulai');
                $table->date('tenggat_waktu');
                $table->unsignedInteger('target_pekerjaan')->default(1);
                $table->string('satuan', 100)->default('Kegiatan');
                $table->text('indikator_keberhasilan')->nullable();
                $table->string('status', 50)->default('belum_dikerjakan')->comment('belum_dikerjakan, sedang_berjalan, menunggu_verifikasi, perlu_perbaikan, selesai');
                $table->timestamps();

                $table->index(['status', 'prioritas']);
                $table->index('penanggung_jawab_id');
            });
        }

        if (! Schema::hasTable('penugasan_anggota')) {
            Schema::create('penugasan_anggota', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penugasan_id')->constrained('penugasans')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('peran', 100)->default('Anggota Pelaksana')->comment('Penanggung Jawab, Anggota Pelaksana, Pendamping');
                $table->timestamps();

                $table->unique(['penugasan_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('pelaksanaans')) {
            Schema::create('pelaksanaans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_laporan', 80)->unique();
                $table->string('client_sync_id', 100)->nullable()->unique();
                $table->foreignId('penugasan_id')->constrained('penugasans')->cascadeOnDelete();
                $table->foreignId('kegiatan_id')->constrained('kegiatans')->cascadeOnDelete();
                $table->foreignId('tupoksi_id')->nullable()->constrained('tupoksis')->nullOnDelete();
                $table->foreignId('pelapor_id')->constrained('users')->cascadeOnDelete();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->text('uraian_hasil');
                $table->unsignedInteger('jumlah_target')->default(1);
                $table->unsignedInteger('jumlah_realisasi')->default(0);
                $table->string('satuan', 100)->default('Kegiatan');
                $table->text('keterangan')->nullable();
                $table->text('catatan_pegawai')->nullable();
                $table->string('status_laporan', 50)->default('draft')->comment('draft, dikirim, diterima, perlu_perbaikan, disetujui');
                $table->string('sumber_input', 50)->default('web')->comment('web, mobile_sync');
                $table->string('status_sinkronisasi', 50)->default('tersinkron')->comment('tersinkron, menunggu_sinkron, gagal_sinkron');
                $table->timestamp('synced_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable();
                $table->text('catatan_verifikasi')->nullable();
                $table->timestamps();

                $table->index(['penugasan_id', 'status_laporan']);
                $table->index('pelapor_id');
            });
        }

        if (! Schema::hasTable('dokumentasi_kegiatans')) {
            Schema::create('dokumentasi_kegiatans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pelaksanaan_id')->nullable()->constrained('pelaksanaans')->cascadeOnDelete();
                $table->foreignId('penugasan_id')->nullable()->constrained('penugasans')->cascadeOnDelete();
                $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
                $table->string('jenis_dokumen', 50)->default('foto')->comment('foto, dokumen, lampiran_pendukung');
                $table->string('nama_file');
                $table->string('file_path');
                $table->string('mime_type', 100)->nullable();
                $table->unsignedInteger('ukuran_kb')->default(0);
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifikasi_pegawais')) {
            Schema::create('notifikasi_pegawais', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('penugasan_id')->nullable()->constrained('penugasans')->cascadeOnDelete();
                $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatans')->cascadeOnDelete();
                $table->foreignId('pelaksanaan_id')->nullable()->constrained('pelaksanaans')->cascadeOnDelete();
                $table->string('jenis_notifikasi', 100);
                $table->string('judul');
                $table->text('pesan');
                $table->string('url_tujuan')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_read']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Drops only the module's own tables and the user columns it introduced.
     * Shared SIPPINTER tables (`users`, `app_settings`) are left untouched.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi_pegawais');
        Schema::dropIfExists('dokumentasi_kegiatans');
        Schema::dropIfExists('pelaksanaans');
        Schema::dropIfExists('penugasan_anggota');
        Schema::dropIfExists('penugasans');
        Schema::dropIfExists('kegiatans');
        Schema::dropIfExists('tupoksis');

        foreach (['users_email_unique', 'users_nip_unique', 'users_api_token_unique'] as $index) {
            if (Schema::hasIndex('users', $index)) {
                Schema::table('users', fn (Blueprint $table) => $table->dropUnique($index));
            }
        }

        $existingColumns = array_values(array_filter(
            $this->userColumns,
            fn (string $column): bool => Schema::hasColumn('users', $column)
        ));

        if ($existingColumns !== []) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn($existingColumns));
        }
    }

    /**
     * Add the module's user columns that do not exist yet on the shared `users` table.
     */
    private function addMissingUserColumns(): void
    {
        $definitions = [
            'email' => fn (Blueprint $table) => $table->string('email')->nullable()->unique(),
            'nip' => fn (Blueprint $table) => $table->string('nip', 50)->nullable()->unique(),
            'jabatan' => fn (Blueprint $table) => $table->string('jabatan', 150)->nullable(),
            'unit_program' => fn (Blueprint $table) => $table->string('unit_program', 150)->nullable(),
            'phone' => fn (Blueprint $table) => $table->string('phone', 50)->nullable(),
            'avatar' => fn (Blueprint $table) => $table->string('avatar')->nullable(),
            'is_active' => fn (Blueprint $table) => $table->boolean('is_active')->default(true),
            'api_token' => fn (Blueprint $table) => $table->string('api_token', 100)->nullable()->unique(),
        ];

        foreach ($definitions as $column => $definition) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', $definition);
            }
        }
    }
};
