@extends('layouts.app')

@section('title', 'Alertas')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Alertas</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Mantenimientos próximos, documentos por vencer y licencias</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="alerts-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Vehículo</th>
                            <th>Severidad</th>
                            <th>Título</th>
                            <th>Fecha límite</th>
                            <th>Pospuesta</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal posponer alerta -->
<div id="snooze-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeSnoozeModal()"></div>
        <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Posponer alerta</h3>
                <div class="space-y-4">
                    <div>
                        <label for="snooze-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Motivo (obligatorio)</label>
                        <textarea id="snooze-reason" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Ej.: Trámite en curso"></textarea>
                    </div>
                    <div>
                        <label for="snooze-hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horas (48–72)</label>
                        <input type="number" id="snooze-hours" value="48" min="48" max="72" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" id="snooze-confirm" class="inline-flex justify-center w-full sm:w-auto px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg">
                    Posponer
                </button>
                <button type="button" onclick="closeSnoozeModal()" class="mt-3 sm:mt-0 inline-flex justify-center w-full sm:w-auto px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-lg text-gray-900 dark:text-white">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = initDataTable('alerts-table', {
        ajax: {
            url: "{{ route('alerts.index') }}",
            type: 'GET'
        },
        columns: [
            { data: 'generated_at', name: 'generated_at', render: function(d) { return d ? (d.date || d) : '—'; } },
            { data: 'vehicle_info', name: 'vehicle.license_plate', orderable: false },
            { data: 'severity_badge', name: 'severity', orderable: true, searchable: false },
            { data: 'title', name: 'title' },
            { data: 'due_date_formatted', name: 'due_date', orderable: true, searchable: false },
            { data: 'snoozed_info', name: 'snoozed_until', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'no-export' }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 4, 5] },
            { className: "text-right", targets: [6] }
        ]
    });
});

let snoozeAlertId = null;

function closeAlert(id) {
    swalConfirmDelete('¿Cerrar esta alerta?', 'Se marcará como resuelta.', 'Sí, cerrar')
        .then((result) => {
            if (result.isConfirmed) {
                fetch("{{ url('alertas') }}/" + id + "/cerrar", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        $('#alerts-table').DataTable().ajax.reload();
                        swalSuccess(data.message || 'Alerta cerrada.');
                    } else {
                        swalError(data.message || 'Error al cerrar.');
                    }
                })
                .catch(() => swalError('Error al cerrar la alerta.'));
            }
        });
}

function snoozeAlert(id) {
    snoozeAlertId = id;
    document.getElementById('snooze-reason').value = '';
    document.getElementById('snooze-hours').value = '48';
    document.getElementById('snooze-modal').classList.remove('hidden');
}

function closeSnoozeModal() {
    snoozeAlertId = null;
    document.getElementById('snooze-modal').classList.add('hidden');
}

document.getElementById('snooze-confirm').addEventListener('click', function() {
    const reason = document.getElementById('snooze-reason').value.trim();
    const hours = parseInt(document.getElementById('snooze-hours').value, 10) || 48;
    if (!reason) {
        swalError('El motivo es obligatorio.');
        return;
    }
    if (snoozeAlertId === null) return;
    fetch("{{ url('alertas') }}/" + snoozeAlertId + "/posponer", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ reason: reason, hours: hours })
    })
    .then(r => r.json())
    .then(data => {
        closeSnoozeModal();
        if (data.success) {
            $('#alerts-table').DataTable().ajax.reload();
            swalSuccess(data.message || 'Alerta pospuesta.');
        } else {
            swalError(data.message || 'Error al posponer.');
        }
    })
    .catch(() => { closeSnoozeModal(); swalError('Error al posponer la alerta.'); });
});
</script>
@endpush
@endsection
