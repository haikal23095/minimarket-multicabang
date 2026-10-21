<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlsrv_malang';

    public function up(): void
    {
        Schema::connection($this->connection)->create('Stok', function (Blueprint $table) {
            $table->string('id_cabang', 20);
            $table->string('id_produk', 20);
            $table->integer('jumlah_stok')->default(0);
            $table->primary(['id_cabang', 'id_produk']);
            $table->foreign('id_produk')->references('id_produk')->on('Produk')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('Stok');
    }
};
