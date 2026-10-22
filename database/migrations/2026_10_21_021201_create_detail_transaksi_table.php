<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(env('APP_NODE', 'malang'), ['malang', 'surabaya'], true)) {
            return;
        }

        $schema = Schema::connection(env('DB_CONNECTION'));

        if (! $schema->hasTable('DetailTransaksi')) {
            $schema->create('DetailTransaksi', function (Blueprint $table) {
                $table->id('id_detail');
                $table->string('id_transaksi', 25);
                $table->string('id_produk', 20);
                $table->integer('qty');
                $table->decimal('harga', 12, 2);
                $table->decimal('subtotal', 12, 2);
                $table->foreign('id_transaksi')->references('id_transaksi')->on('Transaksi')->cascadeOnDelete();
                $table->foreign('id_produk')->references('id_produk')->on('Produk');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(env('APP_NODE', 'malang'), ['malang', 'surabaya'], true)) {
            Schema::connection(env('DB_CONNECTION'))->dropIfExists('DetailTransaksi');
        }
    }
};
