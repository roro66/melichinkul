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
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Mantenimiento
        </a>
    </div>

    @livewire('mantenimientos.maintenance-table')
</div>
@endsection
