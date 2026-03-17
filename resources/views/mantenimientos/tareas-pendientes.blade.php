@extends('layouts.app')

@section('title', 'Tareas pendientes')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tareas pendientes</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Mantenimientos no terminados (excluye completados y cancelados).
                @if(auth()->user()->hasRole('technician') && !auth()->user()->hasAnyRole(['administrator', 'supervisor', 'administrativo']))
                    Solo los asignados a ti.
                @else
                    Vista de todo el equipo.
                @endif
            </p>
        </div>
        <a href="{{ route('mantenimientos.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            <i class="fas fa-list mr-2"></i> Listado completo
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Vehículo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Fecha prog.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Técnico</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($maintenances as $m)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">#{{ $m->id }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $m->vehicle->license_plate ?? '—' }}</span>
                            <span class="block text-xs text-gray-500">{{ $m->vehicle->brand ?? '' }} {{ $m->vehicle->model ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ __('mantenimiento.types.' . $m->type, [], 'es') }}</td>
                        <td class="px-4 py-3 text-sm">{{ __('mantenimiento.statuses.' . $m->status, [], 'es') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $m->scheduled_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $m->responsibleTechnician?->name ?? $m->responsibleTechnician?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-sm space-x-2">
                            <a href="{{ route('mantenimientos.show', $m->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver</a>
                            @can('maintenances.record_work')
                            @if((int)$m->responsible_technician_id === (int)auth()->id() && !in_array($m->status, ['completed', 'cancelled'], true))
                            <a href="{{ route('mantenimientos.registrar-trabajo', $m) }}" class="text-amber-600 dark:text-amber-400 hover:underline">Trabajo</a>
                            @endif
                            @endcan
                            @can('maintenances.edit')
                            <a href="{{ route('mantenimientos.edit', $m->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Editar</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">No hay tareas pendientes.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($maintenances->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $maintenances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
