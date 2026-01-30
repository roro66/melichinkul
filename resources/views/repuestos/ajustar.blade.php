@extends('layouts.app')

@section('title', 'Ajustar stock - ' . $sparePart->code)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Ajustar stock</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $sparePart->code }} - {{ $sparePart->description }}</p>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Stock actual: <strong>{{ $sparePart->stock?->quantity ?? 0 }}</strong></p>
        </div>
        <a href="{{ route('repuestos.show', $sparePart->id) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Volver</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-lg">
        <form action="{{ route('repuestos.ajustar.store', $sparePart->id) }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de ajuste</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="type" value="adjustment_in" {{ old('type', 'adjustment_in') === 'adjustment_in' ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Entrada (sumar al stock)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="type" value="adjustment_out" {{ old('type') === 'adjustment_out' ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Salida (restar del stock)</span>
                    </label>
                </div>
                @error('type')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cantidad <span class="text-red-500">*</span></label>
                <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}" min="1" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('quantity') border-red-500 @enderror">
                @error('quantity')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                <textarea id="notes" name="notes" rows="3" maxlength="500" placeholder="Motivo del ajuste (opcional)"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('notes') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('repuestos.show', $sparePart->id) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 text-white rounded-lg">Registrar ajuste</button>
            </div>
        </form>
    </div>
</div>
@endsection
