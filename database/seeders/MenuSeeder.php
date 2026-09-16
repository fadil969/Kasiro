<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Menu default — sama dengan data dummy sebelumnya.
     * Gambar tidak disentuh: otomatis dikenali dari file lokal
     * public/images/menu/<slug-nama>.svg.
     */
    public function run(): void
    {
        $menus = [
            ['name' => 'Nasi Goreng',          'category' => 'Makanan', 'price' => 15000, 'cost' => 7000,  'status' => 'Aktif'],
            ['name' => 'Capjay',               'category' => 'Makanan', 'price' => 18000, 'cost' => 8000,  'status' => 'Aktif'],
            ['name' => 'Bakmi Godog',          'category' => 'Makanan', 'price' => 14000, 'cost' => 6000,  'status' => 'Aktif'],
            ['name' => 'Nasi Goreng Spesial',  'category' => 'Makanan', 'price' => 22000, 'cost' => 10000, 'status' => 'Aktif'],
            ['name' => 'Mie Goreng',           'category' => 'Makanan', 'price' => 15000, 'cost' => 7000,  'status' => 'Aktif'],
            ['name' => 'Soto Ayam',            'category' => 'Makanan', 'price' => 17000, 'cost' => 7500,  'status' => 'Aktif'],
            ['name' => 'Es Teh',               'category' => 'Minuman', 'price' => 5000,  'cost' => 1500,  'status' => 'Aktif'],
            ['name' => 'Es Jeruk',             'category' => 'Minuman', 'price' => 6000,  'cost' => 2000,  'status' => 'Aktif'],
            ['name' => 'Kopi Panas',           'category' => 'Minuman', 'price' => 7000,  'cost' => 2500,  'status' => 'Aktif'],
            ['name' => 'Es Kopi Susu',         'category' => 'Minuman', 'price' => 8000,  'cost' => 3000,  'status' => 'Nonaktif'],
            ['name' => 'Air Mineral',          'category' => 'Minuman', 'price' => 4000,  'cost' => 1500,  'status' => 'Aktif'],
            ['name' => 'Jus Alpukat',          'category' => 'Minuman', 'price' => 12000, 'cost' => 5000,  'status' => 'Aktif'],
        ];

        foreach ($menus as $data) {
            Menu::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
