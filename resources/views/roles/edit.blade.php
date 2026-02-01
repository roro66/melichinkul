@extends('layouts.app')

@section('title', 'Editar rol: ' . $role->name)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar rol: {{ ucfirst($role->name) }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Marcar los permisos que tendrá este rol</p>
        </div>
        <a href="{{ route('roles.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            Volver
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                @foreach($grouped as $module => $data)
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ $data['label'] }}</h3>
                    <div class="flex flex-wrap gap-4">
                        @foreach($data['items'] as $item)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="permissions[]" value="{{ $item['name'] }}"
                                {{ in_array($item['name'], $rolePermissionNames, true) ? 'checked' : '' }}
                                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $item['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
                    Guardar permisos
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
