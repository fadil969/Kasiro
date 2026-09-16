<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database minimal — satu-satunya tabel aplikasi: `users` (login via username + role admin|user).
 * Tidak ada kolom email: autentikasi sepenuhnya memakai `username`.
 * Session/cache/queue memakai driver file/sync sehingga tidak ada tabel framework.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username', 50)->unique(); // kunci login
            $table->string('password');
            $table->string('role', 20)->default('user')->index(); // admin | user
            $table->string('status', 10)->default('Aktif'); // Aktif | Nonaktif
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
