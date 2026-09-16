<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
            'status' => 'Aktif',
        ]);

        // kategori dulu — menus.category merujuk categories.name (FK)
        Category::create(['name' => 'Makanan', 'status' => 'Aktif']);
        Category::create(['name' => 'Minuman', 'status' => 'Aktif']);

        // 12 menu: bervariasi untuk menguji urutan & paginasi
        $data = [
            ['name' => 'Nasi Goreng',         'category' => 'Makanan', 'price' => 15000, 'cost' => 7000],
            ['name' => 'Capjay',              'category' => 'Makanan', 'price' => 18000, 'cost' => 8000],
            ['name' => 'Bakmi Godog',         'category' => 'Makanan', 'price' => 14000, 'cost' => 6000],
            ['name' => 'Nasi Goreng Spesial', 'category' => 'Makanan', 'price' => 22000, 'cost' => 10000],
            ['name' => 'Mie Goreng',          'category' => 'Makanan', 'price' => 15000, 'cost' => 7000],
            ['name' => 'Soto Ayam',           'category' => 'Makanan', 'price' => 17000, 'cost' => 7500],
            ['name' => 'Es Teh',              'category' => 'Minuman', 'price' => 5000,  'cost' => 1500],
            ['name' => 'Es Jeruk',            'category' => 'Minuman', 'price' => 6000,  'cost' => 2000],
            ['name' => 'Kopi Panas',          'category' => 'Minuman', 'price' => 7000,  'cost' => 2500],
            ['name' => 'Es Kopi Susu',        'category' => 'Minuman', 'price' => 8000,  'cost' => 3000, 'status' => 'Nonaktif'],
            ['name' => 'Air Mineral',         'category' => 'Minuman', 'price' => 4000,  'cost' => 1500],
            ['name' => 'Jus Alpukat',         'category' => 'Minuman', 'price' => 12000, 'cost' => 5000],
        ];
        foreach ($data as $m) {
            Menu::create($m + ['status' => $m['status'] ?? 'Aktif']);
        }

        $this->actingAs($this->admin);
    }

    private User $admin;

    private function namesOnPage(string $url): array
    {
        $response = $this->get($url);
        $response->assertOk();
        preg_match_all('/<span class="font-medium text-ink">(.*?)<\/span>/', $response->getContent(), $mm);
        return $mm[1];
    }

    public function test_tabel_menu_tampil_dengan_paginasi_5_baris(): void
    {
        $res = $this->get('/admin/menu');
        $res->assertOk();
        $res->assertSee('Menampilkan 1–5 dari 12 menu');

        // hanya 5 baris nomor (No 1..5) — kolom No sel pertama
        $this->assertSame(5, substr_count($res->getContent(), 'font-mono text-[12px] text-inkmuted tnum'));

        // halaman 2 valid
        $this->get('/admin/menu?page=2')->assertOk()->assertSee('Menampilkan 6–10 dari 12 menu');
        $this->get('/admin/menu?page=3')->assertOk()->assertSee('Menampilkan 11–12 dari 12 menu');
    }

    public function test_urut_abjad(): void
    {
        $names = $this->namesOnPage('/admin/menu?sort=abjad');
        $this->assertSame(['Air Mineral', 'Bakmi Godog', 'Capjay', 'Es Jeruk', 'Es Kopi Susu'], $names);
    }

    public function test_urut_harga_terkecil_ke_terbesar(): void
    {
        $this->assertSame(['Air Mineral', 'Es Teh', 'Es Jeruk', 'Kopi Panas', 'Es Kopi Susu'],
            $this->namesOnPage('/admin/menu?sort=harga-kecil'));
    }

    public function test_urut_harga_terbesar_ke_terkecil(): void
    {
        $this->assertSame(['Nasi Goreng Spesial', 'Capjay', 'Soto Ayam', 'Nasi Goreng', 'Mie Goreng'],
            $this->namesOnPage('/admin/menu?sort=harga-besar'));
    }

    public function test_filter_mempertahankan_urutan_dan_halaman(): void
    {
        $res = $this->get('/admin/menu?sort=harga-besar&kategori=Minuman');
        $res->assertOk()->assertSee('Menampilkan 1–5 dari 6 menu');
        $names = $this->namesOnPage('/admin/menu?sort=harga-besar&kategori=Minuman');
        $this->assertSame(['Jus Alpukat', 'Es Kopi Susu', 'Kopi Panas', 'Es Jeruk', 'Es Teh'], $names);
        // link halaman 2 harus membawa sort + filter
        $this->get('/admin/menu?sort=harga-besar&kategori=Minuman&page=2')
            ->assertOk()->assertSee('Air Mineral');
    }

    public function test_admin_bisa_tambah_edit_dan_hapus_menu(): void
    {
        $this->post('/admin/menu', [
            'name' => 'Tahu Isi', 'category' => 'Makanan', 'price' => 3000, 'cost' => 1200, 'status' => 'Aktif',
        ])->assertRedirect()->assertSessionHas('success');

        $menu = Menu::where('name', 'Tahu Isi')->firstOrFail();
        $this->assertSame(3000, $menu->price);

        $this->put("/admin/menu/{$menu->id}", [
            'name' => 'Tahu Isi', 'category' => 'Makanan', 'price' => 4000, 'cost' => 1500, 'status' => 'Nonaktif',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame(4000, $menu->fresh()->price);
        $this->assertSame('Nonaktif', $menu->fresh()->status);

        $this->delete("/admin/menu/{$menu->id}")->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }

    public function test_tambah_menu_validasi_ditolak(): void
    {
        $this->from('/admin/menu')->post('/admin/menu', [
            'name' => '', 'category' => '', 'price' => -5, 'status' => 'Aktif',
        ])->assertSessionHasErrors(['name', 'category', 'price']);
    }

    public function test_toggle_status_menu(): void
    {
        $menu = Menu::where('name', 'Es Teh')->firstOrFail();
        $this->patch("/admin/menu/{$menu->id}/toggle")->assertRedirect();
        $this->assertSame('Nonaktif', $menu->fresh()->status);
    }

    public function test_halaman_kasir_mengambil_menu_aktif_dari_database(): void
    {
        // login sebagai user biasa untuk akses halaman kasir
        $user = User::create([
            'name' => 'Kasir Satu', 'username' => 'kasir', 'password' => 'kasir123',
            'role' => 'user', 'status' => 'Aktif',
        ]);
        $this->actingAs($user);

        // gambar = file LOKAL public/images/menu/<slug>.<ekstensi> — dibuktikan dengan
        // file sementara milik test ini (folder dikelola manual oleh pengguna,
        // isinya boleh kosong, jadi test tidak boleh bergantung padanya).
        $slug = 'uji-gambar-lokal-e2e';
        $file = public_path("images/menu/{$slug}.svg");
        file_put_contents($file, '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"/>');
        Menu::create([
            'name' => 'Uji Gambar Lokal E2E', 'category' => 'Makanan',
            'price' => 1000, 'cost' => 500, 'status' => 'Aktif',
        ]);

        try {
            $res = $this->get('/kasir/transaksi');
            $res->assertOk();
            // menu Aktif hadir, menu Nonaktif (Es Kopi Susu) tidak dikirim
            $res->assertSee('Nasi Goreng');
            $res->assertDontSee('Es Kopi Susu');
            // tanpa localStorage lagi
            $res->assertDontSee('kasiro-menus');
            // URL gambar lokal dikirim ke halaman (JSON escape '/' jadi '\/')
            $res->assertSee("\\/images\\/menu\\/{$slug}.svg");
            $res->assertSee('Bakmi Godog');
        } finally {
            @unlink($file);
        }
    }
}
