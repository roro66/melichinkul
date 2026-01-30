@extends('layouts.app')

@section('title', $sparePart->code . ' - ' . $sparePart->description)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $sparePart->code }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $sparePart->description }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('repuestos.ajustar', $sparePart->id) }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 text-white rounded-lg">Ajustar stock</a>
            <a href="{{ route('repuestos.edit', $sparePart->id) }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Editar</a>
            <a href="{{ route('repuestos.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Datos del repuesto</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <dt class="text-gray-500 dark:text-gray-400">Código</dt>
                <dd class="text-gray-900 dark:text-white font-medium">{{ $sparePart->code }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Descripción</dt>
                <dd class="text-gray-900 dark:text-white">{{ $sparePart->description }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Marca</dt>
                <dd class="text-gray-900 dark:text-white">{{ $sparePart->brand ?? '—' }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Categoría</dt>
                <dd class="text-gray-900 dark:text-white">{{ SparePart::CATEGORIES[$sparePart->category] ?? $sparePart->category }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Precio referencia</dt>
                <dd class="text-gray-900 dark:text-white">{{ $sparePart->reference_price !== null ? '$' . number_format($sparePart->reference_price, 0, ',', '.') : '—' }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Con vencimiento</dt>
                <dd class="text-gray-900 dark:text-white">{{ $sparePart->has_expiration ? 'Sí' : 'No' }}</dd>
            </dl>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Stock actual</h2>
            @php $stock = $sparePart->stock; @endphp
            @if($stock)
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stock->quantity }}</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Mínimo: {{ $stock->min_stock !== null ? $stock->min_stock : '—' }}
                    @if($stock->isBelowMinimum())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 ml-1">Bajo mínimo</span>
                    @endif
                </p>
                @if($stock->location)
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ubicación: {{ $stock->location }}</p>
                @endif
            @else
                <p class="text-2xl font-bold text-gray-500 dark:text-gray-400">0</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Sin registro de stock</p>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white px-6 py-4 border-b border-gray-200 dark:border-gray-700">Últimos movimientos</h2>
        @if($sparePart->inventoryMovements->isEmpty())
            <p class="px-6 py-8 text-gray-500 dark:text-gray-400 text-center">No hay movimientos registrados.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($sparePart->inventoryMovements as $mov)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $mov->movement_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ \App\Models\InventoryMovement::TYPES[$mov->type] ?? $mov->type }}</td>
                                <td class="px-4 py-3 text-sm text-right {{ $mov->quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $mov->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $mov->notes }}">{{ $mov->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('inventario.movimientos.index') }}?spare_part_id={{ $sparePart->id }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ver todos los movimientos</a>
        </div>
    </div>
</div>
@endsection
