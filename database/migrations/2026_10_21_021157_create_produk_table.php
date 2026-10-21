<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlsrv_malang';

    public function up(): void
    {
        Schema::connection($this->connection)->create('Produk', function (Blueprint $table) {
            $table->string('id_produk', 20)->primary();
            $table->string('id_kategori', 20);
            $table->string('nama_produk', 150);
            $table->decimal('harga_jual', 12, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('Produk');
    }
};
