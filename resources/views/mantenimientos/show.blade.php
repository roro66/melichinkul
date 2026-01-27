@extends('layouts.app')

@section('title', 'Detalle de Mantenimiento')

@section('content')
<div class="space-y-6">
    @php
        $mantenimiento = \App\Models\Mantenimiento::with(['vehiculo', 'tecnicoResponsable', 'conductorAsignado'])->findOrFail($id);
    @endphp

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mantenimiento #{{ $mantenimiento->id }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $mantenimiento->vehiculo->patente }} - {{ ucfirst($mantenimiento->tipo) }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('mantenimientos.edit', $mantenimiento->id) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-colors duration-150">
                Editar
            </a>
            <a href="{{ route('mantenimientos.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                Volver
            </a>
        </div>
    </div>

    <!-- Información General -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Vehículo:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->vehiculo->patente }} - {{ $mantenimiento->vehiculo->marca }} {{ $mantenimiento->vehiculo->modelo }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $mantenimiento->tipo === 'preventivo' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                       ($mantenimiento->tipo === 'correctivo' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                       'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300') }}">
                    {{ ucfirst($mantenimiento->tipo) }}
                </span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $mantenimiento->estado === 'completado' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                       ($mantenimiento->estado === 'en_proceso' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                       ($mantenimiento->estado === 'cancelado' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 
                       'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300')) }}">
                    {{ ucfirst(str_replace('_', ' ', $mantenimiento->estado)) }}
                </span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Programada:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->fecha_programada?->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Inicio:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->fecha_inicio?->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Fin:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->fecha_fin?->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
        </div>
    </div>

    <!-- Descripción y Trabajos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Descripción del Trabajo</h2>
        <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $mantenimiento->descripcion_trabajo }}</p>
        
        @if($mantenimiento->motivo_ingreso)
            <div class="mt-4">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Motivo de Ingreso:</span>
                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $mantenimiento->motivo_ingreso }}</p>
            </div>
        @endif

        @if($mantenimiento->trabajos_realizados)
            <div class="mt-4">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Trabajos Realizados:</span>
                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $mantenimiento->trabajos_realizados }}</p>
            </div>
        @endif
    </div>

    <!-- Costos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Costos</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo Repuestos:</span>
                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($mantenimiento->costo_repuestos, 0, ',', '.') }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo Mano de Obra:</span>
                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($mantenimiento->costo_mano_obra, 0, ',', '.') }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo Total:</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white">${{ number_format($mantenimiento->costo_total, 0, ',', '.') }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Horas Trabajadas:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->horas_trabajadas ?? 'N/A' }} hrs</p>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Información Adicional</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Técnico Responsable:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->tecnicoResponsable->name ?? 'Sin asignar' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Conductor Asignado:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->conductorAsignado->nombre_completo ?? 'Sin asignar' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Taller/Proveedor:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->taller_proveedor ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Kilometraje:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->kilometraje_en_mantenimiento ? number_format($mantenimiento->kilometraje_en_mantenimiento, 0, ',', '.') . ' km' : 'N/A' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Horómetro:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $mantenimiento->horometro_en_mantenimiento ? number_format($mantenimiento->horometro_en_mantenimiento, 0, ',', '.') . ' hrs' : 'N/A' }}</p>
            </div>
            @if($mantenimiento->observaciones)
                <div class="md:col-span-2">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Observaciones:</span>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $mantenimiento->observaciones }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
