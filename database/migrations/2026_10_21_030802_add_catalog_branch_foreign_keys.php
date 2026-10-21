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
        $relations = [
            ['Produk', 'id_kategori', 'Kategori', 'id_kategori', 'fk_produk_id_kategori'],
            ['Stok', 'id_cabang', 'Cabang', 'id_cabang', 'fk_stok_id_cabang'],
            ['Transaksi', 'id_cabang', 'Cabang', 'id_cabang', 'fk_transaksi_id_cabang'],
        ];

        foreach ($relations as [$tableName, $column, $foreignTable, $foreignColumn, $constraint]) {
            $exists = collect($schema->getForeignKeys($tableName))->contains(
                fn (array $foreignKey): bool => $foreignKey['columns'] === [$column]
                    && strcasecmp($foreignKey['foreign_table'], $foreignTable) === 0
                    && $foreignKey['foreign_columns'] === [$foreignColumn],
            );

            if (! $exists) {
                $schema->table($tableName, function (Blueprint $table) use ($column, $foreignTable, $foreignColumn, $constraint) {
                    $table->foreign($column, $constraint)->references($foreignColumn)->on($foreignTable);
                });
            }
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);
        $constraints = [
            ['Produk', 'fk_produk_id_kategori'],
            ['Stok', 'fk_stok_id_cabang'],
            ['Transaksi', 'fk_transaksi_id_cabang'],
        ];

        foreach ($constraints as [$tableName, $constraint]) {
            $exists = collect($schema->getForeignKeys($tableName))
                ->contains(fn (array $foreignKey): bool => $foreignKey['name'] === $constraint);

            if ($exists) {
                $schema->table($tableName, function (Blueprint $table) use ($constraint) {
                    $table->dropForeign($constraint);
                });
            }
        }
    }
};
