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

        if (! $schema->hasTable('Kategori')) {
            $schema->create('Kategori', function (Blueprint $table) {
                $table->string('id_kategori', 20)->primary();
                $table->string('nama_kategori', 100);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION'))->dropIfExists('Kategori');
    }
};
