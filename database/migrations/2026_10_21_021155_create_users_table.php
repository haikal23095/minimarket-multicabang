<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $node = env('APP_NODE', 'malang');
        $schema = Schema::connection(env('DB_CONNECTION'));

        if (! $schema->hasTable('users')) {
            $schema->create('users', function (Blueprint $table) use ($node) {
                $table->id();
                $table->foreignId('pegawai_id')->nullable();

                if (in_array($node, ['malang', 'surabaya'], true)) {
                    $table->foreign('pegawai_id')
                        ->references('id_pegawai')
                        ->on('Pegawai')
                        ->nullOnDelete();
                }

                $table->string('username', 50)->unique();
                $table->string('password');
                $table->enum('role', ['kasir', 'admin_cabang', 'admin_pusat'])->default('kasir');
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(env('DB_CONNECTION'))->dropIfExists('users');
    }
};
