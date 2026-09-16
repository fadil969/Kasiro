<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Batasi akses hanya untuk role tertentu, contoh: admin.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            if (! $user) {
                return redirect()->route('login');
            }
            // arahkan kembali ke beranda sesuai role user tsb (hindari redirect loop)
            return $user->role === 'admin'
                ? redirect()->route('admin.dashboard')->with('error', __('ui.common.no_access'))
                : redirect()->route('kasir.transaksi')->with('error', __('ui.common.no_access'));
        }

        return $next($request);
    }
}
