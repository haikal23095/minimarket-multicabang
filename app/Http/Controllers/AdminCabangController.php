<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminCabangController extends Controller
{
    public function index(): View
    {
        $branch = config('app.branch');

        abort_unless(is_array($branch), 404);

        $ringkasanStok = DB::table('Stok')
            ->where('id_cabang', $branch['id'])
            ->selectRaw('COUNT(*) as jumlah_produk, COALESCE(SUM(jumlah_stok), 0) as total_stok')
            ->first();

        $produkHampirHabis = DB::table('Stok')
            ->where('id_cabang', $branch['id'])
            ->where('jumlah_stok', '<=', 10)
            ->count();

        $today = now()->startOfDay();
        $transaksiMingguan = DB::table('Transaksi')
            ->where('id_cabang', $branch['id'])
            ->whereBetween('tanggal_transaksi', [
                $today->copy()->subDays(6),
                $today->copy()->endOfDay(),
            ])
            ->get(['tanggal_transaksi', 'total_transaksi']);

        $grafikHarian = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = $today->copy()->subDays($daysAgo);
            $grafikHarian[$date->toDateString()] = [
                'label' => $date->locale('id')->translatedFormat('D'),
                'tanggal' => $date->format('d/m'),
                'jumlah_transaksi' => 0,
                'omzet' => 0.0,
            ];
        }

        $omzetMingguan = 0.0;

        foreach ($transaksiMingguan as $transaksi) {
            $date = Carbon::parse($transaksi->tanggal_transaksi)->toDateString();
            $omzet = (float) $transaksi->total_transaksi;

            $grafikHarian[$date]['jumlah_transaksi']++;
            $grafikHarian[$date]['omzet'] += $omzet;
            $omzetMingguan += $omzet;
        }

        $grafikHarian = array_values($grafikHarian);
        $omzetMaksimumHarian = max(1, ...array_column($grafikHarian, 'omzet'));
        $transaksiMaksimumHarian = max(1, ...array_column($grafikHarian, 'jumlah_transaksi'));

        foreach ($grafikHarian as &$dataHarian) {
            $dataHarian['tinggi_omzet'] = $dataHarian['omzet'] > 0
                ? max(6, (int) round(($dataHarian['omzet'] / $omzetMaksimumHarian) * 100))
                : 0;
            $dataHarian['tinggi_transaksi'] = $dataHarian['jumlah_transaksi'] > 0
                ? max(6, (int) round(($dataHarian['jumlah_transaksi'] / $transaksiMaksimumHarian) * 100))
                : 0;
        }
        unset($dataHarian);

        return view('admin-cabang.dashboard', [
            'namaCabang' => $branch['name'],
            'kodeCabang' => $branch['id'],
            'jumlahProduk' => $ringkasanStok->jumlah_produk,
            'totalStok' => $ringkasanStok->total_stok,
            'produkHampirHabis' => $produkHampirHabis,
            'jumlahTransaksiMingguan' => $transaksiMingguan->count(),
            'omzetMingguan' => $omzetMingguan,
            'grafikHarian' => $grafikHarian,
        ]);
    }
}
