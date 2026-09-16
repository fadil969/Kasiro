<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Opsi urutan tabel menu admin.
     */
    private const SORTS = [
        'abjad'       => ['name', 'asc'],
        'abjad-z-a'   => ['name', 'desc'],
        'harga-kecil' => ['price', 'asc'],
        'harga-besar' => ['price', 'desc'],
    ];

    /**
     * Daftar menu (admin): cari, filter, urut, dan paginasi 5 baris/halaman.
     */
    public function index(Request $request)
    {
        $sort = array_key_exists((string) $request->query('sort'), self::SORTS)
            ? (string) $request->query('sort')
            : 'abjad';

        $query = Menu::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->string('q')->trim() . '%'))
            ->when($request->filled('kategori'), fn ($q) => $q->where('category', $request->query('kategori')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')));

        [$column, $direction] = self::SORTS[$sort];
        $menus = $query
            ->orderBy($column, $direction)
            ->orderBy('id')
            ->paginate(5)
            ->withQueryString();

        return view('admin.menu.index', [
            'menus'        => $menus,
            'kategoris'    => Category::orderBy('name')->get(),
            'sort'         => $sort,
            'totalAktif'   => Menu::where('status', 'Aktif')->count(),
        ]);
    }

    /**
     * Halaman kasir: grid menu Aktif dari database (gambar = file lokal).
     * Kategori chip mengikuti tabel categories — hanya yang berisi menu Aktif.
     */
    public function kasir()
    {
        $menus = Menu::where('status', 'Aktif')
            ->orderBy('id')
            ->get()
            ->map(fn (Menu $m) => [
                'id'       => $m->id,
                'name'     => $m->name,
                'category' => strtolower($m->category),
                'price'    => $m->price,
                'image'    => $m->imageUrl() ?? '',
            ]);

        $kategoris = Category::where('status', 'Aktif')
            ->whereHas('menus', fn ($q) => $q->where('status', 'Aktif'))
            ->orderBy('id')
            ->get()
            ->map(fn (Category $c) => [
                'label' => $c->name,
                'value' => strtolower($c->name),
            ]);

        return view('kasir.transaksi', [
            'posMenus'     => $menus,
            'posCategories' => $kategoris,
        ]);
    }

    /**
     * Simpan menu baru.
     */
    public function store(Request $request)
    {
        $data = $this->validateMenu($request);

        $menu = Menu::create($data);
        $this->saveImage($request, $menu);

        return redirect()->back()->with('success', __('ui.menu.flash_added', ['name' => $menu->name]));
    }

    /**
     * Perbarui menu.
     */
    public function update(Request $request, Menu $menu)
    {
        $data = $this->validateMenu($request);

        $menu->update($data);
        $this->saveImage($request, $menu);

        return redirect()->back()->with('success', __('ui.menu.flash_updated', ['name' => $menu->name]));
    }

    /**
     * Toggle Aktif/Nonaktif.
     */
    public function toggle(Menu $menu)
    {
        $menu->status = $menu->status === 'Aktif' ? 'Nonaktif' : 'Aktif';
        $menu->save();

        return redirect()->back()->with('success', __('ui.menu.flash_status', ['name' => $menu->name, 'status' => $menu->status]));
    }

    /**
     * Hapus menu permanen.
     */
    public function destroy(Menu $menu)
    {
        $name = $menu->name;
        $menu->delete();

        return redirect()->back()->with('success', __('ui.menu.flash_deleted', ['name' => $name]));
    }

    /**
     * Aturan validasi bersama untuk store/update.
     */
    private function validateMenu(Request $request): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'category' => ['required', 'exists:categories,name'],
            'price'    => ['required', 'numeric', 'min:0', 'max:999999999'],
            'cost'     => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'status'   => ['required', 'in:Aktif,Nonaktif'],
        ], [
            'name.required'     => __('ui.menu.v_name'),
            'category.required' => __('ui.menu.v_cat_req'),
            'category.exists'   => __('ui.menu.v_cat_inv'),
            'price.required'    => __('ui.menu.v_price_req'),
            'price.numeric'     => __('ui.menu.v_price_num'),
            'price.min'         => __('ui.menu.v_price_min'),
        ]);
    }

    /**
     * Simpan gambar ke FOLDER LOKAL public/images/menu/<slug>.<ekstensi> —
     * sengaja TIDAK disimpan ke database.
     */
    private function saveImage(Request $request, Menu $menu): void
    {
        $file = $request->file('image');
        if (! $file) {
            return;
        }

        $request->validate(
            ['image' => ['image', 'mimes:png,jpg,jpeg,svg,webp,gif', 'max:2048']],
            ['image.image' => __('ui.menu.v_img_type'), 'image.max' => __('ui.menu.v_img_size')]
        );

        $dir = public_path('images/menu');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = $menu->imageSlug() . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($dir, $filename);
    }
}
