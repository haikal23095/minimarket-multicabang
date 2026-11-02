<?php

namespace App\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('admin-pusat.kategori.index', [
            'kategori' => Kategori::query()
                ->withCount('produk')
                ->orderBy('nama_kategori')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin-pusat.kategori.form', ['kategori' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_kategori' => ['required', 'string', 'alpha_dash', 'max:20', Rule::unique('Kategori', 'id_kategori')],
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        Kategori::query()->create($validated);

        return redirect()
            ->route('admin.pusat.kategori.index')
            ->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Kategori $kategori): View
    {
        return view('admin-pusat.kategori.form', ['kategori' => $kategori]);
    }

    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
        ]);

        $kategori->update($validated);

        return redirect()
            ->route('admin.pusat.kategori.index')
            ->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        if ($kategori->produk()->exists()) {
            return redirect()
                ->route('admin.pusat.kategori.index')
                ->with('error', 'Kategori yang masih digunakan produk tidak dapat dihapus.');
        }

        $kategori->delete();

        return redirect()
            ->route('admin.pusat.kategori.index')
            ->with('status', 'Kategori berhasil dihapus.');
    }
}
