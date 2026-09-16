<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KategoriTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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

        Category::create(['name' => 'Makanan', 'status' => 'Aktif']);
        Category::create(['name' => 'Minuman', 'status' => 'Aktif']);

        foreach ([
            ['Nasi Goreng', 'Makanan'], ['Soto Ayam', 'Makanan'], ['Bakmi Godog', 'Makanan'],
            ['Es Teh', 'Minuman'], ['Es Jeruk', 'Minuman'],
        ] as [$name, $cat]) {
            Menu::create(['name' => $name, 'category' => $cat, 'price' => 10000, 'cost' => 5000, 'status' => 'Aktif']);
        }

        $this->actingAs($this->admin);
    }

    public function test_halaman_kategori_menampilkan_jumlah_menu_yang_sesuai(): void
    {
        $res = $this->get('/admin/kategori');
        $res->assertOk();

        // jumlah dihitung dari tabel menus — 3 Makanan, 2 Minuman
        $res->assertSee('3 menu');
        $res->assertSee('2 menu');
        $res->assertSee('Makanan');
        $res->assertSee('Minuman');
        // header: 2 kategori — 5 menu total
        $res->assertSee('2 kategori');
        $res->assertSee('5 menu total');
    }

    public function test_admin_bisa_menambah_kategori(): void
    {
        $this->post('/admin/kategori', ['name' => 'Snack', 'status' => 'Aktif'])
            ->assertRedirect(route('admin.kategori'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Snack', 'status' => 'Aktif']);

        // muncul di halaman dengan 0 menu
        $this->get('/admin/kategori')->assertOk()->assertSee('Snack')->assertSee('0 menu');
    }

    public function test_tambah_kategori_validasi_ditolak(): void
    {
        // nama kosong
        $this->from('/admin/kategori')->post('/admin/kategori', ['name' => '', 'status' => 'Aktif'])
            ->assertSessionHasErrors('name');

        // duplikat
        $this->from('/admin/kategori')->post('/admin/kategori', ['name' => 'Makanan', 'status' => 'Aktif'])
            ->assertSessionHasErrors('name');

        $this->assertSame(2, Category::count(), 'Tidak ada kategori baru yang tersimpan.');
    }

    public function test_rename_kategori_mengikut_sertakan_menu(): void
    {
        $makanan = Category::where('name', 'Makanan')->firstOrFail();

        $this->put("/admin/kategori/{$makanan->id}", ['name' => 'Makan Berat', 'status' => 'Aktif'])
            ->assertRedirect(route('admin.kategori'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Makan Berat']);
        $this->assertDatabaseMissing('categories', ['name' => 'Makanan']);

        // 3 menu ikut pindah kategori
        $this->assertSame(3, Menu::where('category', 'Makan Berat')->count());
        $this->assertSame(0, Menu::where('category', 'Makanan')->count());

        // filter dropdown menu admin memakai nama baru
        $this->get('/admin/menu')->assertOk()->assertSee('Makan Berat');
    }

    public function test_hapus_kategori_yang_masih_dipakai_menu_ditolak(): void
    {
        $makanan = Category::where('name', 'Makanan')->firstOrFail();

        $this->delete("/admin/kategori/{$makanan->id}")
            ->assertRedirect(route('admin.kategori'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['name' => 'Makanan']);
    }

    public function test_hapus_kategori_kosong_berhasil(): void
    {
        $snack = Category::create(['name' => 'Snack', 'status' => 'Nonaktif']);

        $this->delete("/admin/kategori/{$snack->id}")
            ->assertRedirect(route('admin.kategori'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $snack->id]);
    }

    public function test_menu_dengan_kategori_baru_muncul_di_filter_dan_chip_kasir(): void
    {
        // kategori baru dengan menu aktif
        Category::create(['name' => 'Cemilan', 'status' => 'Aktif']);
        Menu::create(['name' => 'Pisang Goreng', 'category' => 'Cemilan', 'price' => 5000, 'cost' => 2000, 'status' => 'Aktif']);

        // dropdown filter admin memuat kategori baru
        $this->get('/admin/menu')->assertOk()->assertSee('Cemilan');

        // login kasir → chip kategori berasal dari tabel categories
        $kasir = User::create([
            'name' => 'Kasir Satu', 'username' => 'kasir', 'password' => 'kasir123',
            'role' => 'user', 'status' => 'Aktif',
        ]);
        $this->actingAs($kasir);

        $res = $this->get('/kasir/transaksi');
        $res->assertOk();
        $res->assertSee('Makanan');   // chip
        $res->assertSee('Minuman');   // chip
        $res->assertSee('Cemilan');   // chip baru (punya menu aktif)
        $res->assertSee('Pisang Goreng');

        // kategori kosong tidak jadi chip: 'Snack' tanpa menu tidak dikirim
        Category::create(['name' => 'Snack', 'status' => 'Aktif']);
        $res2 = $this->get('/kasir/transaksi');
        $res2->assertOk();
        $this->assertSame(0, substr_count($res2->getContent(), 'Snack'));
    }

    public function test_kategori_nonaktif_tidak_muncul_di_chip_kasir(): void
    {
        // buat kategori nonaktif yang berisi menu aktif
        Category::create(['name' => 'Cemilan', 'status' => 'Nonaktif']);
        Menu::create(['name' => 'Pisang Goreng', 'category' => 'Cemilan', 'price' => 5000, 'cost' => 2000, 'status' => 'Aktif']);

        $kasir = User::create([
            'name' => 'Kasir Satu', 'username' => 'kasir', 'password' => 'kasir123',
            'role' => 'user', 'status' => 'Aktif',
        ]);
        $this->actingAs($kasir);

        $res = $this->get('/kasir/transaksi');
        $res->assertOk();
        // menu-nya tetap tampil di grid (status menu Aktif)
        $res->assertSee('Pisang Goreng');
        // tapi chip 'Cemilan' tidak ada — hanya kategori Aktif yang jadi chip
        $this->assertSame(0, substr_count($res->getContent(), 'Cemilan'));
    }

    public function test_menu_baru_dengan_kategori_tidak_terdaftar_ditolak(): void
    {
        $this->from('/admin/menu')->post('/admin/menu', [
            'name' => 'Kopi Susu', 'category' => 'Kategori Ngawur', 'price' => 5000, 'status' => 'Aktif',
        ])->assertSessionHasErrors('category');

        $this->assertDatabaseMissing('menus', ['name' => 'Kopi Susu']);
    }
}
