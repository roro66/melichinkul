@extends('layouts.app')

@section('title', 'Detalle conductor')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $driver->full_name }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">RUT: {{ $driver->rut }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('conductores.edit', $driver->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
                Editar
            </a>
            <a href="{{ route('conductores.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Volver
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono</p>
                    <p class="text-gray-900 dark:text-white">{{ $driver->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                    <p class="text-gray-900 dark:text-white">{{ $driver->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado licencia</p>
                    @if (!$driver->license_expiration_date)
                        <span class="text-gray-500 dark:text-gray-400">Sin fecha</span>
                    @elseif($driver->hasExpiredLicense())
                        <span class="text-red-600 dark:text-red-400 font-medium">Vencida ({{ $driver->license_expiration_date->format('d/m/Y') }})</span>
                    @elseif($driver->licenseExpiringSoon(30))
                        <span class="text-amber-600 dark:text-amber-400 font-medium">Por vencer ({{ $driver->license_expiration_date->format('d/m/Y') }})</span>
                    @else
                        <span class="text-green-600 dark:text-green-400">Vigente hasta {{ $driver->license_expiration_date->format('d/m/Y') }}</span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nº y clase licencia</p>
                    <p class="text-gray-900 dark:text-white">{{ $driver->license_number ?? '—' }} {{ $driver->license_class ? '(' . $driver->license_class . ')' : '' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</p>
                    <p class="text-gray-900 dark:text-white">{{ $driver->active ? 'Activo' : 'Inactivo' }}</p>
                </div>
            </div>

            @if($driver->observations)
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Observaciones</p>
                    <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ $driver->observations }}</p>
                </div>
            @endif

            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Vehículos asignados</h2>
                @if($driver->assignedVehicles->isEmpty())
                    <p class="text-gray-500 dark:text-gray-400">Ningún vehículo asignado actualmente.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($driver->assignedVehicles as $vehicle)
                            <li>
                                <a href="{{ route('vehiculos.show', $vehicle->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $vehicle->license_plate }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
