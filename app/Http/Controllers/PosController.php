<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $produk = Produk::with('stok', 'kategori')->get();
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

        DB::connection('sqlsrv_malang')->beginTransaction();

        try {
            // Buat string ID Transaksi max 25 karakter sesuai varchar(25) di SSMS
            $idTransaksi = 'TRX-MLG-' . date('YmdHis') . rand(10, 99);
            $noFaktur = 'FKT-MLG-' . date('YmdHis');

            // 1. Simpan Header Transaksi
            $transaksi = new Transaksi();
            $transaksi->id_transaksi = $idTransaksi; // Mengisi id_transaksi secara eksplisit
            $transaksi->no_faktur = $noFaktur;
            $transaksi->id_cabang = 'MLG';    // Mengisi id_cabang secara eksplisit
            $transaksi->id_pegawai = auth()->user()->pegawai_id ?? 'PEG001';
            $transaksi->tanggal_transaksi = now();
            $transaksi->total_transaksi = $request->total_harga;
            $transaksi->bayar = $request->bayar;
            $transaksi->save();

            // 2. Simpan Detail Transaksi & Kurangi Stok
            foreach ($request->cart as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $idTransaksi,
                    'id_produk' => $item['id_produk'],
                    'qty' => $item['jumlah'],
                    'harga' => $item['harga_jual'],
                    'subtotal' => $item['jumlah'] * $item['harga_jual'],
                ]);

                $stok = Stok::where('id_produk', $item['id_produk'])->first();
                if ($stok) {
                    $stok->decrement('jumlah_stok', $item['jumlah']);
                }
            }

            DB::connection('sqlsrv_malang')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil!',
                'no_faktur' => $noFaktur,
            ]);

        } catch (\Exception $e) {
            DB::connection('sqlsrv_malang')->rollBack();
            return response()->json(['message' => 'Gagal memproses transaksi: ' . $e->getMessage()], 500);
        }
    }
}