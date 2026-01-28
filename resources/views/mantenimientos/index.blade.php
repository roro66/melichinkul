@extends('layouts.app')

@section('title', 'Mantenimientos')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mantenimientos</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de mantenimientos de vehículos</p>
        </div>
        <a href="{{ route('mantenimientos.create') }}" 
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nuevo Mantenimiento
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="maintenances-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha Programada</th>
                            <th>Vehículo</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Descripción</th>
                            <th>Costo Total</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables llenará esto automáticamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = initDataTable('maintenances-table', {
        ajax: {
            url: "{{ route('mantenimientos.index') }}",
            type: 'GET'
        },
        columns: [
            { 
                data: 'formatted_date', 
                name: 'scheduled_date',
                render: function(data, type, row) {
                    return data || 'Sin fecha';
                }
            },
            { 
                data: 'vehicle_info', 
                name: 'vehicle.license_plate',
                orderable: false
            },
            { 
                data: 'type_badge', 
                name: 'type',
                orderable: true,
                searchable: false
            },
            { 
                data: 'status_badge', 
                name: 'status',
                orderable: true,
                searchable: false
            },
            { 
                data: 'work_description', 
                name: 'work_description',
                render: function(data) {
                    if (!data) return '-';
                    return data.length > 50 ? data.substring(0, 50) + '...' : data;
                }
            },
            { 
                data: 'formatted_cost', 
                name: 'total_cost',
                render: function(data) {
                    return data || '$0';
                }
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false, 
                className: 'no-export' 
            }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5] },
            { className: "text-right", targets: [6] }
        ]
    });
});

// Función para eliminar mantenimiento (disponible globalmente)
window.deleteMaintenance = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'Esta acción no se puede deshacer', 'Sí, eliminar')
        .then((result) => {
            if (result.isConfirmed) {
                fetch('/mantenimientos/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#maintenances-table').DataTable().ajax.reload();
                        swalSuccess(data.message || 'Mantenimiento eliminado correctamente.', 2000);
                    } else {
                        swalError(data.message || 'Error al eliminar el mantenimiento.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    swalError('Error al eliminar el mantenimiento.');
                });
            }
        });
}
</script>
@endpush
@endsection
