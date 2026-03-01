@extends('layouts.app')

@section('title', 'Corregir lectura - ' . $reading->vehicle->license_plate)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Corregir lectura de kilometraje</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $reading->vehicle->license_plate }} — {{ $reading->vehicle->brand }} {{ $reading->vehicle->model }} · {{ $reading->recorded_at->format('d-m-Y') }}
            </p>
        </div>
        <a href="{{ route('kilometraje.readings', ['vehicle_id' => $reading->vehicle_id]) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i> Volver al listado
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-lg">
        <form action="{{ route('kilometraje.readings.update', $reading) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="mileage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kilometraje <span class="text-red-500">*</span></label>
                <input type="number" step="1" id="mileage" name="mileage" value="{{ old('mileage', $reading->mileage) }}" required
                       min="{{ $minMileage }}" {{ $maxMileage !== null ? 'max=' . $maxMileage : '' }}
                       class="w-full px-3 py-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('mileage')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    El valor debe estar entre
                    <strong>{{ number_format($minMileage, 0, ',', '.') }}</strong> km
                    @if($maxMileage !== null)
                    y <strong>{{ number_format($maxMileage, 0, ',', '.') }}</strong> km
                    (lecturas anterior y siguiente)
                    @else
                    (lectura anterior). No hay lectura posterior.
                    @endif
                </p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">
                    <i class="fas fa-save mr-2"></i> Guardar corrección
                </button>
                <a href="{{ route('kilometraje.readings', ['vehicle_id' => $reading->vehicle_id]) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
