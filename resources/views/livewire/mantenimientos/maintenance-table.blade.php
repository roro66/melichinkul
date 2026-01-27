<div class="space-y-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <input type="text" wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por vehículo, descripción o motivo..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <select wire:model.live="vehiculoFilter" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos los vehículos</option>
                    @foreach($vehiculos as $vehiculo)
                        <option value="{{$vehiculo->id}}">{{$vehiculo->patente}} - {{$vehiculo->marca}} {{$vehiculo->modelo}}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="tipoFilter" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos los tipos</option>
                    <option value="preventivo">Preventivo</option>
                    <option value="correctivo">Correctivo</option>
                    <option value="inspeccion">Inspección</option>
                </select>
            </div>
            <div>
                <select wire:model.live="estadoFilter" 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos los estados</option>
                    <option value="programado">Programado</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th wire:click="sortBy('fecha_programada')" 
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                            Fecha Programada
                            @if($sortField === 'fecha_programada')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Vehículo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Tipo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Descripción
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Costo Total
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($mantenimientos as $mantenimiento)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{$mantenimiento->fecha_programada?->format('d/m/Y') ?? 'Sin fecha'}}
                                </div>
                                @if($mantenimiento->fecha_fin)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Fin: {{$mantenimiento->fecha_fin->format('d/m/Y')}}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{$mantenimiento->vehiculo->patente}}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{$mantenimiento->vehiculo->marca}} {{$mantenimiento->vehiculo->modelo}}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{$mantenimiento->tipo === 'preventivo' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                                       ($mantenimiento->tipo === 'correctivo' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                                       'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300')}}">
                                    {{ucfirst($mantenimiento->tipo)}}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{$mantenimiento->estado === 'completado' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                                       ($mantenimiento->estado === 'en_proceso' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                                       ($mantenimiento->estado === 'cancelado' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 
                                       'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'))}}">
                                    {{ucfirst(str_replace('_', ' ', $mantenimiento->estado))}}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate">
                                    {{$mantenimiento->descripcion_trabajo}}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    ${{number_format($mantenimiento->costo_total, 0, ',', '.')}}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{route('mantenimientos.show', $mantenimiento->id)}}" 
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">
                                    Ver
                                </a>
                                <a href="{{route('mantenimientos.edit', $mantenimiento->id)}}" 
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">
                                    Editar
                                </a>
                                <button wire:click="delete({{$mantenimiento->id}})" 
                                    wire:confirm="¿Estás seguro de eliminar este mantenimiento?"
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No se encontraron mantenimientos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{$mantenimientos->links()}}
        </div>
    </div>
</div>
