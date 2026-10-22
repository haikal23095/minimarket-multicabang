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

        if (! $schema->hasTable('Transaksi')) {
            $schema->create('Transaksi', function (Blueprint $table) {
                $table->string('id_transaksi', 25)->primary();
                $table->string('no_faktur', 50)->unique();
                $table->string('id_cabang', 10);
                $table->foreignId('id_pegawai')->constrained('Pegawai', 'id_pegawai');
                $table->dateTime('tanggal_transaksi');
                $table->decimal('total_transaksi', 12, 2);
                $table->decimal('bayar', 12, 2);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(env('APP_NODE', 'malang'), ['malang', 'surabaya'], true)) {
            Schema::connection(env('DB_CONNECTION'))->dropIfExists('Transaksi');
        }
    }
};
