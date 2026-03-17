@extends('layouts.app')

@section('title', 'Trabajos realizados · Mantenimiento #' . $maintenance->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registrar trabajos realizados</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Mantenimiento #{{ $maintenance->id }} · {{ $maintenance->vehicle->license_plate ?? '' }} — {{ $maintenance->vehicle->brand ?? '' }} {{ $maintenance->vehicle->model ?? '' }}
        </p>
        <p class="mt-2 text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg px-3 py-2">
            Puedes editar trabajos realizados, repuestos, costos, horas, observaciones y subir documentos o fotos. El resto del mantenimiento lo gestionan administración o supervisión.
        </p>
    </div>

    @if(session('success'))
    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-green-800 dark:text-green-200 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 text-red-800 dark:text-red-200 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-6">
        <div>
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Descripción del trabajo (solo lectura)</h2>
            <p class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ $maintenance->work_description }}</p>
        </div>

        <!-- Repuestos utilizados -->
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Repuestos utilizados</h3>
            @if($maintenance->maintenanceSpareParts->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">Aún no se han agregado repuestos.</p>
            @else
                <ul class="border border-gray-200 dark:border-gray-600 rounded-lg divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($maintenance->maintenanceSpareParts as $pivot)
                    <li class="flex items-center justify-between px-4 py-2 text-sm">
                        <span class="text-gray-900 dark:text-white">{{ optional($pivot->sparePart)->code ?? '—' }} — {{ optional($pivot->sparePart)->description ?? '—' }} × {{ $pivot->quantity }}</span>
                        <form action="{{ route('mantenimientos.registrar-trabajo.repuestos.remove', [$maintenance, $pivot->id]) }}" method="POST" class="inline" onsubmit="return confirm('¿Quitar este repuesto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs">Quitar</button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            @endif
            @if($spareParts->isNotEmpty())
            <form action="{{ route('mantenimientos.registrar-trabajo.repuestos.add', $maintenance) }}" method="POST" class="mt-3 flex flex-wrap items-end gap-3">
                @csrf
                <div class="min-w-[200px]">
                    <label for="spare_part_id" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Agregar repuesto</label>
                    <select name="spare_part_id" id="spare_part_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                        <option value="">Seleccionar</option>
                        @foreach($spareParts as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->code }} — {{ $sp->description }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <label for="quantity" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cantidad</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                </div>
                <button type="submit" class="px-3 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-lg text-sm font-medium text-gray-800 dark:text-gray-200">
                    <i class="fas fa-plus mr-1"></i> Agregar
                </button>
            </form>
            @else
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">No hay repuestos activos en el sistema para agregar. Contacte al administrador.</p>
            @endif
        </div>

        <!-- Formulario principal -->
        <form action="{{ route('mantenimientos.registrar-trabajo.store', $maintenance) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label for="work_performed" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trabajos realizados</label>
                <textarea id="work_performed" name="work_performed" rows="6"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 @error('work_performed') border-red-500 @enderror"
                    placeholder="Describe el trabajo ejecutado…">{{ old('work_performed', $maintenance->work_performed) }}</textarea>
                @error('work_performed')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="parts_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Costo repuestos ($)</label>
                    <input type="number" id="parts_cost" name="parts_cost" value="{{ old('parts_cost', $maintenance->parts_cost) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="labor_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mano de obra ($)</label>
                    <input type="number" id="labor_cost" name="labor_cost" value="{{ old('labor_cost', $maintenance->labor_cost) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="total_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Costo total ($)</label>
                    <input type="number" id="total_cost" name="total_cost" value="{{ old('total_cost', $maintenance->total_cost) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="hours_worked" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horas trabajadas</label>
                    <input type="number" id="hours_worked" name="hours_worked" value="{{ old('hours_worked', $maintenance->hours_worked) }}" min="0" step="0.5"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="observations" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
                <textarea id="observations" name="observations" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('observations', $maintenance->observations) }}</textarea>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Evidencia (documentos y fotos)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="evidence_invoice" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Factura / Documento (PDF o imagen)</label>
                        @if($maintenance->evidence_invoice_path)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Actual: <a href="{{ Storage::url($maintenance->evidence_invoice_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver archivo</a>
                        </p>
                        @endif
                        <input type="file" id="evidence_invoice" name="evidence_invoice" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-sm text-gray-600 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máx. 10 MB. PDF, JPG o PNG.</p>
                        @error('evidence_invoice')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="evidence_photo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto del trabajo realizado</label>
                        @if($maintenance->evidence_photo_path)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            Actual: <a href="{{ Storage::url($maintenance->evidence_photo_path) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Ver archivo</a>
                        </p>
                        @endif
                        <input type="file" id="evidence_photo" name="evidence_photo" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-sm text-gray-600 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máx. 10 MB. PDF, JPG o PNG.</p>
                        @error('evidence_photo')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
                    <i class="fas fa-save mr-2"></i> Guardar
                </button>
                <a href="{{ route('mantenimientos.show', $maintenance->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
