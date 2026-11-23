<?php

namespace App\Services\Ai;

use App\Services\Ai\Tools\AssistantTool;
use App\Services\Ai\Tools\ManualTool;
use App\Services\Ai\Tools\SqlQueryTool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Responde en lenguaje natural. Las cifras salen de SELECT de solo lectura.
 * El uso de la app sale del manual, limitado a las pantallas que el usuario puede abrir.
 */
class DataAssistant
{
    private const ANSWER_NOW = 'Ya no puedes hacer más consultas. Responde ahora al usuario con los datos que ya obtuviste. Si no alcanzan para responder todo, di qué encontraste y qué faltó.';

    /** @var array<string, AssistantTool> */
    private array $tools = [];

    public function __construct(
        private GeminiClient $client,
        private SchemaCatalog $schema,
    ) {
        foreach ([SqlQueryTool::class, ManualTool::class] as $class) {
            $tool = app($class);
            $this->tools[$tool->name()] = $tool;
        }
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return array{answer: string, steps: list<array{tool: string, arguments: array<string, mixed>, result: array<string, mixed>}>, seconds: float}
     */
    public function ask(string $question, array $history = []): array
    {
        $start = microtime(true);
        $callsBefore = count($this->client->calls());
        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            array_slice($history, -6),
            [['role' => 'user', 'content' => $question]],
        );

        $definitions = array_values(array_map(fn (AssistantTool $tool) => $tool->definition(), $this->tools));
        $steps = [];
        $answer = '';
        $maxRounds = (int) config('ai.max_tool_rounds');
        $nudges = 0;

        for ($round = 0; $round <= $maxRounds + 1; $round++) {
            $canQuery = $round < $maxRounds && $nudges === 0;
            if (! $canQuery && $nudges === 0) {
                $messages[] = ['role' => 'user', 'content' => self::ANSWER_NOW];
                $nudges++;
            }

            $reply = $this->client->chat($messages, $canQuery ? $definitions : []);

            if ($reply['content'] !== '' && ($reply['tool_calls'] === [] || ! $canQuery)) {
                $answer = $reply['content'];
                break;
            }

            if ($reply['tool_calls'] === [] || ! $canQuery) {
                if ($nudges >= 2) {
                    break;
                }
                $messages[] = ['role' => 'user', 'content' => self::ANSWER_NOW];
                $nudges++;

                continue;
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $reply['content'],
                'model_parts' => $reply['model_parts'] ?? [],
                'tool_calls' => array_map(
                    fn ($call) => ['function' => ['name' => $call['name'], 'arguments' => $call['arguments']]],
                    $reply['tool_calls']
                ),
            ];

            foreach ($reply['tool_calls'] as $call) {
                $result = $this->runTool($call['name'], $call['arguments']);
                $steps[] = ['tool' => $call['name'], 'arguments' => $call['arguments'], 'result' => $result];
                $messages[] = [
                    'role' => 'tool',
                    'name' => $call['name'],
                    'content' => json_encode($this->forModel($result), JSON_UNESCAPED_UNICODE),
                ];
            }
        }

        if ($answer === '') {
            $answer = 'No pude armar una respuesta. Prueba reformulando la pregunta.';
        }

        $seconds = round(microtime(true) - $start, 1);
        Log::channel('ai')->info('Asistente IA', [
            'user_id' => auth()->id(),
            'question' => $question,
            'model' => $this->client->activeModel(),
            'tools' => array_map(fn ($step) => [
                'tool' => $step['tool'],
                'ok' => $step['result']['ok'] ?? null,
                'rows' => isset($step['result']['filas']) ? count($step['result']['filas']) : null,
                'message' => $step['result']['mensaje'] ?? null,
            ], $steps),
            'answer' => mb_substr($answer, 0, 500),
            'seconds' => $seconds,
            'gemini_calls' => array_slice($this->client->calls(), $callsBefore),
        ]);

        return ['answer' => $answer, 'steps' => $steps, 'seconds' => $seconds];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function runTool(string $name, array $arguments): array
    {
        $tool = $this->tools[$name] ?? null;
        if (! $tool) {
            return ['ok' => false, 'mensaje' => "No existe la consulta «{$name}»."];
        }

        DB::beginTransaction();
        try {
            return $tool->run($arguments);
        } catch (Throwable $e) {
            Log::channel('ai')->warning('Asistente IA: falló la consulta', [
                'tool' => $name,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'mensaje' => 'La consulta falló. Revisa los filtros.'];
        } finally {
            DB::rollBack();
        }
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function forModel(array $result): array
    {
        $max = (int) config('ai.max_rows_to_model');
        if (isset($result['filas']) && count($result['filas']) > $max) {
            $result['filas_omitidas'] = count($result['filas']) - $max;
            $result['filas'] = array_slice($result['filas'], 0, $max);
        }

        return array_filter($result, fn ($value) => $value !== null);
    }

    private function systemPrompt(): string
    {
        $today = now('America/Santiago')->format('Y-m-d');
        $schema = $this->schema->forPrompt();

        return <<<PROMPT
Eres el asistente de Melichinkul, la app de flota, conductores, mantenimientos y repuestos de un contratista Lipigas en Chile.
Hoy es {$today}. Respondes en español de Chile, de tú, en forma breve y clara.

Reglas:
- Si preguntan cómo se usa la aplicación, dónde está una pantalla o quién puede hacer algo, llama consultar_manual. No inventes menús ni pasos: usa el procedimiento que devuelva.
- Si consultar_manual responde sin_acceso, di que esa pantalla no está en su perfil y no describas los pasos.
- consultar_manual no tiene cifras. Para cualquier dato de la flota, conductores, alertas, mantenimientos, kilometraje, repuestos o compras, llama consultar_sql con un SELECT de PostgreSQL.
- Nunca inventes cifras. Copia los números del resultado.
- Si consultar_sql responde ok=false, corrige el SQL y vuelve a intentar una vez.
- En tablas con deleted_at, filtra deleted_at IS NULL salvo que pregunten por lo eliminado.
- Conductores activos: drivers.active = true y drivers.deleted_at IS NULL.
- Vehículos: la patente es vehicles.license_plate. El conductor actual es vehicles.current_driver_id.
- Licencia: drivers.license_expiration_date. Documentos del conductor: driver_documents (kind carnet, licencia u otro). El reverso, si existe, está en reverse_file_path.
- Alertas de licencia: alerts.type en licencia_por_vencer y licencia_vencida. Siguen pendientes cuando alerts.status = 'pending'.
- No consultes contraseñas ni tablas de sesión, caché, colas o migraciones.
- Si hay muchas filas, resume lo principal en vez de listar todo.

Esquema (PostgreSQL):
{$schema}
PROMPT;
    }
}
