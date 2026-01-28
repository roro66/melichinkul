@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    html.dark .card-total-vehiculos,
    .dark .card-total-vehiculos {
        border-color: #facc15 !important;
        border-width: 2px !important;
    }
    html.dark .card-en-proceso,
    .dark .card-en-proceso {
        border-color: #fb923c !important;
        border-width: 2px !important;
    }
    html.dark .card-costo-mes,
    .dark .card-costo-mes {
        border-color: #86efac !important;
        border-width: 2px !important;
    }
    html.dark .card-en-mantenimiento,
    .dark .card-en-mantenimiento {
        border-color: #ef4444 !important;
        border-width: 2px !important;
    }
</style>
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Resumen del estado de la flota</p>
    </div>

    <!-- Cards de Métricas Principales -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Vehículos -->
        <div class="card-total-vehiculos bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/60 dark:to-indigo-800/60 overflow-hidden shadow rounded-lg border-2 border-indigo-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-car text-indigo-600 dark:text-yellow-400 text-3xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-indigo-700 dark:text-white truncate">Total Vehículos</dt>
                            <dd class="text-2xl font-bold text-indigo-900 dark:text-white">{{ $stats['vehiculos_total'] }}</dd>
                            <dd class="text-xs text-indigo-700 dark:text-gray-200 mt-1">
                                {{ $stats['vehiculos_activos'] }} activos
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mantenimientos en Proceso -->
        <div class="card-en-proceso bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/60 dark:to-yellow-800/60 overflow-hidden shadow rounded-lg border-2 border-yellow-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-wrench text-yellow-600 dark:text-orange-400 text-3xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-yellow-700 dark:text-white truncate">En Proceso</dt>
                            <dd class="text-2xl font-bold text-yellow-900 dark:text-white">{{ $stats['mantenimientos_en_proceso'] }}</dd>
                            <dd class="text-xs text-yellow-700 dark:text-gray-200 mt-1">
                                {{ $stats['mantenimientos_programados'] }} programados
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Costo del Mes -->
        <div class="card-costo-mes bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/60 dark:to-green-800/60 overflow-hidden shadow rounded-lg border-2 border-green-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign text-green-600 dark:text-green-300 text-3xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-green-700 dark:text-white truncate">Costo del Mes</dt>
                            <dd class="text-2xl font-bold text-green-900 dark:text-white">${{ number_format($costo_mes_actual, 0, ',', '.') }}</dd>
                            <dd class="text-xs text-green-700 dark:text-gray-200 mt-1">
                                {{ $stats['mantenimientos_completados_mes'] }} completados
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehículos en Mantenimiento -->
        <div class="card-en-mantenimiento bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/60 dark:to-red-800/60 overflow-hidden shadow rounded-lg border-2 border-red-200">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-500 text-3xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-red-700 dark:text-white truncate">En Mantenimiento</dt>
                            <dd class="text-2xl font-bold text-red-900 dark:text-white">{{ $stats['vehiculos_mantenimiento'] }}</dd>
                            <dd class="text-xs text-red-700 dark:text-gray-200 mt-1">
                                Requieren atención
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico de Costos -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-8 py-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Costos de Mantenimiento (Últimos 6 Meses)</h2>
                <div style="position: relative; height: 450px; padding: 30px 20px;">
                    <canvas id="costChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico por Tipo -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-8 py-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Gastos por Tipo (Últimos 6 Meses)</h2>
                <div style="position: relative; height: 450px; padding: 30px 20px;">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mantenimientos en Curso -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Mantenimientos en Curso</h2>
                    <a href="{{ route('mantenimientos.index') }}?status=in_progress" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                        Ver todos
                    </a>
                </div>
                @if($mantenimientos_en_curso->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-gray-400 dark:text-gray-400 text-4xl mb-2"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-300">No hay mantenimientos en curso</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($mantenimientos_en_curso as $maintenance)
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $maintenance->vehicle->license_plate ?? 'N/A' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $maintenance->type === 'preventive' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                                           ($maintenance->type === 'corrective' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                                           'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300') }}">
                                        {{ ucfirst($maintenance->type) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                                    {{ $maintenance->work_description }}
                                </p>
                                @if($maintenance->start_date)
                                <p class="text-xs text-gray-400 dark:text-gray-300 mt-1">
                                    Iniciado: {{ $maintenance->start_date->format('d/m/Y') }}
                                    @if($maintenance->start_date->diffInDays(now()) > 0)
                                        <span class="ml-2 text-yellow-600 dark:text-yellow-300">
                                            ({{ $maintenance->start_date->diffInDays(now()) }} días)
                                        </span>
                                    @endif
                                </p>
                                @endif
                            </div>
                            <a href="{{ route('mantenimientos.show', $maintenance->id) }}" 
                               class="ml-3 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Próximos Mantenimientos -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Próximos Mantenimientos</h2>
                    <a href="{{ route('mantenimientos.index') }}?status=scheduled" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                        Ver todos
                    </a>
                </div>
                @if($proximos_mantenimientos->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-check text-gray-400 dark:text-gray-400 text-4xl mb-2"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-300">No hay mantenimientos programados</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($proximos_mantenimientos as $maintenance)
                        <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $maintenance->vehicle->license_plate ?? 'N/A' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $maintenance->type === 'preventive' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                                           ($maintenance->type === 'corrective' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                                           'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300') }}">
                                        {{ ucfirst($maintenance->type) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">
                                    {{ $maintenance->work_description }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-300 mt-1">
                                    Programado: {{ $maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}
                                    @if($maintenance->scheduled_date && $maintenance->scheduled_date->isPast())
                                        <span class="ml-2 text-red-600 dark:text-red-300 font-medium">¡Vencido!</span>
                                    @elseif($maintenance->scheduled_date && $maintenance->scheduled_date->diffInDays(now()) <= 7)
                                        <span class="ml-2 text-yellow-600 dark:text-yellow-300">Próximo</span>
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('mantenimientos.show', $maintenance->id) }}" 
                               class="ml-3 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Vehículos que Requieren Atención -->
    @if($vehiculos_atencion->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Vehículos que Requieren Atención</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($vehiculos_atencion as $vehicle)
                <div class="p-4 border border-yellow-200 dark:border-yellow-800 rounded-lg bg-yellow-50 dark:bg-yellow-900/20">
                    <div class="flex items-center justify-between mb-2">
                        <a href="{{ route('vehiculos.show', $vehicle->id) }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                            {{ $vehicle->license_plate }}
                        </a>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                            {{ ucfirst($vehicle->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ $vehicle->brand }} {{ $vehicle->model }}</p>
                    @if($vehicle->maintenances->count() > 0)
                    <div class="space-y-1">
                        @foreach($vehicle->maintenances->take(2) as $maintenance)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <i class="fas fa-wrench mr-1"></i>
                            {{ $maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }} - {{ $maintenance->work_description }}
                        </p>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Top Vehículos por Costo -->
    @if($top_vehiculos_costo->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-6 sm:px-8 sm:py-8">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Top 5 Vehículos por Costo (Últimos 6 Meses)</h2>
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Vehículo</th>
                            <th class="px-8 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Costo Total</th>
                            <th class="px-8 py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($top_vehiculos_costo as $vehicle)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="text-base font-semibold text-gray-900 dark:text-white">{{ $vehicle->license_plate }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $vehicle->brand }} {{ $vehicle->model }}</div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap">
                                <div class="text-base font-bold text-gray-900 dark:text-white">
                                    ${{ number_format($vehicle->maintenances_sum_total_cost ?? 0, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="px-8 py-5 whitespace-nowrap text-right">
                                <a href="{{ route('vehiculos.show', $vehicle->id) }}" 
                                   class="inline-flex items-center justify-center w-10 h-10 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">
                                    <i class="fas fa-eye text-lg"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    'use strict';
    
    // Evitar múltiples inicializaciones
    if (window.dashboardChartsInitialized) {
        return;
    }
    window.dashboardChartsInitialized = true;

    document.addEventListener('DOMContentLoaded', function() {
        // Detectar modo oscuro una sola vez
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#f3f4f6' : '#111827';
        const gridColor = isDark ? '#4b5563' : '#e5e7eb';

        // Gráfico de Costos Mensuales
        const costCtx = document.getElementById('costChart');
        if (costCtx && !costCtx.chart) {
            costCtx.chart = new Chart(costCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_column($costos_mensuales, 'mes')) !!},
                    datasets: [{
                        label: 'Costo Mensual',
                        data: {!! json_encode(array_column($costos_mensuales, 'costo')) !!},
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: textColor,
                                font: {
                                    size: 14,
                                    weight: '600'
                                },
                                padding: 20
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Costo: $' + new Intl.NumberFormat('es-CL').format(context.parsed.y);
                                }
                            },
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 14,
                                    weight: '500'
                                },
                                padding: 15,
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return '$' + (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return '$' + (value / 1000).toFixed(0) + 'K';
                                    }
                                    return '$' + new Intl.NumberFormat('es-CL').format(value);
                                }
                            },
                            grid: {
                                color: gridColor,
                                lineWidth: isDark ? 1.5 : 1
                            },
                            title: {
                                display: true,
                                text: 'Costo (CLP)',
                                color: textColor,
                                font: {
                                    size: 16,
                                    weight: '600'
                                },
                                padding: {
                                    top: 15,
                                    bottom: 15
                                }
                            }
                        },
                        x: {
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 14,
                                    weight: '500'
                                },
                                padding: 15,
                                maxRotation: 45,
                                minRotation: 45
                            },
                            grid: {
                                color: gridColor,
                                lineWidth: isDark ? 1.5 : 1
                            },
                            title: {
                                display: true,
                                text: 'Mes',
                                color: textColor,
                                font: {
                                    size: 16,
                                    weight: '600'
                                },
                                padding: {
                                    top: 15,
                                    bottom: 15
                                }
                            }
                        }
                    }
                }
            });
        }

        // Gráfico por Tipo de Mantenimiento
        const typeCtx = document.getElementById('typeChart');
        if (typeCtx && !typeCtx.chart) {
            typeCtx.chart = new Chart(typeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Preventivos', 'Correctivos', 'Inspecciones'],
                    datasets: [{
                        data: [
                            {{ $stats_por_tipo['preventive'] }},
                            {{ $stats_por_tipo['corrective'] }},
                            {{ $stats_por_tipo['inspection'] }}
                        ],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(168, 85, 247, 0.8)'
                        ],
                        borderColor: [
                            'rgb(59, 130, 246)',
                            'rgb(249, 115, 22)',
                            'rgb(168, 85, 247)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            align: 'center',
                            labels: {
                                color: textColor,
                                font: {
                                    size: 15,
                                    weight: '600'
                                },
                                padding: 25,
                                boxWidth: 25,
                                boxHeight: 25,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': $' + new Intl.NumberFormat('es-CL').format(value) + ' (' + percentage + '%)';
                                }
                            },
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12
                        }
                    }
                }
            });
        }
    });
})();
</script>
@endpush
@endsection
