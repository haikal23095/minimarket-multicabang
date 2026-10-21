<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $connection = 'sqlsrv_malang';

    public function up(): void
    {
        Schema::connection($this->connection)->create('Transaksi', function (Blueprint $table) {
            $table->string('id_transaksi', 25)->primary();
            $table->string('no_faktur', 50)->unique();
            $table->string('id_cabang', 20);
            $table->foreignId('id_pegawai')->constrained('Pegawai', 'id_pegawai');
            $table->dateTime('tanggal_transaksi');
            $table->decimal('total_transaksi', 12, 2);
            $table->decimal('bayar', 12, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('Transaksi');
    }
};
