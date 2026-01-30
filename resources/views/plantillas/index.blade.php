@extends('layouts.app')

@section('title', 'Plantillas de Mantenimiento')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Plantillas de Mantenimiento</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Plantillas para pre-llenar mantenimientos con tipo y repuestos</p>
        </div>
        @can('maintenances.create')
        <a href="{{ route('plantillas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg">
            <i class="fas fa-plus mr-2"></i> Nueva plantilla
        </a>
        @endcan
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="plantillas-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th class="text-center">Repuestos</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables llenará esto vía AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initDataTable('plantillas-table', {
        ajax: {
            url: "{{ route('plantillas.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'type_badge', name: 'type', orderable: true, searchable: false },
            { data: 'description_short', name: 'description' },
            { data: 'spare_parts_count', name: 'spare_parts_count', className: 'text-center', orderable: true, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'no-export text-right' }
        ],
        order: [[0, 'asc']],
        columnDefs: [
            { className: 'text-left', targets: [0, 1, 2] },
            { className: 'text-center', targets: [3] },
            { className: 'text-right', targets: [4] }
        ]
    });
});

@can('maintenances.create')
window.deletePlantilla = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'Se eliminará la plantilla y sus repuestos asociados.', 'Sí, eliminar')
        .then(function(result) {
            if (result.isConfirmed) {
                fetch('/plantillas/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) { return response.json().then(function(data) { return { ok: response.ok, data: data }; }); })
                .then(function(result) {
                    if (result.ok && result.data !== undefined) {
                        $('#plantillas-table').DataTable().ajax.reload();
                        swalSuccess(result.data.message || 'Plantilla eliminada.', 2000);
                    } else {
                        swalError(result.data && result.data.message ? result.data.message : 'Error al eliminar.');
                    }
                })
                .catch(function() {
                    swalError('Error al eliminar la plantilla.');
                });
            }
        });
};
@endcan
</script>
@endpush
@endsection
