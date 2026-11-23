<?php

namespace App\Services\Ai\Tools;

use App\Services\Ai\ReadOnlySql;

class SqlQueryTool extends AssistantTool
{
    public function name(): string
    {
        return 'consultar_sql';
    }

    public function description(): string
    {
        return 'Ejecuta un SELECT de PostgreSQL de solo lectura. Una sola sentencia. Pon alias claros. El ORDER BY va al final. Si la tabla tiene deleted_at, filtra deleted_at IS NULL. No uses INSERT, UPDATE, DELETE ni punto y coma.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'sql' => [
                    'type' => 'string',
                    'description' => 'Un SELECT (o WITH … SELECT) de PostgreSQL. Sin punto y coma.',
                ],
            ],
            'required' => ['sql'],
        ];
    }

    public function run(array $args): array
    {
        return app(ReadOnlySql::class)->run((string) ($args['sql'] ?? ''));
    }
}
