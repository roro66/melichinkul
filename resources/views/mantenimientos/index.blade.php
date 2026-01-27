@extends('layouts.app')

@section('title', 'Mantenimientos')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mantenimientos</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de mantenimientos de vehículos</p>
        </div>
        <a href="{{ route('mantenimientos.create') }}"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-colors duration-150">
            Nuevo Mantenimiento
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <p class="text-gray-500 dark:text-gray-400">Módulo de mantenimientos - Próximamente con Livewire</p>
    </div>
</div>
@endsection
