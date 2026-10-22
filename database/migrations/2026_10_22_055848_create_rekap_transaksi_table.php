<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (env('APP_NODE', 'malang') !== 'pusat') {
            return;
        }

        $schema = Schema::connection(env('DB_CONNECTION'));

        if (! $schema->hasTable('RekapTransaksi')) {
            $schema->create('RekapTransaksi', function (Blueprint $table) {
                $table->bigIncrements('id_rekap');
                $table->string('id_cabang', 10);
                $table->date('tanggal');
                $table->decimal('total_penjualan', 14, 2);
                $table->integer('jumlah_transaksi');
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
                $table->foreign('id_cabang', 'fk_rekap_transaksi_id_cabang')
                    ->references('id_cabang')
                    ->on('Cabang');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (env('APP_NODE', 'malang') === 'pusat') {
            Schema::connection(env('DB_CONNECTION'))->dropIfExists('RekapTransaksi');
        }
    }
};
