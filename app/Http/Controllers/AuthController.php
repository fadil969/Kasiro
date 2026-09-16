<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Proses login: valid kredensial, lalu arahkan sesuai role.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // field username menerima username ATAU nama user (contoh: "admin")
        $username = trim($credentials['username']);

        $user = \App\Models\User::query()
            ->where('username', $username)
            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($username)])
            ->orderBy('id')
            ->first();

        $ok = $user && $user->status === 'Aktif' && Auth::attempt([
            'username' => $user->username,
            'password' => $credentials['password'],
        ]);

        if ($user && $user->status !== 'Aktif' && Auth::validate([
            'username' => $user->username,
            'password' => $credentials['password'],
        ])) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => __('ui.auth.inactive')]);
        }

        if (! $ok) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => __('ui.auth.bad_login')]);
        }

        return $request->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('kasir.transaksi');
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
