@extends('layouts.app')

@section('title', 'Editar certificación - ' . $vehicle->license_plate)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar certificación</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $vehicle->license_plate }} — {{ $certification->name }}</p>
        </div>
        <a href="{{ route('vehiculos.show', $vehicle->id) }}#certificaciones" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            Volver al vehículo
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('certificaciones.update', $certification->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de documento <span class="text-red-500">*</span></label>
                    <select id="type" name="type" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        @foreach($certTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $certification->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $certification->name) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="certificate_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nº certificado / Póliza</label>
                    <input type="text" id="certificate_number" name="certificate_number" value="{{ old('certificate_number', $certification->certificate_number) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Proveedor / Emisor</label>
                    <input type="text" id="provider" name="provider" value="{{ old('provider', $certification->provider) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="issue_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha emisión</label>
                    <input type="date" id="issue_date" name="issue_date" value="{{ old('issue_date', $certification->issue_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="expiration_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha vencimiento <span class="text-red-500">*</span></label>
                    <input type="date" id="expiration_date" name="expiration_date" value="{{ old('expiration_date', $certification->expiration_date?->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    @error('expiration_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Costo (CLP)</label>
                    <input type="number" id="cost" name="cost" value="{{ old('cost', $certification->cost) }}" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-center gap-6">
                    <label class="inline-flex items-center">
                        <input type="hidden" name="required" value="0">
                        <input type="checkbox" name="required" value="1" {{ old('required', $certification->required) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Obligatorio</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="hidden" name="active" value="0">
                        <input type="checkbox" name="active" value="1" {{ old('active', $certification->active) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label for="observations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                    <textarea id="observations" name="observations" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('observations', $certification->observations) }}</textarea>
                </div>
                <div>
                    <label for="attached_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Archivo escaneado (PDF, JPG, PNG)</label>
                    @if($certification->attached_file)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Actual: <a href="{{ route('certificaciones.view', [$certification->id, 1]) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver archivo</a></p>
                    @endif
                    <input type="file" id="attached_file" name="attached_file" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dejar vacío para mantener el actual</p>
                </div>
                <div>
                    <label for="attached_file_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Segundo archivo (reverso)</label>
                    @if($certification->attached_file_2)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Actual: <a href="{{ route('certificaciones.view', [$certification->id, 2]) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver archivo</a></p>
                    @endif
                    <input type="file" id="attached_file_2" name="attached_file_2" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('vehiculos.show', $vehicle->id) }}#certificaciones" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">Actualizar certificación</button>
            </div>
        </form>
    </div>
</div>
@endsection
