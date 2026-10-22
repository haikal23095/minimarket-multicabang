<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection(env('DB_CONNECTION'));

        if (! $schema->hasTable('Produk')) {
            $schema->create('Produk', function (Blueprint $table) {
                $table->string('id_produk', 20)->primary();
                $table->string('id_kategori', 20);
                $table->string('nama_produk', 150);
                $table->decimal('harga_jual', 12, 2);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION'))->dropIfExists('Produk');
    }
};
