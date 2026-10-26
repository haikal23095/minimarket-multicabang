<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StokController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    public function index(Request $request): View
    {
        $branch = $this->currentBranch();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,low,normal'],
        ]);

        $ringkasanStok = DB::table('Stok')
            ->where('id_cabang', $branch['id'])
            ->selectRaw(
                'COUNT(*) as jumlah_produk, COALESCE(SUM(jumlah_stok), 0) as total_stok, COALESCE(SUM(CASE WHEN jumlah_stok <= ? THEN 1 ELSE 0 END), 0) as produk_hampir_habis',
                [self::LOW_STOCK_THRESHOLD],
            )
            ->first();

        $stokQuery = DB::table('Stok')
            ->join('Produk', 'Produk.id_produk', '=', 'Stok.id_produk')
            ->where('Stok.id_cabang', $branch['id'])
            ->select('Stok.id_produk', 'Produk.nama_produk', 'Stok.jumlah_stok')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('Produk.nama_produk', 'like', '%'.$search.'%')
                        ->orWhere('Stok.id_produk', 'like', '%'.$search.'%');
                });
            })
            ->when(($filters['status'] ?? 'all') === 'low', function (Builder $query): void {
                $query->where('Stok.jumlah_stok', '<=', self::LOW_STOCK_THRESHOLD);
            })
            ->when(($filters['status'] ?? 'all') === 'normal', function (Builder $query): void {
                $query->where('Stok.jumlah_stok', '>', self::LOW_STOCK_THRESHOLD);
            })
            ->orderBy('Stok.jumlah_stok')
            ->orderBy('Produk.nama_produk');

        return view('stok.index', [
            'stok' => $stokQuery->paginate(15)->withQueryString(),
            'batasStokRendah' => self::LOW_STOCK_THRESHOLD,
            'namaCabang' => $branch['name'],
            'jumlahProduk' => $ringkasanStok->jumlah_produk,
            'totalStok' => $ringkasanStok->total_stok,
            'produkHampirHabis' => $ringkasanStok->produk_hampir_habis,
            'search' => $filters['search'] ?? '',
            'status' => $filters['status'] ?? 'all',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $branch = $this->currentBranch();

        $validated = $request->validate([
            'id_produk' => ['required', 'string', 'exists:Produk,id_produk'],
            'jumlah_masuk' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $stokDiperbarui = DB::table('Stok')
            ->where('id_cabang', $branch['id'])
            ->where('id_produk', $validated['id_produk'])
            ->increment('jumlah_stok', $validated['jumlah_masuk']);

        abort_if($stokDiperbarui === 0, 404);

        return redirect()
            ->route('admin.stok.index')
            ->with('status', 'Stok barang berhasil ditambahkan.');
    }

    /**
     * @return array{id: string, name: string}
     */
    private function currentBranch(): array
    {
        $branch = config('app.branch');

        abort_unless(is_array($branch), 404);

        return $branch;
    }
}
