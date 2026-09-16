<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel kategori menu — sumber kebenaran daftar kategori.
 * menus.category merujuk categories.name (natural key) lewat FK:
 * - onUpdate cascade : rename kategori otomatis mengikut-sertakan semua menu.
 * - onDelete restrict : kategori yang masih dipakai menu tidak bisa dihapus
 *   (CategoryController juga memeriksa ini supaya pesan error ramah pengguna).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique(); // sama lebar dengan menus.category (kolom FK)
            $table->string('status', 10)->default('Aktif')->index(); // Aktif | Nonaktif
            $table->timestamps();
        });

        // Backfill untuk database lama: setiap kategori yang sudah terpakai di
        // menus didaftarkan (created_at = menu pertama kategori itu) sebelum FK dipasang.
        if (Schema::hasTable('menus')) {
            $used = DB::table('menus')
                ->select('category', DB::raw('MIN(created_at) as first_created'))
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->groupBy('category')
                ->get();

            foreach ($used as $row) {
                DB::table('categories')->updateOrInsert(
                    ['name' => $row->category],
                    [
                        'status'     => 'Aktif',
                        'created_at' => $row->first_created,
                        'updated_at' => now(),
                    ]
                );
            }
        }

        Schema::table('menus', function (Blueprint $table) {
            $table->foreign('category')->references('name')->on('categories')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['category']);
        });
        Schema::dropIfExists('categories');
    }
};
