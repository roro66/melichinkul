@extends('layouts.app')

@section('title', 'Editar repuesto')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar repuesto</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $sparePart->code }} — {{ $sparePart->description }}</p>
        </div>
        <a href="{{ route('repuestos.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Volver</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('repuestos.update', $sparePart->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Código <span class="text-red-500">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code', $sparePart->code) }}" required maxlength="64"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('code') border-red-500 @enderror">
                    @error('code')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría <span class="text-red-500">*</span></label>
                    <select id="category" name="category" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category', $sparePart->category) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción <span class="text-red-500">*</span></label>
                <input type="text" id="description" name="description" value="{{ old('description', $sparePart->description) }}" required maxlength="255"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('description') border-red-500 @enderror">
                @error('description')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marca</label>
                    <input type="text" id="brand" name="brand" value="{{ old('brand', $sparePart->brand) }}" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <div>
                    <label for="reference_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio de referencia (CLP)</label>
                    <input type="number" id="reference_price" name="reference_price" value="{{ old('reference_price', $sparePart->reference_price) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="has_expiration" value="1" {{ old('has_expiration', $sparePart->has_expiration) ? 'checked' : '' }}
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Tiene vencimiento</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="active" value="1" {{ old('active', $sparePart->active) ? 'checked' : '' }}
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Activo</span>
                </label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('repuestos.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
