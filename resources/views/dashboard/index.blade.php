@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Resumen del estado de la flota</p>
    </div>

    <!-- Estadísticas -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Vehículos Activos</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['vehiculos_activos'] }} / {{ $stats['vehiculos_total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Mantenimientos Programados</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['mantenimientos_programados'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Alertas Críticas</dt>
                            <dd class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $stats['alertas_criticas'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Mantenimientos en Proceso</dt>
                            <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['mantenimientos_en_proceso'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Alertas Pendientes</dt>
                            <dd class="text-lg font-semibold text-orange-600 dark:text-orange-400">{{ $stats['alertas_pendientes'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas recientes -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Alertas Recientes</h2>
            @if($alertas_recientes->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay alertas pendientes.</p>
            @else
                <div class="space-y-3">
                    @foreach($alertas_recientes as $alerta)
                        <div class="flex items-start justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $alerta->titulo }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $alerta->mensaje }}</p>
                                @if($alerta->vehiculo)
                                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Vehículo: {{ $alerta->vehiculo->patente }}</p>
                                @endif
                            </div>
                            <span class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $alerta->severidad === 'critica' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : ($alerta->severidad === 'advertencia' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300') }}">
                                {{ ucfirst($alerta->severidad) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Mantenimientos recientes -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Mantenimientos Recientes</h2>
            @if($mantenimientos_recientes->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No hay mantenimientos recientes.</p>
            @else
                <div class="space-y-3">
                    @foreach($mantenimientos_recientes as $mantenimiento)
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $mantenimiento->vehiculo->patente ?? 'N/A' }} - {{ ucfirst($mantenimiento->tipo) }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $mantenimiento->descripcion_trabajo ?? 'Sin descripción' }}</p>
                            </div>
                            <span class="ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $mantenimiento->estado === 'completado' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' }}">
                                {{ ucfirst(str_replace('_', ' ', $mantenimiento->estado)) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
