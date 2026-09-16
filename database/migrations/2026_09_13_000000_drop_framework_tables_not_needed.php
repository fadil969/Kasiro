<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Database minimal: tabel framework tidak dipakai lagi karena
 * SESSION_DRIVER=file, CACHE_STORE=file, QUEUE_CONNECTION=sync.
 * Tidak ada fitur reset password / notifikasi / cache-lock DB.
 *
 * Role non-admin disederhanakan dari 'kasir' menjadi 'user' (spesifikasi: user & admin).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_reset_tokens');

        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'kasir')->update(['role' => 'user']);
            // default kolom role: 'kasir' (lama) -> 'user'
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('user')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'user')->update(['role' => 'kasir']);
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('kasir')->change();
            });
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'user')->update(['role' => 'kasir']);
        }
    }
};
