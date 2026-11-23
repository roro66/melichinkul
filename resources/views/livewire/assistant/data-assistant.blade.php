<div class="space-y-6">
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
            <p class="text-sm text-gray-500 dark:text-gray-400">Puedes preguntar, por ejemplo, cuántos móviles están activos o cómo se carga una licencia.</p>
        @endforelse
        <p wire:loading class="text-sm text-gray-500 dark:text-gray-400">Consultando…</p>
    </div>

    <form wire:submit="ask" class="flex flex-col sm:flex-row gap-3">
        <textarea wire:model="question" rows="2" maxlength="500" placeholder="Escribe tu pregunta"
            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"></textarea>
        <div class="flex gap-2">
            <button type="submit" wire:loading.attr="disabled"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">Preguntar</button>
            @if ($conversation !== [])
                <button type="button" wire:click="clear"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 rounded-lg text-sm">Limpiar</button>
            @endif
        </div>
    </form>
</div>
