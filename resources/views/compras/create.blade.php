@extends('layouts.app')

@section('title', 'Nueva compra')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nueva compra</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Registrar compra de repuestos a proveedor</p>
        </div>
        <a href="{{ route('compras.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Volver</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <form action="{{ route('compras.store') }}" method="POST" id="purchase-form" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Proveedor <span class="text-red-500">*</span></label>
                    <select id="supplier_id" name="supplier_id" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('supplier_id') border-red-500 @enderror">
                        <option value="">Seleccione proveedor</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha de compra <span class="text-red-500">*</span></label>
                    <input type="date" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white @error('purchase_date') border-red-500 @enderror">
                    @error('purchase_date')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Referencia / Nº factura</label>
                <input type="text" id="reference" name="reference" value="{{ old('reference') }}" maxlength="64"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                <textarea id="notes" name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('notes') }}</textarea>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Detalle de ítems</label>
                    <button type="button" id="add-item-row" class="px-3 py-1 text-sm bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">Añadir ítem</button>
                </div>
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Repuesto</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase w-24">Cantidad</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase w-32">P. unitario</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase w-36">Vencimiento</th>
                                <th class="px-3 py-2 w-12"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            <!-- Rows added by JS or one empty row -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('compras.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">Crear compra (borrador)</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('items-tbody');
    const addBtn = document.getElementById('add-item-row');
    const spareParts = @json($spareParts->map(fn($p) => ['id' => $p->id, 'code' => $p->code, 'description' => $p->description]));

    function addRow(index, data = {}) {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = index;
        const options = spareParts.map(p => `<option value="${p.id}" ${(data.spare_part_id == p.id) ? 'selected' : ''}>${p.code} - ${p.description}</option>`).join('');
        tr.innerHTML = `
            <td class="px-3 py-2">
                <select name="items[${index}][spare_part_id]" class="item-spare-part w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
                    <option value="">Seleccione repuesto</option>
                    ${options}
                </select>
            </td>
            <td class="px-3 py-2">
                <input type="number" name="items[${index}][quantity]" min="1" value="${data.quantity || ''}" placeholder="0"
                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="items[${index}][unit_price]" min="0" value="${data.unit_price || ''}" placeholder="0"
                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm text-right">
            </td>
            <td class="px-3 py-2">
                <input type="date" name="items[${index}][expiry_date]" value="${data.expiry_date || ''}"
                    class="w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
            </td>
            <td class="px-3 py-2">
                <button type="button" class="remove-row text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 p-1" title="Quitar"><i class="fas fa-times"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); reindex(); });
    }

    function reindex() {
        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach((tr, i) => {
            tr.dataset.index = i;
            tr.querySelectorAll('[name^="items["]').forEach(inp => {
                const name = inp.getAttribute('name');
                const key = name.replace(/items\[\d+\]/, 'items[' + i + ']');
                inp.setAttribute('name', key);
            });
        });
    }

    addBtn.addEventListener('click', () => {
        const n = tbody.querySelectorAll('.item-row').length;
        addRow(n);
    });

    addRow(0);
});
</script>
@endpush
@endsection
