@extends('layouts.app')

@section('title', 'Editar Mantenimiento')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Mantenimiento</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Modificar información del mantenimiento</p>
    </div>

    @livewire('mantenimientos.maintenance-form', ['id' => $id])
</div>
@endsection
