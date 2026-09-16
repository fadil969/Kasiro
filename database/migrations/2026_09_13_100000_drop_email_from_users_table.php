<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Login cukup dengan username: kolom `email` (dan `email_verified_at`) dihapus
 * dari tabel `users`, dan `username` dijadikan NOT NULL sebagai kunci identitas unik.
 *
 * Data akun lama tetap aman: setiap baris sudah memiliki username unik
 * (dijaga oleh unique index users_username_unique yang sudah ada).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Baseline fresh-install sudah tidak punya email; migration ini hanya untuk
        // database lama yang masih memiliki kolom email. Guard agar idempoten.
        if (! Schema::hasColumn('users', 'email')) {
            return;
        }

        // Jaga-jaga: username yang masih kosong diisi dari prefix email / nama,
        // supaya bisa dijadikan NOT NULL tanpa kehilangan baris.
        DB::table('users')->whereNull('username')->orWhere('username', '')->orderBy('id')->get()
            ->each(function ($u) {
                $base = $u->email
                    ? strtolower(preg_replace('/[^a-z0-9]/', '', strtolower(explode('@', $u->email)[0])))
                    : strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($u->name)));
                $username = $base !== '' ? $base : 'user' . $u->id;
                $i = 1;
                while (DB::table('users')->where('username', $username)->where('id', '!=', $u->id)->exists()) {
                    $username = $base . ++$i;
                }
                DB::table('users')->where('id', $u->id)->update(['username' => $username]);
            });

        // Jadikan username NOT NULL (sertakan unique hanya jika index-nya belum ada,
        // mis. DB lama dari baseline yang unique index username sudah terpasang).
        if ($this->usernameIsNullable()) {
            Schema::table('users', function (Blueprint $table) {
                $blueprint = $table->string('username', 50)->nullable(false);
                if (! Schema::hasIndex('users', 'users_username_unique')) {
                    $blueprint = $blueprint->unique();
                }
                $blueprint->change();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(array_values(array_filter(
                ['email', 'email_verified_at'],
                fn ($c) => Schema::hasColumn('users', $c)
            )));
        });
    }

    private function usernameIsNullable(): bool
    {
        foreach (Schema::getColumns('users') as $col) {
            if ($col['name'] === 'username') {
                return (bool) $col['nullable'];
            }
        }

        return false;
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->after('username');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // kembalikan email turunan dari username (format lama <username>@kasiro.test)
        DB::table('users')->orderBy('id')->get()->each(function ($u) {
            DB::table('users')->where('id', $u->id)->update(['email' => $u->username . '@kasiro.test']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};
