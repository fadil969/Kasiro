<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    /**
     * Kategori khusus domain pengeluaran — SENGAJA tetap daftar konstan,
     * bukan dari tabel categories (itu domain kategori menu).
     */
    public const CATEGORIES = ['Operasional', 'Bahan Baku', 'Utilitas', 'Sewa'];

    protected $fillable = [
        'date',
        'description',
        'category',
        'amount',
    ];

    protected $casts = [
        'date'   => 'date:Y-m-d',
        'amount' => 'integer',
    ];
}
