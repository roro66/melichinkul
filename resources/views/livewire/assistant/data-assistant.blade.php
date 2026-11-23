<div class="space-y-6">
    <style>
        .ia-hourglass {
            width: 1.7rem;
            height: 1.7rem;
            display: inline-block;
            flex: 0 0 auto;
            transform-origin: center;
            animation: ia-hourglass-flip 1.15s ease-in-out infinite;
        }
        @keyframes ia-hourglass-flip {
            0%, 40% { transform: rotate(0deg); }
            50%, 90% { transform: rotate(180deg); }
            100% { transform: rotate(360deg); }
        }
        .ia-processing {
            align-items: center;
            gap: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #000;
            background: #facc15;
            padding: 0.85rem 1rem;
        }
        .ia-processing-label,
        html.dark .ia-processing-label {
            color: #000 !important;
            font-size: 0.95rem;
            font-weight: 700;
        }
    </style>
    <div class="flex flex-wrap gap-2">
        @foreach ($examples as $index => $example)
            <button type="button" wire:click="useExample({{ $index }})" wire:loading.attr="disabled"
                class="px-3 py-1.5 text-sm rounded-full bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-300 dark:hover:bg-indigo-900/50">
                {{ $example }}
            </button>
        @endforeach
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4 min-h-48">
        @forelse ($conversation as $turn)
            <div class="{{ $turn['role'] === 'user' ? 'ml-8' : 'mr-8' }}">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                    {{ $turn['role'] === 'user' ? 'Tú' : 'Asistente' }}
                    @if (!empty($turn['seconds']))
                        <span>· {{ $turn['seconds'] }} s</span>
                    @endif
                </p>
                <div class="rounded-lg px-4 py-3 text-sm whitespace-pre-wrap {{ $turn['role'] === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' }}">{{ $turn['content'] }}</div>
                @if ($turn['role'] === 'assistant' && !empty($turn['steps']))
                    <div class="mt-2 space-y-2">
                        @foreach ($turn['steps'] as $step)
                            @if (($step['tool'] ?? '') === 'consultar_sql' && !empty($step['filas']))
                                <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-600">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400">
                                            <tr>
                                                @foreach ($step['columnas'] as $column)
                                                    <th class="px-2 py-1 text-left font-medium">{{ $column }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($step['filas'] as $row)
                                                <tr class="border-t border-gray-100 dark:border-gray-700">
                                                    @foreach ($step['columnas'] as $column)
                                                        <td class="px-2 py-1 text-gray-800 dark:text-gray-200">{{ $row[$column] ?? '' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p wire:loading.remove wire:target="ask,useExample" class="text-sm text-gray-500 dark:text-gray-400">Puedes preguntar, por ejemplo, cuántos móviles están activos o cómo se carga una licencia.</p>
        @endforelse
        <div wire:loading.flex wire:target="ask,useExample" role="status" aria-live="polite" class="ia-processing">
            <svg class="ia-hourglass shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#facc15" stroke="#000" stroke-width="1.4" stroke-linejoin="round" d="M6 2.5h12v2.2c0 .8-.4 1.6-1.1 2.1L13.2 10.5c-.7.5-.7 1.5 0 2l3.7 3.7c.7.5 1.1 1.3 1.1 2.1v2.2H6v-2.2c0-.8.4-1.6 1.1-2.1l3.7-3.7c.7-.5.7-1.5 0-2L7.1 6.8C6.4 6.3 6 5.5 6 4.7V2.5z"/>
                <path fill="#000" d="M8.2 4.2h7.6l-3.8 3.6-3.8-3.6z"/>
            </svg>
            <span class="ia-processing-label">Procesando la pregunta…</span>
        </div>
    </div>

    <form wire:submit="ask" class="flex flex-col sm:flex-row gap-3">
        <textarea wire:model="question" rows="2" maxlength="500" placeholder="Escribe tu pregunta"
            wire:loading.attr="disabled" wire:target="ask,useExample"
            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 disabled:opacity-60"></textarea>
        <div class="flex gap-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="ask,useExample"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm disabled:opacity-60">
                <span wire:loading.remove wire:target="ask,useExample">Preguntar</span>
                <span wire:loading.inline-flex wire:target="ask,useExample" class="items-center gap-2">
                    <svg class="ia-hourglass" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#facc15" stroke="#000" stroke-width="1.4" stroke-linejoin="round" d="M6 2.5h12v2.2c0 .8-.4 1.6-1.1 2.1L13.2 10.5c-.7.5-.7 1.5 0 2l3.7 3.7c.7.5 1.1 1.3 1.1 2.1v2.2H6v-2.2c0-.8.4-1.6 1.1-2.1l3.7-3.7c.7-.5.7-1.5 0-2L7.1 6.8C6.4 6.3 6 5.5 6 4.7V2.5z"/>
                    </svg>
                    Procesando…
                </span>
            </button>
            @if ($conversation !== [])
                <button type="button" wire:click="clear"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 rounded-lg text-sm">Limpiar</button>
            @endif
        </div>
    </form>
</div>
