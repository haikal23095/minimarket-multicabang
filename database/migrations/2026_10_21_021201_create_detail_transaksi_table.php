<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlsrv_malang';

    public function up(): void
    {
        Schema::connection($this->connection)->create('DetailTransaksi', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('DetailTransaksi');
    }
};
