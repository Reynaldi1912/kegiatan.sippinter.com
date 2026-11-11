<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Aturan Approval Line (Konfigurasi Level Berjenjang untuk Dana, RPK, Pelaksanaan, SPJ, dll.)
        if (! Schema::hasTable('approval_lines')) {
            Schema::create('approval_lines', function (Blueprint $table) {
                $table->id();
                $table->string('kode_aturan')->unique();
                $table->string('nama_aturan');
                $table->string('modul')->default('pencairan_dana'); // pencairan_dana, kegiatan_rpk, pelaksanaan_spj, penugasan
                $table->text('deskripsi')->nullable();
                $table->decimal('nominal_minimal', 15, 2)->default(0);
                $table->decimal('nominal_maksimal', 15, 2)->nullable();
                $table->unsignedTinyInteger('step_order')->default(1);
                $table->string('nama_level'); // Misal: Verifikasi PJ Program, Verifikasi KTU / PPK, Persetujuan Kepala Puskesmas
                $table->string('approver_type')->default('role_or_user'); // role, user, role_or_user
                $table->string('approver_role')->nullable();
                $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('allow_web')->default(true);
                $table->boolean('allow_mobile')->default(true);
                $table->boolean('is_mandatory')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['modul', 'is_active', 'step_order']);
            });
        }

        // 2. Transaksi Pengajuan Persetujuan (Dana Kegiatan, Anggaran RPK, SPJ Pelaksanaan, dll.)
        if (! Schema::hasTable('approval_requests')) {
            Schema::create('approval_requests', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_pengajuan')->unique();
                $table->string('judul_pengajuan');
                $table->string('modul')->default('pencairan_dana'); // pencairan_dana, kegiatan_rpk, pelaksanaan_spj, penugasan
                $table->string('referensi_tipe')->nullable(); // App\Models\Kegiatan, App\Models\Pelaksanaan, dll.
                $table->unsignedBigInteger('referensi_id')->nullable();
                $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatans')->nullOnDelete();
                $table->foreignId('penugasan_id')->nullable()->constrained('penugasans')->nullOnDelete();
                $table->foreignId('pelaksanaan_id')->nullable()->constrained('pelaksanaans')->nullOnDelete();
                $table->foreignId('pengaju_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('nominal_diajukan', 15, 2)->default(0);
                $table->decimal('nominal_disetujui', 15, 2)->nullable();
                $table->string('sumber_dana')->default('BOK Puskesmas');
                $table->string('kode_rekening')->nullable();
                $table->text('uraian_pengajuan')->nullable();
                $table->unsignedTinyInteger('current_step')->default(1);
                $table->unsignedTinyInteger('total_steps')->default(1);
                $table->string('status')->default('menunggu'); // menunggu, dalam_proses, disetujui, ditolak, revisi
                $table->string('sumber_pengajuan')->default('web'); // web, mobile
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->text('catatan_akhir')->nullable();
                $table->timestamps();

                $table->index(['modul', 'status', 'current_step']);
            });
        }

        // 3. Tahapan / Log Persetujuan per Level (Bisa dieksekusi via Web maupun Mobile API)
        if (! Schema::hasTable('approval_request_steps')) {
            Schema::create('approval_request_steps', function (Blueprint $table) {
                $table->id();
                $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
                $table->foreignId('approval_line_id')->nullable()->constrained('approval_lines')->nullOnDelete();
                $table->unsignedTinyInteger('step_order')->default(1);
                $table->string('nama_level');
                $table->string('approver_role')->nullable();
                $table->foreignId('assigned_approver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('menunggu'); // menunggu, disetujui, ditolak, revisi
                $table->string('acted_via')->nullable(); // web, mobile
                $table->text('catatan')->nullable();
                $table->timestamp('acted_at')->nullable();
                $table->timestamps();

                $table->index(['approval_request_id', 'step_order', 'status'], 'idx_app_req_step_status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_steps');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_lines');
    }
};
