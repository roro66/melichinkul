@extends('layouts.app')

@section('title', 'Registro de Kilometraje')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Registro de Kilometraje</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ingreso rápido semanal de lecturas del tacómetro</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('kilometraje.charts') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <i class="fas fa-chart-line mr-2"></i> Gráficos
            </a>
            @can('mileage.import')
            <a href="{{ route('kilometraje.import') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <i class="fas fa-file-import mr-2"></i> Importar
            </a>
            @endcan
        </div>
    </div>

    <form action="{{ route('kilometraje.store') }}" method="POST" id="mileage-form">
        @csrf
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-end gap-4">
                <div>
                    <label for="fecha" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de la lectura</label>
                    <input type="date" name="fecha" id="fecha" value="{{ old('fecha', $fecha->format('Y-m-d')) }}"
                           class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">
                        <i class="fas fa-save mr-2"></i> Guardar lecturas
                    </button>
                    <a href="{{ route('kilometraje.index') }}?fecha={{ $fecha->format('Y-m-d') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Recargar
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Patente</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Marca / Modelo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">KM actual</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase w-40">Nuevo KM</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $errorVehicleIds = session('mileage_validation_errors')
                                ? array_column(session('mileage_validation_errors'), 'vehicle_id')
                                : [];
                        @endphp
                        @foreach($vehicles as $v)
                        @php $hasError = in_array($v->id, $errorVehicleIds); @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $hasError ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $v->license_plate }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $v->brand }} {{ $v->model }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $v->current_mileage ? number_format($v->current_mileage, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-2">
                                <input type="hidden" name="readings[{{ $loop->index }}][vehicle_id]" value="{{ $v->id }}">
                                <input type="number" step="1" min="0"
                                       name="readings[{{ $loop->index }}][mileage]" value="{{ old("readings.{$loop->index}.mileage", '') }}"
                                       placeholder="Ej. 125000"
                                       class="mileage-input w-full rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm {{ $hasError ? 'border-2 border-red-500 dark:border-red-500 bg-red-50 dark:bg-red-900/30 dark:text-white' : 'border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white' }}"
                                       data-last="{{ $v->current_mileage ?? 0 }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <p class="text-sm text-gray-500 dark:text-gray-400">
        <i class="fas fa-info-circle mr-1"></i> Deja vacío el campo "Nuevo KM" si no tienes la lectura. Usa Tab o Enter para pasar al siguiente vehículo.
    </p>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('mileage_validation_errors'))
    (function() {
        const errors = @json(session('mileage_validation_errors'));
        const listHtml = errors.map(e =>
            `<li class="py-1"><strong>${e.license_plate}</strong> (${e.model}): ingresó ${e.entered.toLocaleString('es-CL')} km, pero el km actual es ${e.current.toLocaleString('es-CL')}</li>`
        ).join('');
        Swal.fire({
            title: 'Error de validación',
            html: `<p class="text-left mb-3">El kilometraje no puede ser menor al actual. <strong>No se guardó nada.</strong></p>
                   <p class="text-left text-sm mb-2">Corrija los siguientes vehículos:</p>
                   <ul class="text-left list-disc pl-6 space-y-0.5 mb-4">${listHtml}</ul>
                   <p class="text-left text-sm text-gray-600 dark:text-gray-400">Los campos con error están marcados en rojo en la tabla.</p>`,
            icon: 'error',
            confirmButtonColor: '#dc2626',
            customClass: { popup: 'swal-popup', title: 'swal-title', content: 'swal-content', confirmButton: 'swal-confirm' }
        });
    })();
    @endif
    @if(session('success'))
    if (typeof swalSuccess === 'function') {
        swalSuccess('{{ addslashes(session('success')) }}', 2500);
    }
    @endif
    const inputs = document.querySelectorAll('.mileage-input');
    inputs.forEach((inp, i) => {
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const next = inputs[i + 1];
                if (next) next.focus();
            }
        });
    });
});
</script>
@endpush
@endsection
