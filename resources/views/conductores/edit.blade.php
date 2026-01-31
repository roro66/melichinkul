@extends('layouts.app')

@section('title', 'Editar conductor')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar conductor</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Modificar información del conductor</p>
    </div>

    @livewire('conductores.driver-form', ['id' => $id])

    <div class="mt-8">
        @livewire('conductores.driver-documents', ['driverId' => $id])
    </div>
</div>
@endsection
