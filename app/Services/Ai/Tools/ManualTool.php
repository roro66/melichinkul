<?php

namespace App\Services\Ai\Tools;

use App\Services\Ai\AppManual;

class ManualTool extends AssistantTool
{
    public function name(): string
    {
        return 'consultar_manual';
    }

    public function description(): string
    {
        return 'Explica cómo se usa Melichinkul: menús, pasos y quién puede abrir cada pantalla. Úsalo para preguntas de funcionamiento (vehículos, conductores, licencia, carnet, alertas, mantenimientos, kilometraje, repuestos, compras). No trae cifras.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'tema' => [
                    'type' => 'string',
                    'description' => 'Tema de la pregunta, con las palabras de la pantalla. Ejemplo: subir licencia anverso y reverso.',
                ],
            ],
            'required' => ['tema'],
        ];
    }

    public function run(array $args): array
    {
        return app(AppManual::class)->search((string) ($args['tema'] ?? ''), auth()->user());
    }
}
