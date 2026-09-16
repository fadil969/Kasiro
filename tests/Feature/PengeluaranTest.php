<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
            'status' => 'Aktif',
        ]);

        $this->actingAs($admin);
    }

    public function test_halaman_pengeluaran_kosong_dari_database(): void
    {
        // Wadah saja — tanpa data dummy
        $res = $this->get('/admin/pengeluaran');
        $res->assertOk();
        $res->assertSee('Belum ada pengeluaran tercatat');
        $this->assertSame(0, Expense::count());

        // Pilihan kategori "Lainnya" sudah dihapus
        $res->assertDontSee('Lainnya');
        $res->assertSee('Operasional')->assertSee('Bahan Baku')
            ->assertSee('Utilitas')->assertSee('Sewa');
    }

    public function test_admin_bisa_mencatat_pengeluaran_real(): void
    {
        $this->post('/admin/pengeluaran', [
            'date'        => '2026-09-15',
            'description' => 'Beli Gas Melon',
            'category'    => 'Operasional',
            'amount'      => 25000,
        ])->assertRedirect(route('admin.pengeluaran'))->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'description' => 'Beli Gas Melon',
            'category'    => 'Operasional',
            'amount'      => 25000,
        ]);

        // Muncul nyata di halaman (bukan dummy) — refresh membuktikan persistensi DB
        $this->get('/admin/pengeluaran')->assertOk()->assertSee('Beli Gas Melon');
    }

    public function test_validasi_pengeluaran_ditolak(): void {
        $this->from('/admin/pengeluaran')->post('/admin/pengeluaran', [])
            ->assertSessionHasErrors(['date', 'description', 'category', 'amount']);

        // kategori di luar daftar (mis. "Lainnya") ditolak
        $this->from('/admin/pengeluaran')->post('/admin/pengeluaran', [
            'date' => '2026-09-15', 'description' => 'X', 'category' => 'Lainnya', 'amount' => 100,
        ])->assertSessionHasErrors('category');

        $this->assertSame(0, Expense::count());
    }

    public function test_pengeluaran_dihapus_dari_database(): void
    {
        $e = Expense::create([
            'date' => '2026-09-01', 'description' => 'Bayar Air', 'category' => 'Utilitas', 'amount' => 75000,
        ]);

        $this->delete("/admin/pengeluaran/{$e->id}")
            ->assertRedirect(route('admin.pengeluaran'))->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', ['id' => $e->id]);
        // baris tabel kosong lagi (pesan flash memang menyebut namanya)
        $res = $this->get('/admin/pengeluaran');
        $res->assertOk()->assertSee('Belum ada pengeluaran tercatat');
    }

    public function test_tidak_ada_route_edit_pengeluaran(): void
    {
        $e = Expense::create([
            'date' => '2026-09-01', 'description' => 'Sewa', 'category' => 'Sewa', 'amount' => 500000,
        ]);

        // PUT (edit) tidak diterima — route-nya memang tidak dibuat
        $this->put("/admin/pengeluaran/{$e->id}", ['description' => 'Diubah'])->assertStatus(405);
        $this->assertDatabaseHas('expenses', ['id' => $e->id, 'description' => 'Sewa']);
    }
}
