<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pengeluaran — wadah kosong, hanya admin yang mengisi lewat
 * halaman Catat Pengeluaran. Kategori pengeluaran (Operasional, Bahan Baku,
 * Utilitas, Sewa) adalah domain tersendiri, SENGAJA tidak di-FK ke categories
 * (itu domain kategori menu).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('description', 100);
            $table->string('category', 20)->index();
            $table->unsignedInteger('amount'); // Rupiah, tanpa desimal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
