@extends('layouts.app')

@section('title', 'Conductores')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Conductores</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de conductores y licencias</p>
        </div>
        <a href="{{ route('conductores.create') }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nuevo conductor
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="drivers-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>RUT</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado licencia</th>
                            <th>Vencimiento licencia</th>
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
    const table = initDataTable('drivers-table', {
        ajax: {
            url: "{{ route('conductores.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'rut', name: 'rut' },
            { data: 'full_name', name: 'full_name' },
            { data: 'phone', name: 'phone', render: function(d) { return d || '—'; } },
            { data: 'email', name: 'email', render: function(d) { return d || '—'; } },
            { data: 'license_status_badge', name: 'license_expiration_date', orderable: false, searchable: false },
            { data: 'license_expiration_formatted', name: 'license_expiration_date' },
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

window.deleteDriver = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'Esta acción no se puede deshacer', 'Sí, eliminar')
        .then((result) => {
            if (result.isConfirmed) {
                fetch('/conductores/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#drivers-table').DataTable().ajax.reload();
                        swalSuccess(data.message || 'Conductor eliminado correctamente.', 2000);
                    } else {
                        swalError(data.message || 'Error al eliminar el conductor.');
                    }
                })
                .catch(() => swalError('Error al eliminar el conductor.'));
            }
        });
};
</script>
@endpush
@endsection
