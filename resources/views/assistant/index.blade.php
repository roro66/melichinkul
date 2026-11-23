@extends('layouts.app')

@section('title', 'Asistente')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Asistente</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pregunta por la flota o por cómo se usa la aplicación. Las cifras salen de consultas de solo lectura y los pasos, del manual.</p>
    </div>

    @livewire('assistant.data-assistant')
</div>
@endsection
