@extends('layouts.app')

@section('title', 'Logs de acceso')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Logs de acceso</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Registro de accesos al sistema (usuario, IP, URL, fecha)</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="access-logs-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Método</th>
                            <th>URL</th>
                            <th>IP</th>
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
    initDataTable('access-logs-table', {
        ajax: {
            url: "{{ route('access_logs.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'created_at_formatted', name: 'created_at' },
            { data: 'user_name', name: 'user.name' },
            { data: 'method', name: 'method' },
            { data: 'url_short', name: 'url', orderable: false },
            { data: 'ip_address', name: 'ip_address' }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4] }
        ]
    });
});
</script>
@endpush
@endsection
