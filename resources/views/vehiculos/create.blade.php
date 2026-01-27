@extends('layouts.app')

@section('title', 'Nuevo Vehículo')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nuevo Vehículo</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Registrar un nuevo vehículo en la flota</p>
    </div>

    @livewire('vehiculos.vehicle-form')
</div>
@endsection
