<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sqlsrv_malang';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        foreach (['Cabang', 'Kategori', 'Produk'] as $tableName) {
            if (! $schema->hasColumn($tableName, 'created_at')) {
                $schema->table($tableName, function (Blueprint $table) {
                    $table->dateTime('created_at')->nullable();
                });
            }

            if (! $schema->hasColumn($tableName, 'updated_at')) {
                $schema->table($tableName, function (Blueprint $table) {
                    $table->dateTime('updated_at')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        foreach (['Cabang', 'Kategori', 'Produk'] as $tableName) {
            if ($schema->hasColumn($tableName, 'updated_at')) {
                $schema->table($tableName, function (Blueprint $table) {
                    $table->dropColumn('updated_at');
                });
            }

            if ($schema->hasColumn($tableName, 'created_at')) {
                $schema->table($tableName, function (Blueprint $table) {
                    $table->dropColumn('created_at');
                });
            }
        }
    }
};
