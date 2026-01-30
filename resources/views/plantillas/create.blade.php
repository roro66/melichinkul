@extends('layouts.app')

@section('title', 'Nueva Plantilla de Mantenimiento')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nueva Plantilla</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Crear plantilla para pre-llenar mantenimientos</p>
        </div>
        <a href="{{ route('plantillas.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Volver</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('plantillas.store') }}" method="POST" class="space-y-6" id="form-plantilla">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de mantenimiento</label>
                    <select id="type" name="type" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Sin definir</option>
                        <option value="preventive" {{ old('type') === 'preventive' ? 'selected' : '' }}>{{ __('mantenimiento.types.preventive', [], 'es') }}</option>
                        <option value="corrective" {{ old('type') === 'corrective' ? 'selected' : '' }}>{{ __('mantenimiento.types.corrective', [], 'es') }}</option>
                        <option value="inspection" {{ old('type') === 'inspection' ? 'selected' : '' }}>{{ __('mantenimiento.types.inspection', [], 'es') }}</option>
                    </select>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                    <textarea id="description" name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Repuestos</h2>
                    <button type="button" id="btn-add-spare" class="text-sm px-3 py-1.5 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 rounded-lg">
                        <i class="fas fa-plus mr-1"></i> Agregar repuesto
                    </button>
                </div>
                <div id="spare-parts-container" class="space-y-3">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('plantillas.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">Crear plantilla</button>
            </div>
        </form>
    </div>
</div>

@php
    $sparePartsJson = $spareParts->map(function ($s) {
        return ['id' => $s->id, 'code' => $s->code, 'description' => $s->description];
    })->values()->all();
@endphp
@push('scripts')
<script>
(function() {
    const container = document.getElementById('spare-parts-container');
    const btnAdd = document.getElementById('btn-add-spare');
    const spareParts = @json($sparePartsJson);
    let rowIndex = 0;

    function addRow(sparePartId = '', quantity = 1) {
        const row = document.createElement('div');
        row.className = 'flex flex-wrap items-center gap-3 spare-row';
        row.dataset.index = rowIndex;
        row.innerHTML = `
            <select name="spare_parts[${rowIndex}][spare_part_id]" class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">Seleccionar repuesto</option>
                ${spareParts.map(function(s) { return '<option value="' + s.id + '" ' + (s.id == sparePartId ? 'selected' : '') + '>' + s.code + ' - ' + s.description + '</option>'; }).join('')}
            </select>
            <input type="number" name="spare_parts[${rowIndex}][quantity]" value="${quantity}" min="1" class="w-20 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <button type="button" class="remove-row text-red-600 dark:text-red-400 hover:text-red-800 p-2" title="Quitar"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(row);
        row.querySelector('.remove-row').addEventListener('click', function() { row.remove(); reindex(); });
        rowIndex++;
    }

    function reindex() {
        container.querySelectorAll('.spare-row').forEach(function(r, i) {
            r.dataset.index = i;
            r.querySelector('select').name = 'spare_parts[' + i + '][spare_part_id]';
            r.querySelector('input[type="number"]').name = 'spare_parts[' + i + '][quantity]';
        });
        rowIndex = container.children.length;
    }

    btnAdd.addEventListener('click', function() { addRow(); });
    addRow();
})();
</script>
@endpush
@endsection
