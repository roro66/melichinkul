@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Usuarios</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestión de usuarios del sistema</p>
        </div>
        <a href="{{ route('usuarios.create') }}"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150">
            Nuevo usuario
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="users-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Nombre completo</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
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
    initDataTable('users-table', {
        ajax: {
            url: "{{ route('usuarios.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'email', name: 'email' },
            { data: 'full_name', name: 'full_name', render: function(d) { return d || '—'; } },
            { data: 'phone', name: 'phone', render: function(d) { return d || '—'; } },
            { data: 'role_badge', name: 'role', orderable: true, searchable: false },
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

window.deleteUser = function(id) {
    swalConfirmDelete('¿Estás seguro?', 'El usuario será eliminado. Esta acción no se puede deshacer.', 'Sí, eliminar')
        .then(function(result) {
            if (result.isConfirmed) {
                fetch('/usuarios/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        swalSuccess(data.message);
                        $('#users-table').DataTable().ajax.reload(null, false);
                    } else {
                        swalError(data.message || 'Error al eliminar');
                    }
                })
                .catch(function() { swalError('Error de conexión'); });
            }
        });
};
</script>
@endpush
@endsection
