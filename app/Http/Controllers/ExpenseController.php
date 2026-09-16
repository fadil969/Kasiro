<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Daftar pengeluaran (admin): data nyata dari tabel `expenses`.
     * Pencarian, filter kategori, dan urutan berjalan di sisi klien (Alpine)
     * lewat data yang disuntikkan ke halaman — lihat views/admin/pengeluaran.
     */
    public function index()
    {
        $expenses = Expense::latest('date')->latest('id')->get()
            ->map(fn (Expense $e) => [
                'id'          => $e->id,
                'date'        => $e->date->format('Y-m-d'),
                'description' => $e->description,
                'category'    => $e->category,
                'amount'      => $e->amount,
            ]);

        return view('admin.pengeluaran.index', [
            'expenses'      => $expenses,
            'kategoriList'  => Expense::CATEGORIES,
        ]);
    }

    /**
     * Catat pengeluaran baru (satu-satunya aksi tulis — edit sengaja tidak ada).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'date'        => ['required', 'date'],
            'description' => ['required', 'string', 'max:100'],
            'category'    => ['required', 'in:' . implode(',', Expense::CATEGORIES)],
            'amount'      => ['required', 'numeric', 'min:0', 'max:999999999'],
        ], [
            'date.required'        => __('ui.pengeluaran.v_date'),
            'description.required' => __('ui.pengeluaran.v_desc'),
            'category.required'    => __('ui.pengeluaran.v_cat_req'),
            'category.in'          => __('ui.pengeluaran.v_cat_inv'),
            'amount.required'      => __('ui.pengeluaran.v_amt_req'),
            'amount.numeric'       => __('ui.pengeluaran.v_amt_num'),
            'amount.min'           => __('ui.pengeluaran.v_amt_min'),
        ]);

        // Cegah nilai minus lewat input manual
        $data['amount'] = max(0, (int) $data['amount']);

        $expense = Expense::create($data);

        return redirect()->route('admin.pengeluaran')
            ->with('success', __('ui.pengeluaran.flash_saved', ['name' => $expense->description]));
    }

    /**
     * Hapus catatan pengeluaran.
     */
    public function destroy(Expense $pengeluaran)
    {
        $desc = $pengeluaran->description;
        $pengeluaran->delete();

        return redirect()->route('admin.pengeluaran')
            ->with('success', __('ui.pengeluaran.flash_deleted', ['name' => $desc]));
    }
}
