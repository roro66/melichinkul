@extends('layouts.app')

@section('title', 'Editar proveedor')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar proveedor</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $supplier->name }}</p>
        </div>
        <a href="{{ route('proveedores.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Volver</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('proveedores.update', $supplier->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $supplier->name) }}" required maxlength="255"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="rut" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RUT</label>
                    <input type="text" id="rut" name="rut" value="{{ old('rut', $supplier->rut) }}" maxlength="32"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label for="contact_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre de contacto</label>
                    <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}" maxlength="64"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $supplier->email) }}" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                <textarea id="address" name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('address', $supplier->address) }}</textarea>
            </div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="active" value="1" {{ old('active', $supplier->active) ? 'checked' : '' }}
                    class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
            </label>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('proveedores.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
