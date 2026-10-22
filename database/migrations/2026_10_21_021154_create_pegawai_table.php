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

        if (! $schema->hasTable('Pegawai')) {
            $schema->create('Pegawai', function (Blueprint $table) {
                $table->id('id_pegawai');
                $table->string('nama_pegawai', 100);
                $table->string('jabatan', 50);
                $table->string('no_hp', 20)->nullable();
                $table->text('alamat')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (in_array(env('APP_NODE', 'malang'), ['malang', 'surabaya'], true)) {
            Schema::connection(env('DB_CONNECTION'))->dropIfExists('Pegawai');
        }
    }
};
