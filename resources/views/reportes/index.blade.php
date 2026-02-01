@extends('layouts.app')

@section('title', 'Reportes avanzados')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap justify-between items-start gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Reportes avanzados</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Estadísticas de fallas por vehículo y conductor, tendencias de costos, inventario/compras y análisis por conductor (últimos 12 meses).
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reportes.estado-flota-pdf') }}" target="_blank"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i> Estado flota (PDF)
            </a>
            <a href="{{ route('reportes.dashboard-ejecutivo-pdf') }}" target="_blank"
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i> Dashboard ejecutivo (PDF)
            </a>
        </div>
    </div>

    <!-- Resumen numérico -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-orange-100 dark:bg-orange-900/40 p-3">
                    <i class="fas fa-exclamation-triangle text-orange-600 dark:text-orange-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total fallas (correctivos)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $resumen['total_fallas'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-red-100 dark:bg-red-900/40 p-3">
                    <i class="fas fa-dollar-sign text-red-600 dark:text-red-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo por fallas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($resumen['costo_fallas'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-blue-100 dark:bg-blue-900/40 p-3">
                    <i class="fas fa-wrench text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mantenimientos completados</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $resumen['total_mantenimientos'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 rounded-lg bg-green-100 dark:bg-green-900/40 p-3">
                    <i class="fas fa-chart-line text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo total período</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($resumen['costo_total_periodo'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos: Fallas por vehículo y por conductor -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Fallas por vehículo (top 15)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Mantenimientos correctivos completados en los últimos 12 meses</p>
            </div>
            <div class="p-4" style="position: relative; height: 320px;">
                <canvas id="chartFallasVehiculo"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Fallas por conductor (top 15)</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Correctivos donde el conductor estaba asignado al mantenimiento</p>
            </div>
            <div class="p-4" style="position: relative; height: 320px;">
                <canvas id="chartFallasConductor"></canvas>
            </div>
        </div>
    </div>

    <!-- Tendencia de costos y Distribución por tipo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tendencia de costos por mes</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Preventivo, correctivo e inspección (últimos 12 meses)</p>
            </div>
            <div class="p-4" style="position: relative; height: 320px;">
                <canvas id="chartTendenciaCostos"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Distribución por tipo de mantenimiento</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cantidad completada en el período</p>
            </div>
            <div class="p-4" style="position: relative; height: 320px;">
                <canvas id="chartDistribucionTipo"></canvas>
            </div>
        </div>
    </div>

    <!-- Top vehículos por costo -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Top 10 vehículos por costo total</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Suma de mantenimientos completados en los últimos 12 meses</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Vehículo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Costo total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($topVehiculosCosto as $v)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $v->license_plate }}</span>
                            <span class="block text-sm text-gray-500 dark:text-gray-400">{{ $v->brand }} {{ $v->model }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900 dark:text-white">
                            ${{ number_format($v->maintenances_sum_total_cost ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('vehiculos.show', $v->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay datos en el período.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventario y compras -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Inventario y compras</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 p-3">
                        <i class="fas fa-shopping-cart text-emerald-600 dark:text-emerald-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Compras recibidas (período)</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($resumenInventario['total_compras_periodo'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-lg bg-cyan-100 dark:bg-cyan-900/40 p-3">
                        <i class="fas fa-exchange-alt text-cyan-600 dark:text-cyan-400 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Movimientos de inventario</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $resumenInventario['movimientos_count'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Compras por proveedor</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Monto total compras recibidas en el período</p>
                </div>
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900 sticky top-0">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Proveedor</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Compras</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @php $comprasConDatos = collect($comprasPorProveedor)->filter(fn($r) => ($r->compras_count ?? 0) > 0); @endphp
                            @forelse($comprasConDatos as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $row->name }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">{{ $row->compras_count }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-right font-medium text-gray-900 dark:text-white">${{ number_format($row->total_amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay compras en el período.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Compras por mes</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Tendencia últimos 12 meses</p>
                </div>
                <div class="p-4" style="position: relative; height: 280px;">
                    <canvas id="chartComprasMensuales"></canvas>
                </div>
            </div>
        </div>
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Movimientos por tipo</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cantidad de movimientos en el período</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cantidad mov.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unidades (neto)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($movimientosPorTipo as $mov)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ \App\Models\InventoryMovement::TYPES[$mov->type] ?? $mov->type }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">{{ $mov->movimientos_count }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">{{ $mov->cantidad_neto }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay movimientos en el período.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Análisis costos por conductor -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Análisis de costos por conductor</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top 10 conductores por costo (correctivos)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Suma de mantenimientos correctivos completados en los últimos 12 meses</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conductor</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fallas</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Costo total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Costo promedio</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @php $topConductoresConCosto = $topConductoresCosto->filter(function($c) { return ($c->maintenances_sum_total_cost ?? 0) > 0; }); @endphp
                        @forelse($topConductoresConCosto as $c)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $c->full_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">{{ $c->fallas_count ?? 0 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-900 dark:text-white">${{ number_format($c->maintenances_sum_total_cost ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">${{ number_format(($c->fallas_count ?? 0) > 0 ? round(($c->maintenances_sum_total_cost ?? 0) / $c->fallas_count) : 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('conductores.show', $c->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No hay costos por conductor en el período.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(count($conductoresTopIds) > 0)
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tendencia de costos correctivos por conductor (top 5)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Últimos 12 meses</p>
            </div>
            <div class="p-4" style="position: relative; height: 320px;">
                <canvas id="chartCostosConductor"></canvas>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    'use strict';

    function getChartColors() {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            isDark: isDark,
            textColor: isDark ? '#e5e7eb' : '#374151',
            gridColor: isDark ? '#4b5563' : '#e5e7eb'
        };
    }

    function updateChartColors(chart, colors) {
        if (!chart) return;
        if (chart.options.plugins?.legend?.labels) chart.options.plugins.legend.labels.color = colors.textColor;
        if (chart.options.plugins?.tooltip) {
            chart.options.plugins.tooltip.backgroundColor = colors.isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)';
            chart.options.plugins.tooltip.titleColor = colors.isDark ? '#fff' : '#111827';
            chart.options.plugins.tooltip.bodyColor = colors.isDark ? '#e5e7eb' : '#374151';
        }
        if (chart.options.scales) {
            Object.keys(chart.options.scales).forEach(function(k) {
                var s = chart.options.scales[k];
                if (s.ticks) s.ticks.color = colors.textColor;
                if (s.grid) s.grid.color = colors.gridColor;
                if (s.title) s.title.color = colors.textColor;
            });
        }
        chart.update('none');
    }

    document.addEventListener('DOMContentLoaded', function() {
        var colors = getChartColors();

        // Datos para gráficos (desde Blade)
        var fallasVehiculoLabels = @json($fallasPorVehiculo->where('fallas_count', '>', 0)->take(15)->pluck('license_plate'));
        var fallasVehiculoData = @json($fallasPorVehiculo->where('fallas_count', '>', 0)->take(15)->pluck('fallas_count'));

        var fallasConductorLabels = @json($fallasPorConductor->where('fallas_count', '>', 0)->take(15)->pluck('full_name'));
        var fallasConductorData = @json($fallasPorConductor->where('fallas_count', '>', 0)->take(15)->pluck('fallas_count'));

        var costosMensuales = @json($costosMensuales);
        var mesesLabels = costosMensuales.map(function(x) { return x.mes; });
        var costosTotal = costosMensuales.map(function(x) { return x.costo_total; });
        var costosPreventivo = costosMensuales.map(function(x) { return x.preventivo; });
        var costosCorrectivo = costosMensuales.map(function(x) { return x.correctivo; });
        var costosInspeccion = costosMensuales.map(function(x) { return x.inspeccion; });

        var distribucionCount = [
            {{ $distribucionTipo['preventive']['count'] }},
            {{ $distribucionTipo['corrective']['count'] }},
            {{ $distribucionTipo['inspection']['count'] }}
        ];

        var comprasMensuales = @json($comprasMensuales ?? []);
        var costosConductorMensuales = @json($costosConductorMensuales ?? []);
        var conductoresTopIds = @json($conductoresTopIds ?? []);
        var conductoresTopNombres = @json($conductoresTopNombres ?? []);

        // Fallas por vehículo (bar horizontal)
        var ctxVehiculo = document.getElementById('chartFallasVehiculo');
        if (ctxVehiculo && fallasVehiculoLabels.length) {
            ctxVehiculo.chart = new Chart(ctxVehiculo, {
                type: 'bar',
                data: {
                    labels: fallasVehiculoLabels,
                    datasets: [{
                        label: 'Fallas',
                        data: fallasVehiculoData,
                        backgroundColor: 'rgba(249, 115, 22, 0.7)',
                        borderColor: 'rgb(249, 115, 22)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: colors.isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: colors.isDark ? '#fff' : '#111827',
                            bodyColor: colors.isDark ? '#e5e7eb' : '#374151'
                        }
                    },
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } }
                    }
                }
            });
        } else if (ctxVehiculo) {
            ctxVehiculo.parentElement.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-8">No hay fallas por vehículo en el período.</p>';
        }

        // Fallas por conductor (bar horizontal)
        var ctxConductor = document.getElementById('chartFallasConductor');
        if (ctxConductor && fallasConductorLabels.length) {
            ctxConductor.chart = new Chart(ctxConductor, {
                type: 'bar',
                data: {
                    labels: fallasConductorLabels,
                    datasets: [{
                        label: 'Fallas',
                        data: fallasConductorData,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: colors.isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: colors.isDark ? '#fff' : '#111827',
                            bodyColor: colors.isDark ? '#e5e7eb' : '#374151'
                        }
                    },
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } }
                    }
                }
            });
        } else if (ctxConductor) {
            ctxConductor.parentElement.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-8">No hay fallas asignadas a conductores en el período.</p>';
        }

        // Tendencia de costos (line)
        var ctxTendencia = document.getElementById('chartTendenciaCostos');
        if (ctxTendencia) {
            ctxTendencia.chart = new Chart(ctxTendencia, {
                type: 'line',
                data: {
                    labels: mesesLabels,
                    datasets: [
                        { label: 'Total', data: costosTotal, borderColor: '#4f46e5', backgroundColor: 'rgba(79, 70, 229, 0.1)', fill: true, tension: 0.4 },
                        { label: 'Preventivo', data: costosPreventivo, borderColor: '#3b82f6', backgroundColor: 'transparent', tension: 0.4 },
                        { label: 'Correctivo', data: costosCorrectivo, borderColor: '#f97316', backgroundColor: 'transparent', tension: 0.4 },
                        { label: 'Inspección', data: costosInspeccion, borderColor: '#a855f7', backgroundColor: 'transparent', tension: 0.4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: colors.textColor } },
                        tooltip: {
                            backgroundColor: colors.isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: colors.isDark ? '#fff' : '#111827',
                            bodyColor: colors.isDark ? '#e5e7eb' : '#374151',
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': $' + new Intl.NumberFormat('es-CL').format(ctx.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: { ticks: { color: colors.textColor, maxRotation: 45 }, grid: { color: colors.gridColor } },
                        y: {
                            ticks: { color: colors.textColor, callback: function(v) {
                                if (v >= 1e6) return '$' + (v/1e6).toFixed(1) + 'M';
                                if (v >= 1e3) return '$' + (v/1e3).toFixed(0) + 'K';
                                return '$' + v;
                            }},
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Distribución por tipo (doughnut)
        var ctxDist = document.getElementById('chartDistribucionTipo');
        if (ctxDist) {
            ctxDist.chart = new Chart(ctxDist, {
                type: 'doughnut',
                data: {
                    labels: ['Preventivo', 'Correctivo', 'Inspección'],
                    datasets: [{
                        data: distribucionCount,
                        backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(249, 115, 22, 0.8)', 'rgba(168, 85, 247, 0.8)'],
                        borderColor: ['rgb(59, 130, 246)', 'rgb(249, 115, 22)', 'rgb(168, 85, 247)'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: colors.textColor } },
                        tooltip: {
                            backgroundColor: colors.isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: colors.isDark ? '#fff' : '#111827',
                            bodyColor: colors.isDark ? '#e5e7eb' : '#374151'
                        }
                    }
                }
            });
        }

        // Compras por mes (bar)
        var ctxCompras = document.getElementById('chartComprasMensuales');
        if (ctxCompras && comprasMensuales.length) {
            ctxCompras.chart = new Chart(ctxCompras, {
                type: 'bar',
                data: {
                    labels: comprasMensuales.map(function(x) { return x.mes; }),
                    datasets: [{
                        label: 'Monto compras',
                        data: comprasMensuales.map(function(x) { return x.monto; }),
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: colors.isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: colors.isDark ? '#fff' : '#111827',
                            bodyColor: colors.isDark ? '#e5e7eb' : '#374151',
                            callbacks: { label: function(ctx) { return '$' + new Intl.NumberFormat('es-CL').format(ctx.parsed.y); } }
                        }
                    },
                    scales: {
                        x: { ticks: { color: colors.textColor, maxRotation: 45 }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } }
                    }
                }
            });
        } else if (ctxCompras) {
            ctxCompras.parentElement.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 py-8">No hay datos de compras.</p>';
        }

        // Tendencia costos por conductor (line, top 5)
        var ctxCostosConductor = document.getElementById('chartCostosConductor');
        if (ctxCostosConductor && costosConductorMensuales.length && conductoresTopIds.length) {
            var coloresConductor = ['#4f46e5', '#f97316', '#10b981', '#8b5cf6', '#ef4444'];
            var datasetsConductor = conductoresTopIds.map(function(id, idx) {
                return {
                    label: conductoresTopNombres[id] || 'Conductor ' + id,
                    data: costosConductorMensuales.map(function(r) { return r['driver_' + id] || 0; }),
                    borderColor: coloresConductor[idx % coloresConductor.length],
                    backgroundColor: 'transparent',
                    tension: 0.4
                };
            });
            ctxCostosConductor.chart = new Chart(ctxCostosConductor, {
                type: 'line',
                data: {
                    labels: costosConductorMensuales.map(function(x) { return x.mes; }),
                    datasets: datasetsConductor
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: colors.textColor } },
                        tooltip: {
                            backgroundColor: colors.isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                            titleColor: colors.isDark ? '#fff' : '#111827',
                            bodyColor: colors.isDark ? '#e5e7eb' : '#374151',
                            callbacks: { label: function(ctx) { return ctx.dataset.label + ': $' + new Intl.NumberFormat('es-CL').format(ctx.parsed.y); } }
                        }
                    },
                    scales: {
                        x: { ticks: { color: colors.textColor, maxRotation: 45 }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } }
                    }
                }
            });
        }

        // Observar modo oscuro
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                if (m.attributeName === 'class') {
                    var c = getChartColors();
                    [ctxVehiculo, ctxConductor, ctxTendencia, ctxDist, ctxCompras, ctxCostosConductor].forEach(function(ctx) {
                        if (ctx && ctx.chart) updateChartColors(ctx.chart, c);
                    });
                }
            });
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    });
})();
</script>
@endpush
@endsection
