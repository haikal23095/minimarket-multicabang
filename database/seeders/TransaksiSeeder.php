<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\DetailTransaksi;
use App\Models\Pegawai;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $node = env('APP_NODE', 'malang');

        if ($node === 'pusat') {
            return;
        }

        $branch = match ($node) {
            'malang' => [
                'id' => 'MLG',
                'suffix' => 'MLG',
                'cashier_phone' => '081234567890',
                'admin_phone' => '081298765432',
            ],
            'surabaya' => [
                'id' => 'SBY',
                'suffix' => 'SBY',
                'cashier_phone' => '082234567890',
                'admin_phone' => '082298765432',
            ],
            default => throw new \InvalidArgumentException("APP_NODE tidak dikenal: {$node}"),
        };

        $cabang = Cabang::query()->findOrFail($branch['id']);
        $pegawaiKasir = Pegawai::query()->where('no_hp', $branch['cashier_phone'])->firstOrFail();
        $pegawaiAdmin = Pegawai::query()->where('no_hp', $branch['admin_phone'])->firstOrFail();
        $produk = [
            'PRD001' => Produk::query()->findOrFail('PRD001'),
            'PRD002' => Produk::query()->findOrFail('PRD002'),
            'PRD003' => Produk::query()->findOrFail('PRD003'),
        ];
        $tanggalTransaksi = now()->subDay()->setTime(10, 0);

        $transaksi = [
            [
                'id_transaksi' => 'TRX-'.$branch['suffix'].'-001',
                'no_faktur' => 'INV-'.$branch['suffix'].'-20261022-001',
                'id_pegawai' => $pegawaiKasir->id_pegawai,
                'tanggal_transaksi' => $tanggalTransaksi,
                'details' => [
                    ['id_produk' => 'PRD001', 'qty' => 2],
                    ['id_produk' => 'PRD002', 'qty' => 3],
                ],
            ],
            [
                'id_transaksi' => 'TRX-'.$branch['suffix'].'-002',
                'no_faktur' => 'INV-'.$branch['suffix'].'-20261022-002',
                'id_pegawai' => $pegawaiAdmin->id_pegawai,
                'tanggal_transaksi' => $tanggalTransaksi->copy()->addHours(2),
                'details' => [
                    ['id_produk' => 'PRD003', 'qty' => 1],
                ],
            ],
        ];

        foreach ($transaksi as $dataTransaksi) {
            $total = 0;

            foreach ($dataTransaksi['details'] as $detail) {
                $total += (float) $produk[$detail['id_produk']]->harga_jual * $detail['qty'];
            }

            Transaksi::updateOrCreate(
                ['id_transaksi' => $dataTransaksi['id_transaksi']],
                [
                    'no_faktur' => $dataTransaksi['no_faktur'],
                    'id_cabang' => $cabang->id_cabang,
                    'id_pegawai' => $dataTransaksi['id_pegawai'],
                    'tanggal_transaksi' => $dataTransaksi['tanggal_transaksi'],
                    'total_transaksi' => $total,
                    'bayar' => $total + 10000,
                ],
            );

            foreach ($dataTransaksi['details'] as $detail) {
                $product = $produk[$detail['id_produk']];
                $subtotal = (float) $product->harga_jual * $detail['qty'];

                DetailTransaksi::updateOrCreate(
                    [
                        'id_transaksi' => $dataTransaksi['id_transaksi'],
                        'id_produk' => $product->id_produk,
                    ],
                    [
                        'qty' => $detail['qty'],
                        'harga' => $product->harga_jual,
                        'subtotal' => $subtotal,
                    ],
                );
            }
        }
    }
}
