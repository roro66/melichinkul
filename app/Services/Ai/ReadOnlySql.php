<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Ejecuta un único SELECT dentro de la transacción actual, en modo solo lectura.
 * Rechaza cualquier sentencia que pueda escribir o tocar tablas internas.
 */
class ReadOnlySql
{
    /** Tablas de framework, sesión, secretos o datos personales. No se documentan ni se pueden consultar. */
    public const DENIED_TABLES = [
        'sessions',
        'password_reset_tokens',
        'personal_access_tokens',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
        'job_batches',
        'migrations',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
        'notifications',
    ];

    /** Tablas de las que el asistente solo ve algunas columnas. */
    public const LIMITED_COLUMNS = [
        'users' => ['id', 'name', 'full_name', 'role', 'active'],
    ];

    /** Columnas que nunca deben salir en un resultado ni aparecer en el SQL. */
    public const DENIED_COLUMNS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    private const FORBIDDEN_WORDS = [
        'insert', 'update', 'delete', 'drop', 'alter', 'truncate', 'create', 'grant', 'revoke',
        'copy', 'execute', 'call', 'vacuum', 'merge', 'refresh', 'into', 'listen', 'notify',
        'prepare', 'reindex', 'load', 'do', 'set', 'reset',
    ];

    /**
     * @return array{ok: bool, mensaje?: string, columnas?: list<string>, filas?: list<array<string, mixed>>, consulta?: string}
     */
    public function run(string $sql): array
    {
        $clean = $this->stripComments(trim($sql));
        $clean = $this->stripTrailingSemicolon($clean);
        $reason = $this->rejectReason($clean);
        if ($reason !== null) {
            return ['ok' => false, 'mensaje' => $reason];
        }

        $max = max(1, (int) config('ai.max_sql_rows', 200));
        $executable = $this->withLimit($clean, $max);
        $timeout = max(1000, (int) config('ai.sql_timeout_ms', 8000));

        try {
            $db = DB::connection(config('ai.sql_connection', 'pgsql_ai'));
            $db->beginTransaction();
        } catch (Throwable $e) {
            return ['ok' => false, 'mensaje' => 'El usuario de solo lectura del asistente no está configurado (AI_DB_PASSWORD).'];
        }
        try {
            $db->unprepared('SET LOCAL transaction_read_only = on');
            $db->unprepared('SET LOCAL statement_timeout = '.$timeout);
            if (config('ai.sql_set_role')) {
                $db->unprepared('SET LOCAL ROLE "'.str_replace('"', '""', app(ReadOnlyDatabaseRole::class)->name()).'"');
            }
            $rows = $db->select($executable);
        } catch (Throwable $e) {
            return ['ok' => false, 'mensaje' => 'PostgreSQL rechazó la consulta: '.$this->short($e->getMessage()), 'consulta' => $executable];
        } finally {
            $db->rollBack();
        }

        $hitCap = count($rows) > $max;
        if ($hitCap) {
            $rows = array_slice($rows, 0, $max);
        }

        $filas = array_map(fn ($row) => $this->castRow((array) $row), $rows);
        $columnas = $filas === [] ? [] : array_keys($filas[0]);

        return [
            'ok' => true,
            'consulta' => $executable,
            'columnas' => $columnas,
            'filas' => $filas,
            'mensaje' => $hitCap
                ? "Mostré solo {$max} filas. Acota con WHERE o agrega (COUNT, SUM)."
                : ($filas === [] ? 'La consulta no devolvió filas.' : null),
        ];
    }

