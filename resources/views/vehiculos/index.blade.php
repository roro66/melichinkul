@extends('layouts.app')

@section('title', 'Vehículos')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Vehículos</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de la flota de vehículos</p>
        </div>
        <a href="{{ route('vehiculos.create') }}" 
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nuevo Vehículo
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="vehicles-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Patente</th>
                            <th>Marca / Modelo</th>
                            <th>Año</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Conductor</th>
                            <th>Kilometraje</th>
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
    const table = initDataTable('vehicles-table', {
        ajax: {
            url: "{{ route('vehiculos.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'license_plate', name: 'license_plate' },
            { 
                data: 'brand', 
                name: 'brand',
                render: function(data, type, row) {
                    return '<div class="font-medium">' + data + '</div>' +
                           '<div class="text-sm text-gray-500 dark:text-gray-400">' + row.model + '</div>';
                }
            },
            { data: 'year', name: 'year' },
            { data: 'category_name', name: 'category.name' },
            { data: 'status_badge', name: 'status', orderable: true, searchable: false },
            { data: 'driver_name', name: 'currentDriver.full_name' },
            { 
                data: 'current_mileage', 
                name: 'current_mileage',
                render: function(data) {
                    return data ? new Intl.NumberFormat('es-CL').format(data) + ' km' : '-';
                }
            },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'no-export' }
        ],
        order: [[0, 'asc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5, 6] },
            { className: "text-right", targets: [7] }
        ]
    });
});

// Función para eliminar vehículo (disponible globalmente)
window.deleteVehicle = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'Esta acción no se puede deshacer', 'Sí, eliminar')
        .then((result) => {
            if (result.isConfirmed) {
                fetch('/vehiculos/' + id, {
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
                        $('#vehicles-table').DataTable().ajax.reload();
                        swalSuccess(data.message || 'Vehículo eliminado correctamente.', 2000);
                    } else {
                        swalError(data.message || 'Error al eliminar el vehículo.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    swalError('Error al eliminar el vehículo.');
                });
            }
        });
}
</script>
@endpush
@endsection
