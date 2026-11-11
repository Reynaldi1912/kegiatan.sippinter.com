<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('chart_of_accounts')) {
            return;
        }

        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 50)->unique()->comment('Kode Rekening Akun SIPD');
            $table->string('nama_akun', 255)->comment('Deskripsi/Nama Akun Belanja');
            $table->string('kategori', 100)->default('Belanja Daerah');
            $table->unsignedTinyInteger('level')->default(1)->comment('Kedalaman hierarki (1-6)');
            $table->string('parent_kode', 50)->nullable()->index()->comment('Kode rekening induk');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('kategori');
            $table->index('level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
