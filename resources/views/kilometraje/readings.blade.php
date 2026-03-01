@extends('layouts.app')

@section('title', 'Lecturas de Kilometraje')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Lecturas de Kilometraje</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ver y corregir lecturas ingresadas por vehículo</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('kilometraje.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <i class="fas fa-plus mr-2"></i> Ingresar lecturas
            </a>
            <a href="{{ route('kilometraje.charts') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <i class="fas fa-chart-line mr-2"></i> Gráficos
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-green-800 dark:text-green-200">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <form method="GET" action="{{ route('kilometraje.readings') }}" class="flex flex-wrap items-end gap-4">
                <div class="min-w-[200px]">
                    <label for="vehicle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filtrar por vehículo</label>
                    <select name="vehicle_id" id="vehicle_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los vehículos</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" {{ (string)$selectedVehicleId === (string)$v->id ? 'selected' : '' }}>
                            {{ $v->license_plate }} — {{ $v->brand }} {{ $v->model }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg text-sm">
                    Filtrar
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Patente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Vehículo</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Kilometraje</th>
                        @can('mileage.edit')
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase w-24">Acciones</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($readings as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-nowrap">{{ $r->recorded_at->format('d-m-Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $r->vehicle->license_plate }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $r->vehicle->brand }} {{ $r->vehicle->model }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right font-mono">{{ number_format($r->mileage, 0, ',', '.') }} km</td>
                        @can('mileage.edit')
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('kilometraje.readings.edit', $r) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('mileage.edit') ? 5 : 4 }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No hay lecturas registradas{{ $selectedVehicleId ? ' para este vehículo' : '' }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($readings->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $readings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
