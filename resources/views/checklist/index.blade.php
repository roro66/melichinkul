@extends('layouts.app')

@section('title', 'Ítems de Checklist de Mantenimiento')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Checklist de Mantenimiento</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ítems que se muestran al técnico al ejecutar un mantenimiento (por tipo)</p>
        </div>
        @can('maintenances.create')
        <a href="{{ route('checklist.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">
            <i class="fas fa-plus mr-2"></i> Nuevo ítem
        </a>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Orden</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Descripción</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Obligatorio</th>
                    @can('maintenances.create')
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Acciones</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $item->sort_order }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                        @if($item->type)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $item->type === 'preventive' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                                   ($item->type === 'corrective' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                                   'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300') }}">
                                {{ __('mantenimiento.types.' . $item->type, [], 'es') }}
                            </span>
                        @else
                            <span class="text-gray-400 dark:text-gray-500">Todos</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $item->description }}">{{ $item->description ?: '—' }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($item->is_required)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">Sí</span>
                        @else
                            <span class="text-gray-400 dark:text-gray-500">No</span>
                        @endif
                    </td>
                    @can('maintenances.create')
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('checklist.edit', $item->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3" title="Editar"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('checklist.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este ítem?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->can('maintenances.create') ? 6 : 5 }}" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay ítems. @can('maintenances.create')<a href="{{ route('checklist.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Crear uno</a>@endcan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
