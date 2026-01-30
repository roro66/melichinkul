@extends('layouts.app')

@section('title', 'Compras')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Compras</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Registro de compras de repuestos y recepción de stock</p>
        </div>
        <a href="{{ route('compras.create') }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nueva compra
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="purchases-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Proveedor</th>
                            <th>Fecha</th>
                            <th>Referencia</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initDataTable('purchases-table', {
        ajax: { url: "{{ route('compras.index') }}", type: 'GET' },
        columns: [
            { data: 'id', name: 'id', width: '60px' },
            { data: 'supplier_name', name: 'supplier.name' },
            { data: 'purchase_date_formatted', name: 'purchase_date' },
            { data: 'reference', name: 'reference', render: function(d) { return d || '—'; } },
            { data: 'status_badge', name: 'status', orderable: true, searchable: false },
            { data: 'total_formatted', name: 'total', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'no-export' }
        ],
        order: [[2, 'desc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5] },
            { className: "text-right", targets: [6] }
        ]
    });
});

window.deletePurchase = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'Esta acción no se puede deshacer', 'Sí, eliminar')
        .then((result) => {
            if (result.isConfirmed) {
                fetch('/compras/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        $('#purchases-table').DataTable().ajax.reload();
                        swalSuccess(data.message || 'Compra eliminada correctamente.', 2000);
                    } else {
                        swalError(data.message || 'Error al eliminar.');
                    }
                })
                .catch(() => swalError('Error al eliminar la compra.'));
            }
        });
};
</script>
@endpush
@endsection
