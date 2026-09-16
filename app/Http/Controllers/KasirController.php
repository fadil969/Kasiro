<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class KasirController extends Controller
{
    /**
     * Daftar semua akun kasir dari database.
     */
    public function index()
    {
        $kasirs = User::where('role', 'user')->orderBy('id')->get();

        return view('admin.kasir.index', compact('kasirs'));
    }

    /**
     * Simpan akun kasir baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash:ascii', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => __('ui.kasir.v_confirm'),
            'password.min' => __('ui.kasir.v_min6'),
            'username.unique' => __('ui.kasir.v_username'),
        ]);

        $username = $this->resolveUsername($data['username'] ?? '', $data['name']);

        User::create([
            'name' => $data['name'],
            'username' => $username,
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'status' => 'Aktif', // akun baru selalu aktif; status diatur lewat edit/toggle
        ]);

        return redirect()->route('admin.kasir')->with('success', __('ui.kasir.flash_added', ['name' => $data['name']]));
    }

    /**
     * Perbarui akun kasir (password opsional — kosong = tidak diganti).
     */
    public function update(Request $request, User $kasir)
    {
        abort_unless($kasir->role === 'user', 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash:ascii', Rule::unique('users', 'username')->ignore($kasir->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'status' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ], [
            'password.confirmed' => __('ui.kasir.v_confirm'),
            'password.min' => __('ui.kasir.v_min6'),
            'username.unique' => __('ui.kasir.v_username'),
        ]);

        $kasir->name = $data['name'];
        $kasir->status = $data['status'];

        $username = trim($data['username'] ?? '') !== '' ? $data['username'] : $kasir->username;
        $kasir->username = $username;

        if (! empty($data['password'])) {
            $kasir->password = Hash::make($data['password']);
        }

        $kasir->save();

        return redirect()->route('admin.kasir')->with('success', __('ui.kasir.flash_updated', ['name' => $kasir->name]));
    }

    /**
     * Toggle Aktif/Nonaktif.
     */
    public function toggle(User $kasir)
    {
        abort_unless($kasir->role === 'user', 404);

        $kasir->status = $kasir->status === 'Aktif' ? 'Nonaktif' : 'Aktif';
        $kasir->save();

        return redirect()->route('admin.kasir')->with('success', __('ui.kasir.flash_status', ['name' => $kasir->name, 'status' => $kasir->status]));
    }

    /**
     * Hapus akun kasir.
     */
    public function destroy(User $kasir)
    {
        abort_unless($kasir->role === 'user', 404);

        $name = $kasir->name;
        $kasir->delete();

        return redirect()->route('admin.kasir')->with('success', __('ui.kasir.flash_deleted', ['name' => $name]));
    }

    /**
     * Jika username dikosongkan, turunkan dari nama (huruf kecil, tanpa spasi).
     * Tambahkan angka jika sudah ada yang memakai.
     */
    private function resolveUsername(string $input, string $name): string
    {
        $base = $input !== '' ? strtolower($input) : preg_replace('/[^a-z0-9]/', '', strtolower($name));

        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . ++$i;
        }

        return $username;
    }
}
