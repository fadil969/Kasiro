<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Kategori default — HARUS di-seed sebelum MenuSeeder
     * (menus.category merujuk ke categories.name lewat FK).
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan', 'status' => 'Aktif'],
            ['name' => 'Minuman', 'status' => 'Aktif'],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
