<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\DB;

/**
 * Usuario de PostgreSQL con el que el asistente ejecuta sus SELECT: sin superusuario,
 * solo lectura y solo sobre las tablas de negocio. Lo que este usuario no ve, el asistente
 * no lo puede leer aunque el modelo arme un SQL que esquive los filtros de ReadOnlySql.
 *
 * Se vuelve a aplicar después de cada migrate; una tabla nueva queda fuera hasta entonces.
 */
class ReadOnlyDatabaseRole
{
    public function name(): string
    {
        return (string) config('ai.sql_role', 'melichinkul_ai');
    }

    public function sync(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || ! $this->canManageRoles()) {
            return;
        }

        $role = $this->name();
        $quotedRole = $this->identifier($role);
        $database = $this->identifier((string) DB::connection()->getDatabaseName());

        if (! DB::selectOne('SELECT 1 AS ok FROM pg_roles WHERE rolname = ?', [$role])) {
            DB::statement("CREATE ROLE {$quotedRole} NOLOGIN");
        }
        DB::statement("ALTER ROLE {$quotedRole} NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION NOBYPASSRLS");
        $password = (string) config('database.connections.pgsql_ai.password');
        if ($password !== '') {
            DB::statement("ALTER ROLE {$quotedRole} LOGIN PASSWORD ".DB::getPdo()->quote($password));
        }
        DB::statement("ALTER ROLE {$quotedRole} SET default_transaction_read_only = on");
        DB::statement("ALTER ROLE {$quotedRole} SET statement_timeout = '15s'");

        DB::statement("GRANT CONNECT ON DATABASE {$database} TO {$quotedRole}");
        DB::statement("GRANT USAGE ON SCHEMA public TO {$quotedRole}");
        DB::statement("REVOKE ALL ON ALL TABLES IN SCHEMA public FROM {$quotedRole}");
        DB::statement("REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM {$quotedRole}");

        foreach ($this->readableTables() as $table) {
            $columns = ReadOnlySql::LIMITED_COLUMNS[$table] ?? null;
            $columnList = $columns === null
                ? ''
                : ' ('.implode(', ', array_map(fn ($c) => $this->identifier($c), $columns)).')';
            DB::statement("GRANT SELECT{$columnList} ON TABLE public.{$this->identifier($table)} TO {$quotedRole}");
        }
    }

    /**
     * @return list<string>
     */
    public function readableTables(): array
    {
        $denied = array_flip(ReadOnlySql::DENIED_TABLES);

        return collect(DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"))
            ->pluck('tablename')
            ->reject(fn ($table) => isset($denied[$table]) || str_starts_with($table, 'backup_previo_'))
            ->values()
            ->all();
    }

    private function canManageRoles(): bool
    {
        $me = DB::selectOne('SELECT rolsuper, rolcreaterole FROM pg_roles WHERE rolname = current_user');

        return $me && ($me->rolsuper || $me->rolcreaterole);
    }

    private function identifier(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }
}
