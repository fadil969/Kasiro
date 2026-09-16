<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Bahasa UI dari session (diset lewat tombol ID/EN di topbar & halaman login).
 * Nilai valid: id | en. Default mengikuti config app.locale (= id).
 * Nama/isi data DB (menu, kategori, username, status Aktif/Nonaktif) TIDAK diubah.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = (string) $request->session()->get('locale', config('app.locale'));

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
