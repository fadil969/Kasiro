<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Kasiro POS — Routes
|--------------------------------------------------------------------------
| Phase 1-3: view-only dengan data dummy.
| Sekarang: auth nyata (login/logout, role admin|kasir) + route view Blade.
|--------------------------------------------------------------------------
*/

// Guest
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Ganti bahasa UI (id|en) — berlaku untuk tamu & user, disimpan di session
Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['id', 'en'], true), 404);
    session()->put('locale', $locale);

    return redirect()->back(fallback: '/');
})->name('lang.switch');

// Semua halaman lain wajib login
Route::middleware('auth')->group(function () {

    // Shared
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::view('/profile', 'profile.index')->name('profile');

    // Admin Routes — khusus role admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
        Route::get('/menu', [MenuController::class, 'index'])->name('menu');
        Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
        Route::put('/menu/{menu}', [MenuController::class, 'update'])->name('menu.update');
        Route::patch('/menu/{menu}/toggle', [MenuController::class, 'toggle'])->name('menu.toggle');
        Route::delete('/menu/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');

        // Kategori — CRUD database penuh
        Route::get('/kategori', [CategoryController::class, 'index'])->name('kategori');
        Route::post('/kategori', [CategoryController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{kategori}', [CategoryController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [CategoryController::class, 'destroy'])->name('kategori.destroy');
        // Pengeluaran — data nyata dari tabel expenses; TANPA edit (sesuai permintaan user)
        Route::get('/pengeluaran', [ExpenseController::class, 'index'])->name('pengeluaran');
        Route::post('/pengeluaran', [ExpenseController::class, 'store'])->name('pengeluaran.store');
        Route::delete('/pengeluaran/{pengeluaran}', [ExpenseController::class, 'destroy'])->name('pengeluaran.destroy');
        Route::view('/laporan', 'admin.laporan.index')->name('laporan');
        Route::view('/riwayat', 'admin.riwayat.index')->name('riwayat');

        // Akun Kasir — CRUD database penuh
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir');
        Route::post('/kasir', [KasirController::class, 'store'])->name('kasir.store');
        Route::put('/kasir/{kasir}', [KasirController::class, 'update'])->name('kasir.update');
        Route::patch('/kasir/{kasir}/toggle', [KasirController::class, 'toggle'])->name('kasir.toggle');
        Route::delete('/kasir/{kasir}', [KasirController::class, 'destroy'])->name('kasir.destroy');
    });

    // Kasir Routes — khusus role user (admin tidak punya akses)
    Route::middleware('role:user')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/transaksi', [MenuController::class, 'kasir'])->name('transaksi');
        Route::view('/riwayat', 'kasir.riwayat.index')->name('riwayat');
    });
});

// Redirect root sesuai status login
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('kasir.transaksi');
})->name('home');
