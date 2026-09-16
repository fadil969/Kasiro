<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel menu untuk halaman admin (kelola) dan kasir (jualan).
 * Gambar SENGAJA tidak disimpan di database: gambar dilayani dari file lokal
 * public/images/menu/<slug-nama>.svg (lihat Menu::imageUrl()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('category', 20)->index(); // Makanan | Minuman
            $table->unsignedInteger('price');        // harga jual (Rupiah, tanpa desimal)
            $table->unsignedInteger('cost')->default(0); // harga modal
            $table->string('status', 10)->default('Aktif')->index(); // Aktif | Nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
