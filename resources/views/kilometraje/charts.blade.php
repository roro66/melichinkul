@extends('layouts.app')

@section('title', 'Gráficos de Kilometraje')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Gráficos de Kilometraje</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Evolución y comparación entre vehículos</p>
        </div>
        <a href="{{ route('kilometraje.index') }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            <i class="fas fa-tachometer-alt mr-2"></i> Registro
        </a>
    </div>

    {{-- Gráfico por vehículo --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Kilometraje vs tiempo (por vehículo)</h2>
            <select id="vehicle-select" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Selecciona un vehículo</option>
                @foreach($vehicles as $v)
                <option value="{{ $v->id }}" {{ (int)($selectedVehicleId ?? 0) === $v->id ? 'selected' : '' }}>
                    {{ $v->license_plate }} - {{ $v->brand }} {{ $v->model }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="p-6">
            <div style="position: relative; height: 350px;">
                <canvas id="chartVehicle"></canvas>
            </div>
        </div>
    </div>

    {{-- Gráfico comparativo --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Comparación entre vehículos</h2>
            <div class="flex flex-wrap gap-2" id="vehicle-checkboxes">
                @foreach($vehicles as $v)
                <label class="inline-flex items-center">
                    <input type="checkbox" name="compare_vehicles[]" value="{{ $v->id }}"
                           class="compare-vehicle rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700"
                           {{ in_array($v->id, $selectedVehicleIds) ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $v->license_plate }}</span>
                </label>
                @endforeach
            </div>
            <button type="button" id="btn-update-compare" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg">
                Actualizar gráfico
            </button>
        </div>
        <div class="p-6">
            <div style="position: relative; height: 400px;">
                <canvas id="chartCompare"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const chartVehicleUrl = '{{ url("/kilometraje/grafico-vehiculo") }}';
    const chartCompareUrl = '{{ url("/kilometraje/grafico-comparar") }}';
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e7eb' : '#374151';

    function updateChartColors(chart) {
        if (!chart) return;
        if (chart.options.plugins?.legend?.labels) chart.options.plugins.legend.labels.color = textColor;
        if (chart.options.plugins?.tooltip) {
            chart.options.plugins.tooltip.backgroundColor = isDark ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255,255,255,0.95)';
            chart.options.plugins.tooltip.titleColor = isDark ? '#fff' : '#111827';
            chart.options.plugins.tooltip.bodyColor = isDark ? '#e5e7eb' : '#374151';
        }
        if (chart.options.scales) {
            Object.keys(chart.options.scales).forEach(function(k) {
                var s = chart.options.scales[k];
                if (s.ticks) s.ticks.color = textColor;
                if (s.grid) s.grid.color = isDark ? 'rgba(75,85,99,0.5)' : 'rgba(0,0,0,0.1)';
            });
        }
        chart.update('none');
    }

    let chartVehicleInstance = null;
    let chartCompareInstance = null;

    document.getElementById('vehicle-select').addEventListener('change', function() {
        const id = this.value;
        if (!id) {
            if (chartVehicleInstance) {
                chartVehicleInstance.destroy();
                chartVehicleInstance = null;
            }
            return;
        }
        fetch(chartVehicleUrl + '/' + id)
            .then(r => r.json())
            .then(function(data) {
                const ctx = document.getElementById('chartVehicle').getContext('2d');
                if (chartVehicleInstance) chartVehicleInstance.destroy();
                chartVehicleInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: data.vehicle,
                            data: data.data,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: textColor } } },
                        scales: {
                            x: { ticks: { color: textColor }, grid: { color: isDark ? 'rgba(75,85,99,0.5)' : 'rgba(0,0,0,0.1)' } },
                            y: { ticks: { color: textColor }, grid: { color: isDark ? 'rgba(75,85,99,0.5)' : 'rgba(0,0,0,0.1)' }, beginAtZero: false }
                        }
                    }
                });
            });
    });

    if (document.getElementById('vehicle-select').value) {
        document.getElementById('vehicle-select').dispatchEvent(new Event('change'));
    }

    function loadCompareChart() {
        const ids = Array.from(document.querySelectorAll('.compare-vehicle:checked')).map(cb => cb.value);
        if (ids.length === 0) {
            if (chartCompareInstance) {
                chartCompareInstance.destroy();
                chartCompareInstance = null;
            }
            return;
        }
        const params = new URLSearchParams();
        ids.forEach(id => params.append('vehicle_ids[]', id));
        fetch(chartCompareUrl + '?' + params.toString())
            .then(r => r.json())
            .then(function(data) {
                const ctx = document.getElementById('chartCompare').getContext('2d');
                if (chartCompareInstance) chartCompareInstance.destroy();
                chartCompareInstance = new Chart(ctx, {
                    type: 'line',
                    data: { labels: data.labels, datasets: data.datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: textColor } } },
                        scales: {
                            x: { ticks: { color: textColor }, grid: { color: isDark ? 'rgba(75,85,99,0.5)' : 'rgba(0,0,0,0.1)' } },
                            y: { ticks: { color: textColor }, grid: { color: isDark ? 'rgba(75,85,99,0.5)' : 'rgba(0,0,0,0.1)' }, beginAtZero: false }
                        }
                    }
                });
            });
    }

    document.getElementById('btn-update-compare').addEventListener('click', loadCompareChart);

    if (document.querySelectorAll('.compare-vehicle:checked').length > 0) {
        loadCompareChart();
    }
})();
</script>
@endpush
@endsection
