<?php

namespace App\Livewire\Assistant;

use App\Services\Ai\DataAssistant as DataAssistantService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class DataAssistant extends Component
{
    public string $question = '';

    /** @var list<array{role: string, content: string, steps?: list<array<string, mixed>>, seconds?: float, error?: bool}> */
    #[Locked]
    public array $conversation = [];

    public const EXAMPLES = [
        '¿Cuántos vehículos activos hay?',
        '¿Qué conductores tienen la licencia por vencer o vencida?',
        '¿Cómo subo el carnet o la licencia de un conductor?',
        '¿Cómo se aplaza una alerta de licencia?',
    ];

    public function mount(): void
    {
        abort_unless(config('ai.enabled'), 404);
        abort_unless(Auth::user()?->isAdministrator(), 403);
    }

    public function useExample(int $index): void
    {
        $this->question = self::EXAMPLES[$index] ?? '';
        $this->ask(app(DataAssistantService::class));
    }

    public function ask(DataAssistantService $assistant): void
    {
        $question = trim($this->question);
        if ($question === '') {
            return;
        }

        @set_time_limit(240);
        $history = array_map(
            fn ($turn) => ['role' => $turn['role'], 'content' => $turn['content']],
            array_filter($this->conversation, fn ($turn) => empty($turn['error']))
        );

        $this->conversation[] = ['role' => 'user', 'content' => $question];
        $this->question = '';

        try {
            $result = $assistant->ask($question, array_values($history));
            $this->conversation[] = [
                'role' => 'assistant',
                'content' => $result['answer'],
                'steps' => array_map(fn ($step) => $this->compactStep($step), $result['steps']),
                'seconds' => $result['seconds'],
            ];
        } catch (Throwable $e) {
            report($e);
            Log::channel('ai')->error('Asistente IA: sin respuesta', [
                'user_id' => auth()->id(),
                'question' => $question,
                'error' => $e->getMessage(),
            ]);
            $this->conversation[] = [
                'role' => 'assistant',
                'content' => 'No pude consultar al asistente: '.$e->getMessage(),
                'error' => true,
            ];
        }
    }

    public function clear(): void
    {
        $this->conversation = [];
        $this->question = '';
    }

    /**
     * @param  array<string, mixed>  $step
     * @return array<string, mixed>
     */
    private function compactStep(array $step): array
    {
        $result = $step['result'];

        return [
            'tool' => $step['tool'],
            'ok' => (bool) ($result['ok'] ?? false),
            'mensaje' => $result['mensaje'] ?? null,
            'consulta' => $result['consulta'] ?? null,
            'columnas' => $result['columnas'] ?? [],
            'filas' => array_slice($result['filas'] ?? [], 0, 40),
        ];
    }

    public function render()
    {
        return view('livewire.assistant.data-assistant', [
            'examples' => self::EXAMPLES,
        ]);
    }
}
