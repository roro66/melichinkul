@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Proveedores</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de proveedores de repuestos</p>
        </div>
        <a href="{{ route('proveedores.create') }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nuevo proveedor
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="suppliers-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>RUT</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
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
    initDataTable('suppliers-table', {
        ajax: { url: "{{ route('proveedores.index') }}", type: 'GET' },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'rut', name: 'rut', render: function(d) { return d || '—'; } },
            { data: 'contact_name', name: 'contact_name', render: function(d) { return d || '—'; } },
            { data: 'phone', name: 'phone', render: function(d) { return d || '—'; } },
            { data: 'email', name: 'email', render: function(d) { return d || '—'; } },
            { data: 'active_badge', name: 'active', orderable: true, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'no-export' }
        ],
        order: [[0, 'asc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5] },
            { className: "text-right", targets: [6] }
        ]
    });
});

window.deleteSupplier = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'Esta acción no se puede deshacer', 'Sí, eliminar')
        .then((result) => {
            if (result.isConfirmed) {
                fetch('/proveedores/' + id, {
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
                        $('#suppliers-table').DataTable().ajax.reload();
                        swalSuccess(data.message || 'Proveedor eliminado correctamente.', 2000);
                    } else {
                        swalError(data.message || 'Error al eliminar.');
                    }
                })
                .catch(() => swalError('Error al eliminar el proveedor.'));
            }
        });
};
</script>
@endpush
@endsection
