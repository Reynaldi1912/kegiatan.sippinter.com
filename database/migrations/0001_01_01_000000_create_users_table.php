<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core tables shared with the main SIPPINTER database (`sippinter_new`).
 *
 * On `sippinter_new` these tables already exist, so every create is guarded and
 * becomes a no-op. On a fresh database (e.g. the SQLite test database) the
 * `users` table is created with the same core structure as SIPPINTER
 * (`username` + `fullname`), so the application behaves identically in both.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('username');
                $table->string('fullname')->nullable();
                $table->string('role')->default('user');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally left empty: these tables belong to the shared SIPPINTER
     * database and must never be dropped by this application's rollback.
     */
    public function down(): void
    {
        //
    }
};
