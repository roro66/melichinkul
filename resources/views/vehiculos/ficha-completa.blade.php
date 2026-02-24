@extends('layouts.app')

@section('title', 'Ficha completa - ' . $vehicle->license_plate)

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">
    {{-- Título tipo Excel --}}
    <div class="bg-amber-500 text-white px-6 py-3 rounded-t-lg">
        <h1 class="text-xl font-bold">
            Registro de mantenimiento {{ strtoupper($vehicle->category->name ?? 'Vehículo') }} {{ $vehicle->brand }} {{ $vehicle->model }} {{ $vehicle->license_plate }}
        </h1>
    </div>

    {{-- Tres bloques: datos vehículo | kilometraje y mantenciones | elementos de seguridad --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-2">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600 pb-1 mb-2">Datos del vehículo</h3>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Patente:</span> <strong>{{ $vehicle->license_plate }}</strong></p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Tipo:</span> {{ $vehicle->category->name ?? '—' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Chasis:</span> {{ $vehicle->chassis_number ?? '—' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Modelo:</span> {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year ?? '—' }})</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">RUT para trámites:</span> {{ $vehicle->rut_tramites ?? '—' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">RUT propietario:</span> {{ $vehicle->rut_propietario ?? '—' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Tarjeta combustible:</span> {{ $vehicle->tarjeta_combustible ? 'Sí' : 'No' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">GPS:</span> {{ $vehicle->gps ? 'Sí' : 'No' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Medida neumáticos:</span> {{ $vehicle->tire_size ?? '—' }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-2">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600 pb-1 mb-2">Kilometraje y mantenciones</h3>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Kilometraje:</span> <strong>{{ number_format($vehicle->current_mileage ?? 0, 0, ',', '.') }}</strong></p>
            @if($vehicle->mileage_updated_at)
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Fecha KM:</span> {{ $vehicle->mileage_updated_at->format('d/m/Y') }}</p>
            @endif
            <p class="text-sm flex items-center gap-2">
                <span class="text-gray-500 dark:text-gray-400">Próxima mantención:</span>
                @if($nextMaintenance)
                    <span>{{ $nextMaintenance->scheduled_date?->format('d/m/Y') }} ({{ number_format($nextMaintenance->mileage_at_maintenance ?? $vehicle->current_mileage, 0, ',', '.') }})</span>
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400" title="Programada"></i>
                @else
                    <span>—</span>
                @endif
            </p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Próxima alineación y balanceo:</span> {{ $nextAlignmentKm !== null ? number_format($nextAlignmentKm, 0, ',', '.') . ' km' : '—' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Última mantención:</span> {{ $lastMaintenance ? number_format($lastMaintenance->mileage_at_maintenance ?? 0, 0, ',', '.') : '—' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Último cambio de neumáticos:</span> {{ $lastTireChangeKm !== null ? number_format($lastTireChangeKm, 0, ',', '.') : '—' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Última alineación y balanceo:</span> {{ $lastAlignmentKm !== null ? number_format($lastAlignmentKm, 0, ',', '.') : '—' }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-2">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-600 pb-1 mb-2">Elementos de seguridad</h3>
            <p class="text-sm mb-2"><span class="text-gray-500 dark:text-gray-400">Última inspección:</span> <span class="font-medium">{{ $vehicle->safety_last_inspection_date ? $vehicle->safety_last_inspection_date->format('d-m-Y') : '—' }}</span></p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Gata:</span> {{ $vehicle->safety_gata ?? 'Sin información' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Llave rueda:</span> {{ $vehicle->safety_llave_rueda ?? 'Sin información' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Triángulo:</span> {{ $vehicle->safety_triangulo ?? 'Sin información' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Botiquín:</span> {{ $vehicle->safety_botiquin ?? 'Sin información' }}</p>
            <p class="text-sm"><span class="text-gray-500 dark:text-gray-400">Gancho de arrastre:</span> {{ $vehicle->safety_gancho_arrastre ?? 'Sin información' }}</p>
        </div>
    </div>

    {{-- Tabla documentación: Emisión, Vencimiento, Estado --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 px-4 py-3 border-b border-gray-200 dark:border-gray-700">Documentación y vigencia</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Documento</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Emisión</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Vencimiento</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $certTypes = ['technical_review' => 'Revisión Técnica', 'analisis_gases' => 'Revisión Gases', 'permiso_circulacion' => 'Permiso Circulación', 'soap' => 'SOAP', 'extintor_cabina' => 'Extintor Cabina', 'extintor_chasis' => 'Extintor Chasis'];
                    @endphp
                    @foreach($certTypes as $type => $label)
                    @php $cert = $vehicle->certifications->where('type', $type)->first(); @endphp
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $label }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $cert?->issue_date?->format('d-m-Y') ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $cert?->expiration_date?->format('d-m-Y') ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @if($cert)
                                @if($cert->expiration_date && $cert->expiration_date->isFuture())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300"><i class="fas fa-check-circle"></i> Vigente</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300"><i class="fas fa-times-circle"></i> Vencida</span>
                                @endif
                            @else
                                <span class="text-gray-500 dark:text-gray-400">Sin información</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Botón volver --}}
    <div class="flex justify-end">
        <a href="{{ route('vehiculos.show', $vehicle->id) }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors duration-150 font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Volver al resumen
        </a>
    </div>

    {{-- Historial de mantenimientos --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 px-4 py-3 border-b border-gray-200 dark:border-gray-700">Historial de mantenimientos</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Fecha</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Kilometraje</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Servicio efectuado</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Mecánico / taller</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-700 dark:text-gray-300">Costo</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-300">Observación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($vehicle->maintenances as $m)
                    @php
                        $costoValor = (int) ($m->total_cost ?? 0);
                        $mecanicoTexto = trim((string) ($m->workshop_supplier ?? $m->responsibleTechnician?->name ?? ''));
                        if ($costoValor <= 0 && $mecanicoTexto !== '' && preg_match('/^[\d.,\s]+$/', $mecanicoTexto)) {
                            $parsed = str_replace(['.', ' '], '', $mecanicoTexto);
                            $parsed = str_replace(',', '.', $parsed);
                            $costoValor = (int) round((float) $parsed);
                            $mecanicoTexto = '';
                        }
                    @endphp
                    <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $m->end_date?->format('d-m-Y') ?? $m->scheduled_date?->format('d-m-Y') ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $m->mileage_at_maintenance ? number_format($m->mileage_at_maintenance, 0, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $m->work_description ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $mecanicoTexto !== '' ? $mecanicoTexto : '—' }}</td>
                        <td class="px-4 py-2 text-right text-gray-900 dark:text-white">{{ $costoValor > 0 ? '$'.number_format($costoValor, 0, ',', '.') : '—' }}</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ Str::limit($m->observations ?? '—', 40) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No hay mantenimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
