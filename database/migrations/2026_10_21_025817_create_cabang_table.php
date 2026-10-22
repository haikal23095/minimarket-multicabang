<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection(env('DB_CONNECTION'));

        if (! $schema->hasTable('Cabang')) {
            $schema->create('Cabang', function (Blueprint $table) {
                $table->string('id_cabang', 10)->primary();
                $table->string('nama_cabang', 100);
                $table->text('alamat')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION'))->dropIfExists('Cabang');
    }
};
