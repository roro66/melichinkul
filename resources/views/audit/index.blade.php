@extends('layouts.app')

@section('title', 'Auditoría')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Auditoría</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Historial de acciones críticas (eliminaciones, aprobaciones, cierre de alertas)</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="audit-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Modelo</th>
                            <th>ID</th>
                            <th>Descripción</th>
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
    initDataTable('audit-table', {
        ajax: {
            url: "{{ route('audit.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'user_name', name: 'user.name' },
            { data: 'action_label', name: 'action' },
            { data: 'model', name: 'model' },
            { data: 'model_id', name: 'model_id' },
            { data: 'description', name: 'description', orderable: false }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5] }
        ]
    });
});
</script>
@endpush
@endsection
