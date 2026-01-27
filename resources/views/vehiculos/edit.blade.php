@extends('layouts.app')

@section('title', 'Editar Vehículo')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Vehículo</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Modificar información del vehículo</p>
    </div>

    @livewire('vehiculos.vehicle-form', ['id' => $id])
</div>
@endsection
