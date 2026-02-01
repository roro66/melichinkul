@extends('layouts.app')

@section('title', 'Roles y permisos')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Roles y permisos</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Asignar permisos a cada rol del sistema</p>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Permisos</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuarios</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($roles as $role)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($role->name) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">
                            {{ $role->permissions_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-700 dark:text-gray-300">
                            {{ $userCounts[$role->id] ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('roles.edit', $role) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                <i class="fas fa-edit"></i> Editar permisos
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
