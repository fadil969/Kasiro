<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin',
            'status' => 'Aktif',
        ]);
    }

    private function biasa(): User
    {
        return User::create([
            'name' => 'Kasir Satu',
            'username' => 'kasir',
            'password' => 'kasir123',
            'role' => 'user',
            'status' => 'Aktif',
        ]);
    }

    public function test_login_page_is_visible(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_login_with_password_and_lands_on_dashboard(): void
    {
        $admin = $this->admin();

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_login_with_username_and_lands_on_kasir_page(): void
    {
        $user = $this->biasa();

        $this->post('/login', [
            'username' => 'kasir',
            'password' => 'kasir123',
        ])->assertRedirect(route('kasir.transaksi'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_by_nama_user_juga_berhasil(): void
    {
        $user = $this->biasa();

        $this->post('/login', [
            'username' => 'Kasir Satu',
            'password' => 'kasir123',
        ])->assertRedirect(route('kasir.transaksi'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_atau_yang_bukan_username_ditolak(): void
    {
        $this->admin();

        // format email lama tidak berlaku lagi
        $this->from('/login')
            ->post('/login', ['username' => 'admin@kasiro.test', 'password' => 'admin123'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->admin();

        $this->from('/login')
            ->post('/login', ['username' => 'admin', 'password' => 'salah'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_account_is_rejected(): void
    {
        $user = $this->biasa();
        $user->update(['status' => 'Nonaktif']);

        $this->from('/login')
            ->post('/login', ['username' => 'kasir', 'password' => 'kasir123'])
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/kasir/transaksi')->assertRedirect(route('login'));
    }

    public function test_role_middleware_blocks_cross_role_access(): void
    {
        $this->admin();
        $this->post('/login', ['username' => 'admin', 'password' => 'admin123']);

        // admin tidak boleh masuk area user (/kasir/*) -> redirect ke dashboard admin
        $this->get('/kasir/transaksi')->assertRedirect(route('admin.dashboard'));
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_user_role_cannot_access_admin_area(): void
    {
        $this->biasa();
        $this->post('/login', ['username' => 'kasir', 'password' => 'kasir123']);

        $this->get('/admin/dashboard')->assertRedirect(route('kasir.transaksi'));
        $this->get('/kasir/transaksi')->assertOk();
    }

    public function test_session_survives_across_requests(): void
    {
        $this->admin();
        $this->post('/login', ['username' => 'admin', 'password' => 'admin123']);

        $this->get('/admin/dashboard')->assertOk();
        $this->assertAuthenticated();
        $this->get('/admin/menu')->assertOk();
    }

    public function test_database_hanya_tabel_minimal(): void
    {
        $this->assertTrue(\Schema::hasTable('users'));
        $this->assertTrue(\Schema::hasTable('menus'), 'Tabel menus harus ada.');
        $this->assertTrue(\Schema::hasTable('categories'), 'Tabel categories harus ada.');
        $this->assertFalse(\Schema::hasColumn('users', 'email'), 'Kolom email harus sudah dihapus.');
        $this->assertFalse(\Schema::hasColumn('users', 'email_verified_at'), 'Kolom email_verified_at harus sudah dihapus.');
        foreach (['cache', 'cache_locks', 'sessions', 'jobs', 'failed_jobs', 'job_batches', 'password_reset_tokens'] as $t) {
            $this->assertFalse(\Schema::hasTable($t), "Tabel framework '$t' seharusnya sudah dihapus.");
        }
    }
}
