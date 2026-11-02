<?php

namespace App\Http\Controllers\AdminPusat;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProdukController extends Controller
{
    public function index(): View
    {
        return view('admin-pusat.produk.index', [
            'produk' => Produk::query()
                ->with('kategori')
                ->orderBy('nama_produk')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin-pusat.produk.form', [
            'produk' => null,
            'kategori' => Kategori::query()->orderBy('nama_kategori')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_produk' => ['required', 'string', 'alpha_dash', 'max:20', Rule::unique('Produk', 'id_produk')],
            'id_kategori' => ['required', 'string', 'exists:Kategori,id_kategori'],
            'nama_produk' => ['required', 'string', 'max:150'],
            'harga_jual' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
        ]);

        Produk::query()->create($validated);

        return redirect()
            ->route('admin.pusat.produk.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk): View
    {
        return view('admin-pusat.produk.form', [
            'produk' => $produk,
            'kategori' => Kategori::query()->orderBy('nama_kategori')->get(),
        ]);
    }

    public function update(Request $request, Produk $produk): RedirectResponse
    {
        $validated = $request->validate([
            'id_kategori' => ['required', 'string', 'exists:Kategori,id_kategori'],
            'nama_produk' => ['required', 'string', 'max:150'],
            'harga_jual' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
        ]);

        $produk->update($validated);

        return redirect()
            ->route('admin.pusat.produk.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk): RedirectResponse
    {
        $hasStock = Schema::hasTable('Stok')
            && DB::table('Stok')->where('id_produk', $produk->getKey())->exists();
        $hasTransactions = Schema::hasTable('DetailTransaksi')
            && DB::table('DetailTransaksi')->where('id_produk', $produk->getKey())->exists();

        if ($hasStock || $hasTransactions) {
            return redirect()
                ->route('admin.pusat.produk.index')
                ->with('error', 'Produk dengan stok atau riwayat transaksi tidak dapat dihapus.');
        }

        $produk->delete();

        return redirect()
            ->route('admin.pusat.produk.index')
            ->with('status', 'Produk berhasil dihapus.');
    }
}
