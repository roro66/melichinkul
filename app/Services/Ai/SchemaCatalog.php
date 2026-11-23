<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Esquema público, sin tablas internas, para que el modelo arme SELECT.
 */
class SchemaCatalog
{
    public function forPrompt(): string
    {
        $database = (string) DB::connection()->getDatabaseName();

        return Cache::remember('ai-schema-'.$database, 600, fn () => $this->build());
    }

    private function build(): string
    {
        $deniedTables = array_flip(ReadOnlySql::DENIED_TABLES);
        $deniedColumns = array_flip(ReadOnlySql::DENIED_COLUMNS);

        $columns = DB::select("
            SELECT c.table_name, c.column_name, c.data_type
            FROM information_schema.columns c
            JOIN information_schema.tables t
              ON t.table_schema = c.table_schema AND t.table_name = c.table_name
            WHERE c.table_schema = 'public' AND t.table_type = 'BASE TABLE'
            ORDER BY c.table_name, c.ordinal_position
        ");

        $byTable = [];
        foreach ($columns as $column) {
            if (isset($deniedTables[$column->table_name]) || isset($deniedColumns[$column->column_name])
                || str_starts_with($column->table_name, 'backup_previo_')) {
                continue;
            }
            $limited = ReadOnlySql::LIMITED_COLUMNS[$column->table_name] ?? null;
            if ($limited !== null && ! in_array($column->column_name, $limited, true)) {
                continue;
            }
            $type = match ($column->data_type) {
                'character varying', 'text', 'character' => 'text',
                'timestamp without time zone', 'timestamp with time zone' => 'timestamp',
                'double precision' => 'float',
                'bigint', 'integer', 'smallint' => 'int',
                'numeric' => 'numeric',
                'boolean' => 'bool',
                default => $column->data_type,
            };
            $byTable[$column->table_name][] = $column->column_name.' '.$type;
        }

        $lines = [];
        foreach ($byTable as $table => $cols) {
            $lines[] = $table.'('.implode(', ', $cols).')';
        }

        $fks = DB::select("
            SELECT tc.table_name, kcu.column_name, ccu.table_name AS foreign_table, ccu.column_name AS foreign_column
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON kcu.constraint_name = tc.constraint_name AND kcu.table_schema = tc.table_schema
            JOIN information_schema.constraint_column_usage ccu
              ON ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public'
            ORDER BY tc.table_name, kcu.column_name
        ");
        $relations = [];
        foreach ($fks as $fk) {
            if (isset($deniedTables[$fk->table_name]) || isset($deniedTables[$fk->foreign_table])) {
                continue;
            }
            $relations[] = $fk->table_name.'.'.$fk->column_name.'→'.$fk->foreign_table.'.'.$fk->foreign_column;
        }

        $text = implode("\n", $lines);
        if ($relations !== []) {
            $text .= "\n\nRelaciones:\n".implode("\n", $relations);
        }

        return $text;
    }
}
