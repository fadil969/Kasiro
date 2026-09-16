<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Daftar kategori dari database — jumlah menu per kategori
     * dihitung langsung dari tabel menus (withCount), jadi selalu sinkron.
     */
    public function index()
    {
        $kategoris = Category::withCount('menus')->orderBy('id')->get();

        return view('admin.kategori.index', [
            'kategoris'  => $kategoris,
            'totalMenu'  => Menu::count(),
        ]);
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:20', 'unique:categories,name'],
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ], [
            'name.required' => __('ui.kategori.v_name'),
            'name.max'       => __('ui.kategori.v_max'),
            'name.unique'   => __('ui.kategori.v_unique'),
        ]);

        $kategori = Category::create($data);

        return redirect()->route('admin.kategori')->with('success', __('ui.kategori.flash_added', ['name' => $kategori->name]));
    }

    /**
     * Perbarui kategori (rename/status).
     * Rename diikut-sertakan ke menus.category: di MySQL otomatis via FK
     * onUpdate cascade; update eksplisit di bawah menjaga konsistensi juga
     * di database tanpa FK (mis. sqlite pada pengujian).
     */
    public function update(Request $request, Category $kategori)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:20', Rule::unique('categories', 'name')->ignore($kategori->id)],
            'status' => ['required', 'in:Aktif,Nonaktif'],
        ], [
            'name.required' => __('ui.kategori.v_name'),
            'name.max'       => __('ui.kategori.v_max'),
            'name.unique'   => __('ui.kategori.v_unique'),
        ]);

        $oldName = $kategori->name;

        DB::transaction(function () use ($kategori, $data, $oldName) {
            $kategori->update($data);

            if ($data['name'] !== $oldName) {
                Menu::where('category', $oldName)->update(['category' => $data['name']]);
            }
        });

        return redirect()->route('admin.kategori')->with('success', __('ui.kategori.flash_updated', ['name' => $kategori->name]));
    }

    /**
     * Hapus kategori — ditolak jika masih dipakai menu
     * (dijaga juga oleh FK onDelete restrict di database).
     */
    public function destroy(Category $kategori)
    {
        $count = $kategori->menus()->count();

        if ($count > 0) {
            return redirect()->route('admin.kategori')
                ->with('error', __('ui.kategori.flash_used', ['name' => $kategori->name, 'count' => $count]));
        }

        $name = $kategori->name;
        $kategori->delete();

        return redirect()->route('admin.kategori')->with('success', __('ui.kategori.flash_deleted', ['name' => $name]));
    }
}
