@extends('layouts.app')

@section('title', 'Detalle de Mantenimiento')

@section('content')
<div class="space-y-6">
    @php
        $maintenance = \App\Models\Maintenance::with(['vehicle', 'responsibleTechnician', 'assignedDriver', 'approvedBy', 'maintenanceSpareParts.sparePart'])->findOrFail($id);
        $canApprove = in_array(Auth::user()->rol ?? Auth::user()->role ?? null, ['administrator', 'supervisor']);
        $spareParts = \App\Models\SparePart::where('active', true)->orderBy('code')->get();
    @endphp

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mantenimiento #{{ $maintenance->id }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ $maintenance->vehicle->license_plate }} - {{ __('mantenimiento.types.' . $maintenance->type, [], 'es') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if($maintenance->status === 'pending_approval' && $canApprove)
                <form action="{{ route('mantenimientos.approve', $maintenance->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Aprobar este mantenimiento?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 transition-colors duration-150">
                        <i class="fas fa-check mr-2"></i> Aprobar
                    </button>
                </form>
            @endif
            <a href="{{ route('mantenimientos.edit', $maintenance->id) }}" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition-colors duration-150">
                Editar
            </a>
            <a href="{{ route('mantenimientos.index') }}" 
                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                Volver
            </a>
        </div>
    </div>

    <!-- Información General -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Información General</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Vehículo:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->vehicle->license_plate }} - {{ $maintenance->vehicle->brand }} {{ $maintenance->vehicle->model }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $maintenance->type === 'preventive' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 
                       ($maintenance->type === 'corrective' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300' : 
                       'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300') }}">
                    {{ __('mantenimiento.types.' . $maintenance->type, [], 'es') }}
                </span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $maintenance->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : 
                       ($maintenance->status === 'pending_approval' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300' : 
                       ($maintenance->status === 'in_progress' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300' : 
                       ($maintenance->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300' : 
                       'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300'))) }}">
                    {{ __('mantenimiento.statuses.' . $maintenance->status, [], 'es') }}
                </span>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Programada:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->scheduled_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Inicio:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->start_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Fin:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->end_date?->format('d/m/Y') ?? 'Sin fecha' }}</p>
            </div>
        </div>
    </div>

    <!-- Descripción y Trabajos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Descripción del Trabajo</h2>
        <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $maintenance->work_description }}</p>
        
        @if($maintenance->entry_reason)
            <div class="mt-4">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Motivo de Ingreso:</span>
                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $maintenance->entry_reason }}</p>
            </div>
        @endif

        @if($maintenance->work_performed)
            <div class="mt-4">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Trabajos Realizados:</span>
                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $maintenance->work_performed }}</p>
            </div>
        @endif
    </div>

    <!-- Costos -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Costos</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo Repuestos:</span>
                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($maintenance->parts_cost, 0, ',', '.') }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo Mano de Obra:</span>
                <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($maintenance->labor_cost, 0, ',', '.') }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Costo Total:</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white">${{ number_format($maintenance->total_cost, 0, ',', '.') }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Horas Trabajadas:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->hours_worked ?? 'N/A' }} hrs</p>
            </div>
        </div>
    </div>

    <!-- Repuestos utilizados -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Repuestos utilizados</h2>
        @if($maintenance->maintenanceSpareParts->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">No hay repuestos registrados. Al completar el mantenimiento se descontará el stock.</p>
        @else
            <div class="overflow-x-auto mb-4">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Repuesto</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300 uppercase">Cantidad</th>
                            @if($maintenance->status !== 'completed' && $maintenance->status !== 'cancelled')
                                <th class="px-4 py-2 w-20"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach($maintenance->maintenanceSpareParts as $pivot)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                    <a href="{{ route('repuestos.show', $pivot->sparePart->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $pivot->sparePart->code }}</a>
                                    - {{ $pivot->sparePart->description }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white text-right">{{ $pivot->quantity }}</td>
                                @if($maintenance->status !== 'completed' && $maintenance->status !== 'cancelled')
                                    <td class="px-4 py-2">
                                        <form action="{{ route('mantenimientos.repuestos.remove', [$maintenance->id, $pivot->id]) }}" method="POST" class="inline" onsubmit="return confirm('¿Quitar este repuesto del mantenimiento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-sm" title="Quitar"><i class="fas fa-times"></i></button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if($maintenance->status !== 'completed' && $maintenance->status !== 'cancelled')
            <form action="{{ route('mantenimientos.repuestos.add', $maintenance->id) }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-[200px]">
                    <label for="spare_part_id" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Repuesto</label>
                    <select id="spare_part_id" name="spare_part_id" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="">Seleccione repuesto</option>
                        @foreach($spareParts as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->code }} - {{ $sp->description }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <label for="quantity" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cantidad</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" required class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white text-sm rounded-lg">Agregar</button>
            </form>
        @endif
    </div>

    <!-- Información Adicional -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Información Adicional</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Técnico Responsable:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->responsibleTechnician->name ?? 'Sin asignar' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Conductor Asignado:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->assignedDriver->full_name ?? 'Sin asignar' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Taller/Proveedor:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->workshop_supplier ?? 'N/A' }}</p>
            </div>
            @if($maintenance->approved_by_id && $maintenance->approved_at)
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Aprobado por:</span>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->approvedBy->name ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $maintenance->approved_at->format('d/m/Y H:i') }}</p>
                </div>
            @endif
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Kilometraje:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->mileage_at_maintenance ? number_format($maintenance->mileage_at_maintenance, 0, ',', '.') . ' km' : 'N/A' }}</p>
            </div>
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Horómetro:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $maintenance->hours_at_maintenance ? number_format($maintenance->hours_at_maintenance, 0, ',', '.') . ' hrs' : 'N/A' }}</p>
            </div>
            @if($maintenance->observations)
                <div class="md:col-span-2">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Observaciones:</span>
                    <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $maintenance->observations }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($maintenance->evidence_invoice_path || $maintenance->evidence_photo_path)
    <!-- Evidencia -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Evidencia</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($maintenance->evidence_invoice_path)
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Factura / Documento:</span>
                    <p class="mt-1">
                        <a href="{{ Storage::url($maintenance->evidence_invoice_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center">
                            <i class="fas fa-external-link-alt mr-2"></i> Ver archivo
                        </a>
                    </p>
                </div>
            @endif
            @if($maintenance->evidence_photo_path)
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Foto del trabajo:</span>
                    <p class="mt-1">
                        <a href="{{ Storage::url($maintenance->evidence_photo_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline inline-flex items-center">
                            <i class="fas fa-external-link-alt mr-2"></i> Ver archivo
                        </a>
                    </p>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