    public function rejectReason(string $sql): ?string
    {
        if ($sql === '') {
            return 'La consulta está vacía. Escribe un SELECT.';
        }
        if ($this->hasInteriorSemicolon($sql)) {
            return 'Solo se acepta una sentencia. No uses punto y coma en medio.';
        }
        if (! preg_match('/^\s*(select|with)\b/i', $sql)) {
            return 'Solo se acepta un SELECT (o WITH … SELECT).';
        }

        $masked = $this->maskStrings($sql);

        foreach (self::FORBIDDEN_WORDS as $word) {
            if (preg_match('/\b'.$word.'\b/i', $masked)) {
                return "No se puede usar «{$word}». La consulta tiene que ser solo de lectura.";
            }
        }
        if (preg_match('/\bfor\s+(update|share|no\s+key\s+update|key\s+share)\b/i', $masked)) {
            return 'No se puede bloquear filas (FOR UPDATE / FOR SHARE).';
        }
        if (preg_match('/\b(set_config|nextval|setval|pg_read_file|pg_read_binary_file|pg_ls_dir|pg_stat_file|pg_sleep|dblink|lo_import|lo_export|lo_unlink|pg_terminate_backend|pg_cancel_backend|pg_advisory_lock|pg_write_file)\b/i', $masked)) {
            return 'Esa función no está permitida.';
        }
        foreach (self::DENIED_COLUMNS as $column) {
            if (preg_match('/\b'.$column.'\b/i', $masked)) {
                return "No puedes consultar la columna «{$column}».";
            }
        }
        foreach (self::DENIED_TABLES as $table) {
            if (preg_match('/\b'.$table.'\b/i', $masked)) {
                return "La tabla «{$table}» no está disponible para el asistente.";
            }
        }
        if (preg_match('/\bu&["\']/i', $masked)) {
            return 'No se aceptan identificadores con escapes Unicode (U&"...").';
        }
        if (preg_match('/\b(pg_catalog|information_schema|pg_shadow|pg_authid|pg_user|pg_roles|pg_stat_activity|pg_settings)\b/i', $masked)) {
            return 'No puedes consultar el catálogo interno de PostgreSQL.';
        }

        return null;
    }

    private function withLimit(string $sql, int $max): string
    {
        if (preg_match('/\blimit\s+(\d+)\s*$/i', $sql, $match)) {
            if ((int) $match[1] > $max) {
                return (string) preg_replace('/\blimit\s+\d+\s*$/i', 'LIMIT '.$max, $sql);
            }

            return $sql;
        }

        return $sql.' LIMIT '.($max + 1);
    }

    private function stripTrailingSemicolon(string $sql): string
    {
        return trim((string) preg_replace('/;\s*$/', '', $sql));
    }

    private function hasInteriorSemicolon(string $sql): bool
    {
        $len = strlen($sql);
        $inString = false;
        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            if ($ch === "'") {
                if ($inString && $i + 1 < $len && $sql[$i + 1] === "'") {
                    $i++;

                    continue;
                }
                $inString = ! $inString;

                continue;
            }
            if ($ch === ';' && ! $inString) {
                return trim(substr($sql, $i + 1)) !== '';
            }
        }

        return false;
    }

    private function stripComments(string $sql): string
    {
        $out = '';
        $len = strlen($sql);
        $i = 0;
        while ($i < $len) {
            $ch = $sql[$i];
            if ($ch === "'") {
                $out .= "'";
                $i++;
                while ($i < $len) {
                    $out .= $sql[$i];
                    if ($sql[$i] === "'") {
                        if ($i + 1 < $len && $sql[$i + 1] === "'") {
                            $out .= $sql[++$i];
                            $i++;

                            continue;
                        }
                        $i++;
                        break;
                    }
                    $i++;
                }

                continue;
            }
            if ($ch === '-' && $i + 1 < $len && $sql[$i + 1] === '-') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }

                continue;
            }
            if ($ch === '/' && $i + 1 < $len && $sql[$i + 1] === '*') {
                $i += 2;
                while ($i < $len && ! ($sql[$i] === '*' && $i + 1 < $len && $sql[$i + 1] === '/')) {
                    $i++;
                }
                $i = min($len, $i + 2);
                $out .= ' ';

                continue;
            }
            $out .= $ch;
            $i++;
        }

        return trim($out);
    }

    private function maskStrings(string $sql): string
    {
        return (string) preg_replace("/'(?:''|[^'])*'/", "''", $sql);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function castRow(array $row): array
    {
        foreach (self::DENIED_COLUMNS as $column) {
            unset($row[$column]);
        }
        foreach ($row as $key => $value) {
            if (is_string($value) && mb_strlen($value) > 500) {
                $row[$key] = $value = mb_substr($value, 0, 500).'…';
            }
            if (is_string($value) && preg_match('/^-?\d+(\.\d+)?$/', $value) && strlen($value) <= 14) {
                $row[$key] = str_contains($value, '.') ? (float) $value : (int) $value;
            }
        }

        return $row;
    }

    private function short(string $message): string
    {
        $message = preg_replace('/\s+/', ' ', $message) ?? $message;

        return mb_substr(trim($message), 0, 400);
    }
}
