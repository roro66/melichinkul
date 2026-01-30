@extends('layouts.app')

@section('title', 'Compra #' . $purchase->id)

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Compra #{{ $purchase->id }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $purchase->supplier?->name }} · {{ $purchase->purchase_date?->format('d/m/Y') }}
                @if($purchase->reference)
                    · Ref: {{ $purchase->reference }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($purchase->isDraft())
                <a href="{{ route('compras.edit', $purchase->id) }}" class="px-4 py-2 border border-amber-500 dark:border-amber-400 text-amber-700 dark:text-amber-300 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/30">Editar</a>
                <form action="{{ route('compras.receive', $purchase->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Recibir esta compra y actualizar el stock?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white rounded-lg">Recibir compra</button>
                </form>
            @endif
            <a href="{{ route('compras.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-red-800 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Detalle</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <dt class="text-gray-500 dark:text-gray-400">Proveedor</dt>
                <dd class="text-gray-900 dark:text-white font-medium">{{ $purchase->supplier?->name ?? '—' }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Fecha</dt>
                <dd class="text-gray-900 dark:text-white">{{ $purchase->purchase_date?->format('d/m/Y') ?? '—' }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Referencia</dt>
                <dd class="text-gray-900 dark:text-white">{{ $purchase->reference ?? '—' }}</dd>
                <dt class="text-gray-500 dark:text-gray-400">Estado</dt>
                <dd>
                    @php $statuses = \App\Models\Purchase::STATUSES; $status = $statuses[$purchase->status] ?? $purchase->status; @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($purchase->status === 'draft') bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                        @elseif($purchase->status === 'received') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                        @else bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                        @endif">{{ $status }}</span>
                </dd>
                @if($purchase->user)
                    <dt class="text-gray-500 dark:text-gray-400">Registrado por</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $purchase->user->name }}</dd>
                @endif
            </dl>
            @if($purchase->notes)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <dt class="text-gray-500 dark:text-gray-400 text-sm mb-1">Notas</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $purchase->notes }}</dd>
                </div>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Total</h2>
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">${{ number_format($purchase->totalAmount(), 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white px-6 py-4 border-b border-gray-200 dark:border-gray-700">Ítems</h2>
        @if($purchase->items->isEmpty())
            <p class="px-6 py-8 text-gray-500 dark:text-gray-400 text-center">No hay ítems en esta compra.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Repuesto</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">P. unitario</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Subtotal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Vencimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($purchase->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ $item->sparePart?->code ?? '—' }} - {{ $item->sparePart?->description ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white text-right font-medium">${{ number_format($item->subtotal(), 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item->expiry_date?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
