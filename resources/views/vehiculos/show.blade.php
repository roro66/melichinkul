@extends('layouts.app')

@section('title', 'Ficha del Vehículo - ' . $vehicle->license_plate)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'resumen', selectedAlertId: null, showSnoozeModal: false }">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $vehicle->license_plate }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('vehiculos.edit', $vehicle->id) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-colors duration-150">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
            <a href="{{ route('vehiculos.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    <!-- Pestañas -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                <button @click="activeTab = 'resumen'" 
                    :class="activeTab === 'resumen' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-5 border-b-2 font-medium text-base transition-colors duration-150">
                    <i class="fas fa-info-circle mr-2"></i> Resumen
                </button>
                <button @click="activeTab = 'mantenimientos'" 
                    :class="activeTab === 'mantenimientos' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-5 border-b-2 font-medium text-base transition-colors duration-150">
                    <i class="fas fa-wrench mr-2"></i> Mantenimientos
                    <span class="ml-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded text-sm">{{ $vehicle->maintenances->count() }}</span>
                </button>
                <button @click="activeTab = 'estadisticas'" 
                    :class="activeTab === 'estadisticas' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-5 border-b-2 font-medium text-base transition-colors duration-150">
                    <i class="fas fa-chart-line mr-2"></i> Estadísticas
                </button>
                <button @click="activeTab = 'certificaciones'" 
                    :class="activeTab === 'certificaciones' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-5 border-b-2 font-medium text-base transition-colors duration-150">
                    <i class="fas fa-file-alt mr-2"></i> Certificaciones
                    <span class="ml-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded text-sm">{{ $vehicle->certifications->count() }}</span>
                </button>
                <button @click="activeTab = 'alertas'" 
                    :class="activeTab === 'alertas' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-5 border-b-2 font-medium text-base transition-colors duration-150">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Alertas
                    @if($vehicle->alerts->count() > 0)
                        <span class="ml-2 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 px-2.5 py-1 rounded text-sm">{{ $vehicle->alerts->count() }}</span>
                    @endif
                </button>
            </nav>
        </div>

        <div class="p-6 min-h-[32rem]">
            <!-- Pestaña: Resumen -->
            <div x-show="activeTab === 'resumen'" class="space-y-6">
                <!-- Información General -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Categoría</span>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ $vehicle->category->name ?? 'Sin categoría' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</span>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $vehicle->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                                   ($vehicle->status === 'maintenance' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                                   ($vehicle->status === 'decommissioned' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 
                                   'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300')) }}">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Combustible</span>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ ucfirst($vehicle->fuel_type) }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Conductor Actual</span>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ $vehicle->currentDriver->full_name ?? 'Sin asignar' }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Kilometraje Actual</span>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($vehicle->current_mileage, 0, ',', '.') }} km</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Horómetro Actual</span>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($vehicle->current_hours, 0, ',', '.') }} hrs</p>
                    </div>
                </div>

                <!-- Próximos Mantenimientos -->
                @if($upcomingMaintenances->count() > 0)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Próximos Mantenimientos</h3>
                    <div class="space-y-3">
                        @foreach($upcomingMaintenances as $maintenance)
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $maintenance->type === 'preventive' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                                           ($maintenance->type === 'corrective' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                                           'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300') }}">
                                        {{ __('mantenimiento.types.' . $maintenance->type, [], 'es') }}
                                    </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $maintenance->work_description }}</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Programado: {{ $maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $maintenance->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                                       ($maintenance->status === 'pending_approval' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300' : 
                                       ($maintenance->status === 'in_progress' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                                       'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300')) }}">
                                    {{ __('mantenimiento.statuses.' . $maintenance->status, [], 'es') }}
                                </span>
                                <a href="{{ route('mantenimientos.show', $maintenance->id) }}" 
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Información Adicional -->
                @if($vehicle->observations || $vehicle->engine_number || $vehicle->chassis_number)
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información Adicional</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($vehicle->engine_number)
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Motor:</span>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $vehicle->engine_number }}</p>
                        </div>
                        @endif
                        @if($vehicle->chassis_number)
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Número de Chasis:</span>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $vehicle->chassis_number }}</p>
                        </div>
                        @endif
                        @if($vehicle->incorporation_date)
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Incorporación:</span>
                            <p class="text-sm text-gray-900 dark:text-white">{{ $vehicle->incorporation_date->format('d/m/Y') }}</p>
                        </div>
                        @endif
                        @if($vehicle->purchase_value)
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Valor de Compra:</span>
                            <p class="text-sm text-gray-900 dark:text-white">${{ number_format($vehicle->purchase_value, 0, ',', '.') }}</p>
                        </div>
                        @endif
                        @if($vehicle->observations)
                        <div class="md:col-span-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Observaciones:</span>
                            <p class="text-sm text-gray-900 dark:text-white mt-1">{{ $vehicle->observations }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Pestaña: Mantenimientos -->
            <div x-show="activeTab === 'mantenimientos'" class="space-y-4 w-full">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Historial de Mantenimientos</h3>
                    <a href="{{ route('mantenimientos.create') }}?vehicle_id={{ $vehicle->id }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150 text-sm">
                        <i class="fas fa-plus mr-2"></i> Nuevo Mantenimiento
                    </a>
                </div>
                
                @if($vehicle->maintenances->isEmpty())
                    <div class="text-center py-16">
                        <i class="fas fa-wrench text-gray-400 dark:text-gray-600 text-5xl mb-4"></i>
                        <p class="text-base text-gray-500 dark:text-gray-400">No hay mantenimientos registrados para este vehículo.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                        <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700 text-base">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Costo</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Técnico</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($vehicle->maintenances as $maintenance)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            {{ $maintenance->type === 'preventive' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                                               ($maintenance->type === 'corrective' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                                               'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300') }}">
                                            {{ __('mantenimiento.types.' . $maintenance->type, [], 'es') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            {{ $maintenance->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                                               ($maintenance->status === 'pending_approval' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300' : 
                                               ($maintenance->status === 'in_progress' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                                               ($maintenance->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 
                                               'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'))) }}">
                                            {{ __('mantenimiento.statuses.' . $maintenance->status, [], 'es') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white max-w-md truncate" title="{{ $maintenance->work_description }}">
                                            {{ $maintenance->work_description }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        ${{ number_format($maintenance->total_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $maintenance->responsibleTechnician->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('mantenimientos.show', $maintenance->id) }}" 
                                           class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('mantenimientos.edit', $maintenance->id) }}" 
                                           class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Pestaña: Estadísticas -->
            <div x-show="activeTab === 'estadisticas'" class="space-y-6 vehicle-stats-tab">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estadísticas de Gasto</h3>
                
                <!-- Cards de Resumen -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Gasto Total</p>
                                <p class="text-2xl font-bold text-blue-900 dark:text-white mt-1">${{ number_format($stats['total_cost'], 0, ',', '.') }}</p>
                            </div>
                            <i class="fas fa-dollar-sign text-blue-400 dark:text-blue-300 text-3xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-6 border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600 dark:text-green-300">Preventivos</p>
                                <p class="text-2xl font-bold text-green-900 dark:text-white mt-1">${{ number_format($stats['preventive_cost'], 0, ',', '.') }}</p>
                                <p class="text-xs text-green-600 dark:text-gray-300 mt-1">{{ $stats['preventive_count'] }} mantenimientos</p>
                            </div>
                            <i class="fas fa-shield-alt text-green-400 dark:text-green-300 text-3xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg p-6 border border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-orange-600 dark:text-orange-300">Correctivos</p>
                                <p class="text-2xl font-bold text-orange-900 dark:text-white mt-1">${{ number_format($stats['corrective_cost'], 0, ',', '.') }}</p>
                                <p class="text-xs text-orange-600 dark:text-gray-300 mt-1">{{ $stats['corrective_count'] }} mantenimientos</p>
                            </div>
                            <i class="fas fa-tools text-orange-400 dark:text-orange-300 text-3xl" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-6 border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600 dark:text-white">Total Mantenimientos</p>
                                <p class="text-2xl font-bold text-purple-900 dark:text-white mt-1">{{ $stats['total_count'] }}</p>
                                <p class="text-xs text-purple-600 dark:text-gray-300 mt-1">completados</p>
                            </div>
                            <i class="fas fa-list text-purple-400 dark:text-purple-300 text-3xl" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <!-- Desglose de Costos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Desglose por Componente</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Repuestos e Insumos</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($stats['parts_cost'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Mano de Obra</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($stats['labor_cost'], 0, ',', '.') }}</span>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Total</span>
                                <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($stats['total_cost'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Desglose por Tipo</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Preventivos</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($stats['preventive_cost'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Correctivos</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($stats['corrective_cost'], 0, ',', '.') }}</span>
                            </div>
                            @if($stats['inspection_cost'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Inspecciones</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">${{ number_format($stats['inspection_cost'], 0, ',', '.') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña: Certificaciones -->
            <div x-show="activeTab === 'certificaciones'" id="certificaciones" class="space-y-4 w-full">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Documentos Legales</h3>
                    <a href="{{ route('certificaciones.create', $vehicle->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150 text-sm">
                        <i class="fas fa-plus mr-2"></i> Nueva Certificación
                    </a>
                </div>
                
                @if($vehicle->certifications->isEmpty())
                    <div class="text-center py-16">
                        <i class="fas fa-file-alt text-gray-400 dark:text-gray-600 text-5xl mb-4"></i>
                        <p class="text-base text-gray-500 dark:text-gray-400">No hay certificaciones registradas para este vehículo.</p>
                        <a href="{{ route('certificaciones.create', $vehicle->id) }}" class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm">
                            <i class="fas fa-plus mr-2"></i> Agregar primera certificación
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                        <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-600 text-base">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Documento</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Vencimiento</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Archivos</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @php $certTypes = \App\Http\Controllers\CertificationController::CERT_TYPES; @endphp
                                @foreach($vehicle->certifications as $cert)
                                    @php
                                        $exp = $cert->expiration_date;
                                        $now = now();
                                        if ($exp->isPast()) {
                                            $estado = 'vencido';
                                            $estadoClase = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                                        } elseif ($exp->diffInDays($now) <= 30) {
                                            $estado = 'por vencer';
                                            $estadoClase = 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400';
                                        } else {
                                            $estado = 'vigente';
                                            $estadoClase = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-gray-900 dark:text-white text-sm">{{ $cert->name }}</span>
                                            <span class="block text-sm text-gray-500 dark:text-gray-400">{{ $certTypes[$cert->type] ?? $cert->type }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $cert->expiration_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $estadoClase }}">{{ ucfirst($estado) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($cert->attached_file)
                                                <a href="{{ route('certificaciones.view', [$cert->id, 1]) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline mr-2" title="Ver archivo 1"><i class="fas fa-external-link-alt"></i></a>
                                                <a href="{{ route('certificaciones.download', [$cert->id, 1]) }}" class="text-gray-600 dark:text-gray-400 hover:underline" title="Descargar archivo 1"><i class="fas fa-download"></i></a>
                                            @endif
                                            @if($cert->attached_file_2)
                                                <a href="{{ route('certificaciones.view', [$cert->id, 2]) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline ml-2 mr-2" title="Ver archivo 2"><i class="fas fa-external-link-alt"></i></a>
                                                <a href="{{ route('certificaciones.download', [$cert->id, 2]) }}" class="text-gray-600 dark:text-gray-400 hover:underline" title="Descargar archivo 2"><i class="fas fa-download"></i></a>
                                            @endif
                                            @if(!$cert->attached_file && !$cert->attached_file_2)
                                                <span class="text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('certificaciones.edit', $cert->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline mr-3" title="Editar"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('certificaciones.destroy', $cert->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta certificación?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Pestaña: Alertas -->
            <div x-show="activeTab === 'alertas'" class="space-y-4 w-full vehicle-alerts-tab">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Alertas del Vehículo</h3>
                @if($vehicle->alerts->isEmpty())
                    <div class="text-center py-16">
                        <i class="fas fa-check-circle text-green-500 dark:text-green-400 text-5xl mb-4"></i>
                        <p class="text-base text-gray-500 dark:text-gray-400">No hay alertas activas para este vehículo.</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Las alertas se generan automáticamente por mantenimientos próximos y documentos por vencer.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-600">
                        <table class="min-w-full w-full divide-y divide-gray-200 dark:divide-gray-700 text-base">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Severidad</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Título</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Fecha límite</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Pospuesta</th>
                                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $severityColors = [
                                        'informativa' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        'advertencia' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                        'critica' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                    ];
                                @endphp
                                @foreach($vehicle->alerts as $alert)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $severityColors[$alert->severity] ?? $severityColors['informativa'] }}">
                                                {{ ucfirst($alert->severity) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $alert->title }}</span>
                                            @if($alert->message)
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-xs" title="{{ $alert->message }}">{{ $alert->message }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $alert->due_date ? $alert->due_date->format('d/m/Y') : '—' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            @if($alert->snoozed_until && $alert->snoozed_until->isFuture())
                                                <span class="text-amber-600 dark:text-amber-400" title="{{ $alert->snoozed_reason }}">Hasta {{ $alert->snoozed_until->format('d/m/Y H:i') }}</span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @can('alerts.close')
                                            <form action="{{ route('alerts.close', $alert->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Cerrar esta alerta?');">
                                                @csrf
                                                <button type="submit" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:underline mr-3" title="Cerrar"><i class="fas fa-check"></i> Cerrar</button>
                                            </form>
                                            @endcan
                                            @can('alerts.snooze')
                                            <button type="button" @click="selectedAlertId = {{ $alert->id }}; showSnoozeModal = true" class="text-amber-600 dark:text-amber-300 hover:underline" title="Posponer"><i class="fas fa-clock"></i> Posponer</button>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal posponer alerta (desde ficha vehículo) -->
<div x-show="showSnoozeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity" @click="showSnoozeModal = false" aria-hidden="true"></div>
        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form :action="selectedAlertId ? '{{ url('/alertas') }}/' + selectedAlertId + '/posponer' : '#'" method="POST" class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                @csrf
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Posponer alerta</h3>
                <div class="space-y-4">
                    <div>
                        <label for="vehicle-snooze-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo (obligatorio)</label>
                        <textarea id="vehicle-snooze-reason" name="reason" rows="3" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Ej.: Trámite en curso"></textarea>
                    </div>
                    <div>
                        <label for="vehicle-snooze-hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horas (48–72)</label>
                        <input type="number" id="vehicle-snooze-hours" name="hours" value="48" min="48" max="72" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>
                <div class="mt-5 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg">Posponer</button>
                    <button type="button" @click="showSnoozeModal = false" class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-lg text-gray-900 dark:text-white">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
@endsection
