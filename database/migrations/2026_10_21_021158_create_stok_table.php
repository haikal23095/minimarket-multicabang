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

        if (! $schema->hasTable('Stok')) {
            $schema->create('Stok', function (Blueprint $table) {
                $table->string('id_cabang', 10);
                $table->string('id_produk', 20);
                $table->integer('jumlah_stok')->default(0);
                $table->primary(['id_cabang', 'id_produk']);
                $table->foreign('id_produk')->references('id_produk')->on('Produk')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(env('APP_NODE', 'malang'), ['malang', 'surabaya'], true)) {
            Schema::connection(env('DB_CONNECTION'))->dropIfExists('Stok');
        }
    }
};
