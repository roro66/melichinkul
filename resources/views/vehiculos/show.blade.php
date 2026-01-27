@extends('layouts.app')

@section('title', 'Detalle de Vehículo')

@section('content')
<div class="space-y-6">
    @php
        $vehiculo = \App\Models\Vehiculo::with(['categoria', 'conductorActual', 'certificaciones', 'mantenimientos'])->findOrFail($id);
    @endphp

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $vehiculo->patente }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $vehiculo->marca }} {{ $vehiculo->modelo }} ({{ $vehiculo->anio }})</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('vehiculos.edit', $vehiculo->id) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-colors duration-150">
                Editar
            </a>
            <a href="{{ route('vehiculos.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                Volver
            </a>
        </div>
    </div>

    <!-- Información General -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Categoría:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $vehiculo->categoria->nombre ?? 'Sin categoría' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $vehiculo->estado === 'activo' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                       ($vehiculo->estado === 'mantenimiento' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                       ($vehiculo->estado === 'baja' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 
                       'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300')) }}">
                    {{ ucfirst($vehiculo->estado) }}
                </span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Combustible:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ ucfirst($vehiculo->tipo_combustible) }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Conductor Actual:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $vehiculo->conductorActual->nombre_completo ?? 'Sin asignar' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Kilometraje Actual:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ number_format($vehiculo->kilometraje_actual, 0, ',', '.') }} km</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Horómetro Actual:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ number_format($vehiculo->horometro_actual, 0, ',', '.') }} hrs</p>
            </div>
        </div>
    </div>

    <!-- Mantenimientos Recientes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Mantenimientos Recientes</h2>
        @if($vehiculo->mantenimientos->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No hay mantenimientos registrados.</p>
        @else
            <div class="space-y-3">
                @foreach($vehiculo->mantenimientos->take(5) as $mantenimiento)
                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($mantenimiento->tipo) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $mantenimiento->fecha_programada?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $mantenimiento->estado === 'completado' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                               ($mantenimiento->estado === 'en_proceso' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                               'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300') }}">
                            {{ ucfirst(str_replace('_', ' ', $mantenimiento->estado)) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
