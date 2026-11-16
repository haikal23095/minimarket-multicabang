<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $branch = config('app.branch');
        $branchId = $branch['id'] ?? 'MLG';
        $produk = Produk::with(['stok' => function ($q) use ($branchId) {
            $q->where('id_cabang', $branchId);
        }, 'kategori'])->get();

        return view('pos.index', compact('produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'bayar' => 'required|numeric',
            'total_harga' => 'required|numeric',
        ]);

        if ($request->bayar < $request->total_harga) {
            return response()->json(['message' => 'Uang pembayaran kurang!'], 422);
        }

        $branch = config('app.branch');
        $branchId = $branch['id'] ?? 'MLG';
        $branchName = $branch['name'] ?? 'Malang';

        DB::beginTransaction();

        try {
            // Buat string ID Transaksi max 25 karakter sesuai varchar(25) di SSMS
            $idTransaksi = 'TRX-' . $branchId . '-' . date('YmdHis') . rand(10, 99);
            $noFaktur = 'FKT-' . $branchId . '-' . date('YmdHis');

            // 1. Simpan Header Transaksi
            $transaksi = new Transaksi();
            $transaksi->id_transaksi = $idTransaksi;
            $transaksi->no_faktur = $noFaktur;
            $transaksi->id_cabang = $branchId;
            $transaksi->id_pegawai = auth()->user()->pegawai_id ?? (Pegawai::value('id_pegawai') ?? 1);
            $transaksi->tanggal_transaksi = now();
            $transaksi->total_transaksi = $request->total_harga;
            $transaksi->bayar = $request->bayar;
            $transaksi->save();

            // 2. Simpan Detail Transaksi & Kurangi Stok otomatis via Trigger SQL Server
            foreach ($request->cart as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $idTransaksi,
                    'id_produk' => $item['id_produk'],
                    'qty' => $item['jumlah'],
                    'harga' => $item['harga_jual'],
                    'subtotal' => $item['jumlah'] * $item['harga_jual'],
                ]);
            }

            DB::commit();

            $kembalian = (float) max(0, $request->bayar - $request->total_harga);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'transaksi' => [
                    'id_transaksi' => $idTransaksi,
                    'no_faktur' => $noFaktur,
                    'id_cabang' => $branchId,
                    'nama_cabang' => $branchName,
                    'kasir' => auth()->user()->username ?? 'Kasir',
                    'tanggal' => now()->translatedFormat('d/m/Y H:i:s'),
                    'total_transaksi' => (float) $request->total_harga,
                    'bayar' => (float) $request->bayar,
                    'kembalian' => $kembalian,
                    'items' => array_map(function ($item) {
                        return [
                            'id_produk' => $item['id_produk'],
                            'nama_produk' => $item['nama_produk'],
                            'harga_jual' => (float) $item['harga_jual'],
                            'jumlah' => (int) $item['jumlah'],
                            'subtotal' => (float) ($item['harga_jual'] * $item['jumlah']),
                        ];
                    }, $request->cart),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function riwayat(Request $request)
    {
        $branch = config('app.branch');
        $branchId = $branch['id'] ?? 'MLG';
        $branchName = $branch['name'] ?? 'Malang';

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date'],
        ]);

        $query = Transaksi::with(['details.produk', 'pegawai'])
            ->where('id_cabang', $branchId);

        if (!empty($filters['search'])) {
            $query->where('no_faktur', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['tanggal'])) {
            $query->whereDate('tanggal_transaksi', $filters['tanggal']);
        }

        $transaksi = $query->orderByDesc('tanggal_transaksi')->paginate(10)->withQueryString();

        $today = now()->toDateString();
        $ringkasan = [
            'transaksi_hari_ini' => Transaksi::where('id_cabang', $branchId)->whereDate('tanggal_transaksi', $today)->count(),
            'omzet_hari_ini' => (float) Transaksi::where('id_cabang', $branchId)->whereDate('tanggal_transaksi', $today)->sum('total_transaksi'),
        ];

        return view('pos.riwayat', [
            'transaksi' => $transaksi,
            'ringkasan' => $ringkasan,
            'namaCabang' => $branchName,
            'kodeCabang' => $branchId,
            'search' => $filters['search'] ?? '',
            'tanggal' => $filters['tanggal'] ?? '',
        ]);
    }

    public function showNota(string $idTransaksi)
    {
        $transaksi = Transaksi::with(['details.produk', 'pegawai'])
            ->where('id_transaksi', $idTransaksi)
            ->firstOrFail();

        $branch = config('app.branch');

        return response()->json([
            'id_transaksi' => $transaksi->id_transaksi,
            'no_faktur' => $transaksi->no_faktur,
            'id_cabang' => $transaksi->id_cabang,
            'nama_cabang' => $branch['name'] ?? 'Cabang',
            'kasir' => $transaksi->pegawai->nama_pegawai ?? auth()->user()->username ?? 'Kasir',
            'tanggal' => Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y H:i:s'),
            'total_transaksi' => (float) $transaksi->total_transaksi,
            'bayar' => (float) $transaksi->bayar,
            'kembalian' => (float) max(0, $transaksi->bayar - $transaksi->total_transaksi),
            'items' => $transaksi->details->map(function ($d) {
                return [
                    'id_produk' => $d->id_produk,
                    'nama_produk' => $d->produk->nama_produk ?? $d->id_produk,
                    'harga_jual' => (float) $d->harga,
                    'jumlah' => (int) $d->qty,
                    'subtotal' => (float) $d->subtotal,
                ];
            }),
        ]);
    }
}
