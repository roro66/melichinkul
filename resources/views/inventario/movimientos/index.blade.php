@extends('layouts.app')

@section('title', 'Movimientos de inventario')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Movimientos de inventario</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Historial de entradas y salidas de stock</p>
        </div>
        <a href="{{ route('repuestos.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Catálogo repuestos</a>
    </div>

    @if($sparePartId)
        <p class="text-sm text-gray-600 dark:text-gray-400">Filtrado por repuesto. <a href="{{ route('inventario.movimientos.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Quitar filtro</a></p>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="movements-table" class="display nowrap w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Código repuesto</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Usuario</th>
                            <th>Notas</th>
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
    const sparePartId = '{{ $sparePartId ?? '' }}';
    initDataTable('movements-table', {
        ajax: {
            url: "{{ route('inventario.movimientos.index') }}",
            type: 'GET',
            data: function(d) {
                d.spare_part_id = sparePartId;
            }
        },
        columns: [
            { data: 'movement_date_formatted', name: 'movement_date', orderable: true },
            { data: 'spare_part_code', name: 'spare_part.code', orderable: false },
            { data: 'spare_part_description', name: 'spare_part.description', orderable: false },
            { data: 'type_label', name: 'type', orderable: true, searchable: false },
            { data: 'quantity_display', name: 'quantity', orderable: true, searchable: false },
            { data: 'user_name', name: 'user.name', orderable: false },
            { data: 'notes_short', name: 'notes', orderable: false }
        ],
        order: [[0, 'desc']],
        columnDefs: [
            { className: "text-left", targets: [0, 1, 2, 3, 5, 6] },
            { className: "text-right", targets: [4] }
        ]
    });
});
</script>
@endpush
@endsection
