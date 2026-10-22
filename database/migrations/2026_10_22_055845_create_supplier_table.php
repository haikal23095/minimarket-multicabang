<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (env('APP_NODE', 'malang') !== 'pusat') {
            return;
        }

        $schema = Schema::connection(env('DB_CONNECTION'));

        if (! $schema->hasTable('Supplier')) {
            $schema->create('Supplier', function (Blueprint $table) {
                $table->string('id_supplier', 20)->primary();
                $table->string('nama_supplier', 100);
                $table->string('no_hp', 20)->nullable();
                $table->text('alamat')->nullable();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (env('APP_NODE', 'malang') === 'pusat') {
            Schema::connection(env('DB_CONNECTION'))->dropIfExists('Supplier');
        }
    }
};
