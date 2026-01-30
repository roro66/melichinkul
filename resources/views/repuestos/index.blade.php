@extends('layouts.app')

@section('title', 'Catálogo de Repuestos')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Catálogo de Repuestos</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Inventario de repuestos, consumibles e insumos</p>
        </div>
        <a href="{{ route('repuestos.create') }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nuevo repuesto
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="spare-parts-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Precio ref.</th>
                            <th>Vencimiento</th>
                            <th>Activo</th>
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
    initDataTable('spare-parts-table', {
        ajax: { url: "{{ route('repuestos.index') }}", type: 'GET' },
        columns: [
            { data: 'code', name: 'code' },
            { data: 'description', name: 'description' },
            { data: 'brand', name: 'brand', render: function(d) { return d || '—'; } },
            { data: 'category_label', name: 'category', orderable: true, searchable: false },
            { data: 'reference_price_formatted', name: 'reference_price', orderable: true, searchable: false },
            { data: 'has_expiration', name: 'has_expiration', render: function(d) { return d ? 'Sí' : 'No'; } },
            { data: 'active_badge', name: 'active', orderable: true, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'no-export' }
        ],
        order: [[1, 'asc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5, 6] },
            { className: "text-right", targets: [7] }
        ]
    });
});

window.deleteSparePart = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'Esta acción no se puede deshacer', 'Sí, eliminar')
        .then((result) => {
            if (result.isConfirmed) {
                fetch('/repuestos/' + id, {
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
                        $('#spare-parts-table').DataTable().ajax.reload();
                        swalSuccess(data.message || 'Repuesto eliminado correctamente.', 2000);
                    } else {
                        swalError(data.message || 'Error al eliminar.');
                    }
                })
                .catch(() => swalError('Error al eliminar el repuesto.'));
            }
        });
};
</script>
@endpush
@endsection
