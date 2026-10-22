<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection(env('DB_CONNECTION'));
        $relations = [
            ['Produk', 'id_kategori', 'Kategori', 'id_kategori', 'fk_produk_id_kategori'],
        ];

        if (in_array(env('APP_NODE', 'malang'), ['malang', 'surabaya'], true)) {
            $relations[] = ['Stok', 'id_cabang', 'Cabang', 'id_cabang', 'fk_stok_id_cabang'];
            $relations[] = ['Transaksi', 'id_cabang', 'Cabang', 'id_cabang', 'fk_transaksi_id_cabang'];
        }

        foreach ($relations as [$tableName, $column, $foreignTable, $foreignColumn, $constraint]) {
            if (! $schema->hasTable($tableName) || ! $schema->hasTable($foreignTable)) {
                continue;
            }

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
        $schema = Schema::connection(env('DB_CONNECTION'));
        $constraints = [
            ['Produk', 'fk_produk_id_kategori'],
        ];

        if (in_array(env('APP_NODE', 'malang'), ['malang', 'surabaya'], true)) {
            $constraints[] = ['Stok', 'fk_stok_id_cabang'];
            $constraints[] = ['Transaksi', 'fk_transaksi_id_cabang'];
        }

        foreach ($constraints as [$tableName, $constraint]) {
            if (! $schema->hasTable($tableName)) {
                continue;
            }

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
