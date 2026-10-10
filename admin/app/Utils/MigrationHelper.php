<?php

namespace App\Utils;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MigrationHelper
{
    public static function columnType(string $table, string $column): ?string
    {
        $row = DB::selectOne(
            'SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row && $row->t ? strtolower(preg_replace('/\(\d+\)/', '', $row->t)) : null;
    }

    /**
     * Ajoute une clé étrangère seulement si les types de colonnes sont identiques
     * (le socle 6valley mélange bigint signé/non signé : une FK incompatible ferait échouer la migration).
     */
    public static function addForeignKeyIfCompatible(
        string $table,
        string $column,
        string $refTable,
        string $refColumn = 'id',
        string $onDelete = 'cascade'
    ): void {
        $a = self::columnType($table, $column);
        $b = self::columnType($refTable, $refColumn);

        if (!$a || !$b || $a !== $b) {
            Log::warning("FK {$table}.{$column} -> {$refTable}.{$refColumn} ignorée (types {$a} / {$b}).");
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($column, $refTable, $refColumn, $onDelete) {
                $t->foreign($column)->references($refColumn)->on($refTable)->onDelete($onDelete);
            });
        } catch (\Throwable $e) {
            Log::warning("FK {$table}.{$column} ignorée : " . $e->getMessage());
        }
    }
}
